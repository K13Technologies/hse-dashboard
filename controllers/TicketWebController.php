<?php

class TicketWebController extends AjaxController {

    public function ticketsView() {
        $loggedUser = Auth::user();
        $data['user'] = $loggedUser;

        $workers = Worker::where('company_id', '=', $loggedUser->company_id)
                        ->with('tickets')
                        ->orderBy('last_name', 'DESC')
                        ->get();


        foreach($workers as $worker) {
            foreach($worker->tickets as $ticket){
                $ticket['photoIds'] = $ticket->extractPhotoIds();
                if($ticket->review){
                    $ticket['review'] = $ticket->review;
                    // Convert to local user time in usable format
                    $ticket['review']['created_at'] =  WKSSDate::timestampToStringWithCustomFormatAndLocalTime(strtotime($ticket->created_at), WKSSDate::FORMAT_DETAILS);
                } 
            }
        }

        $data['companyWorkers'] = $workers;

        return View::make('webApp::tickets.list', $data);
    }

    public function editTicketView($ticketId) {
        $loggedUser = Auth::user();
        $ticket = Ticket::find($ticketId);

        if($ticket && $ticket->company_id = $loggedUser->company_id){
            $data['user'] = $loggedUser;
            $workers = $loggedUser->company->workers;

            // Sort by last name
            $workers = $workers->sortBy(function($worker){
              return $worker->last_name;
            });

            // Only return necessary values
            foreach($workers as $worker){
                $data['workers'][$worker->worker_id] = $worker->last_name . ', ' . $worker->first_name;
            }

            $data['companyWorkers'] = $workers;

            $ticket['photoIds'] = $ticket->extractPhotoIds();
            if($ticket->review){
                $ticket['review'] = $ticket->review;
            } 

            $data['ticket'] = $ticket;

            return View::make('webApp::tickets.view', $data);
        }
        else {
            return Redirect::to('/');
        }   
    }

    public function createTicketView() {
        $loggedUser = Auth::user();
        $data['user'] = $loggedUser;
        $workers = $loggedUser->company->workers;

        if($workers->count()) {
            // Sort by last name
            $workers = $workers->sortBy(function($worker){
              return $worker->last_name;
            });

            // Only return necessary values
            foreach($workers as $worker){
                if($worker->first_name && $worker->last_name){
                    $data['workers'][$worker->worker_id] = $worker->last_name . ', ' . $worker->first_name . ' ('. $worker->auth_token . ')';
                } else {
                    // Information wasn't filled out
                    $data['workers'][$worker->worker_id] = $worker->auth_token . ' (Name not yet filled out)';
                }
            }
        }

        return View::make('webApp::tickets.create', $data);
    }

    // This functionality should probably NOT be in the controller.
    // Don't know how to implement a better architecture right now.
    // used for both create and update
    private function validateWebForm($input){
        $loggedUser = Auth::user();

        // General Ticket fields
        $rules = array('type_name' => 'required|max:3000',
                       'worker_id' => 'required',
                       'issuer_organization_name' => 'max:3000',
                       'description' => 'max:3000',
                       'expiry_date' => 'regex:/\d\d\d\d-\d\d-\d\d/'
                       );

        $messages = array(); 

        // Object fields =========

        if(isset($input['issued_internally']) && $input['issued_internally'] == FALSE && $input['issuer_organization_name'] == '') {
            array_push($messages, 'You must specify the organization that issued the ticket.');
            return array('result'=>FALSE, 'error'=>$messages);
        }

        // Check that they didn't tamper with the worker ID -- the entered ID must be a valid worker ID within their company
        if(! in_array($input['worker_id'], $loggedUser->company->workers->lists('worker_id'))){
            array_push($messages, 'You must select a worker.');
            return array('result'=>FALSE, 'error'=>$messages);
        }

        // Check that they didn't tamper with the company ID -- the entered ID must be a valid worker ID within their company
        if(isset($input['company_id'])){
            if(! in_array($input['company_id'], $loggedUser->company->workers->lists('company_id'))){
                array_push($messages, 'Do not tamper with the worker IDs. Your IP address has been logged.');
                return array('result'=>FALSE, 'error'=>$messages);
            }
        }
        
        // UPLOADED PICTURES
        if (isset($input['newPhotos'])) {
            $pictureCount = 0;
            foreach($input['newPhotos'] as $picture) {
                if(isset($picture['fileData']['modifiedDataURL'])){
                    try {
                        // Verify the image by trying to create it - probably more resource intensive, but it's clean code!
                        $img = Image::make($picture['fileData']['modifiedDataURL']);

                    } catch (Exception $e) {
                        array_push($messages, 'Please finalize all image edits before adding this ticket.');
                        return array('result'=>FALSE, 'error'=>$messages);
                    }
                }
            }  
        }

        // Actually test the rules if it hasn't already failed the other checks
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()){
            // Markup can be placed in the :message area should we ever change the display format
            return array('result'=>FALSE, 'error'=>$validator->messages()->all(':message'));
        }

        // If it reaches this point, the input is good
        return array('result'=>TRUE);
    }

    // Called when the user submits the create ticket form. Handles Ticket creation and image uploading. 
    public function createTicket() {
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $input = Input::json()->all();

        $validationResult = Self::validateWebForm($input);
        if ($validationResult['result'] == FALSE) {
            return self::buildAjaxResponse(FALSE, $validationResult['error']); // Validation result will be displayed to user in an alert box
        }

        $newTicket = new Ticket;
        $newTicket->setFields($input);
        $newTicket->created_by_admin_id = $loggedUser->admin_id;

        $newTicket->company_id = $loggedUser->company_id;

        if($newTicket->save()) {
            $ticketId = $newTicket->ticket_id;
            Self::createManagementTicketReview($ticketId);
            $companyLevel = Config::get('api::storagePaths.companyLevel');
            $companyId = $loggedUser->company_id;
            $workerId = $newTicket->worker_id;

            $ticketPath = "{$companyLevel}/{$companyId}/tickets/{$workerId}/{$ticketId}/";

            if(!is_dir($ticketPath)){
                // True flag creates parent directories if needed as well, such as if it is their first ticket submission and the tickets
                // folder does not yet exist.
                mkdir($ticketPath, 0777, TRUE); 
            }

            // If the user has uploaded pictures
            foreach($input['newPhotos'] as $picture) {
                // Confirm that they actually attached a picture
                if(isset($picture['fileData']['modifiedDataURL'])){

                    // Creating the file object - resize image to standard ticket size
                    $img = Image::make($picture['fileData']['modifiedDataURL'])->resize(400, 300); // Image Facade is Intervention image manipulation tool

                    // Creating the data object in the DATABASE
                    $photo = new Photo();
                    $photo->name = Photo::generatePhotoName();
                    $photo->path = $ticketPath . $photo->name;
                    $photo->imageable_id = $newTicket->ticket_id;
                    $photo->worker_id = $newTicket->worker_id;
                    $newTicket->photos()->save($photo);

                    // Save image file to directory
                    $img->save($photo->path);

                    // Save image file as thumbnail
                    // Not actually going to do this right now, as I can't think of a reason for it.
                    // Future self, if you do decide to add this feature, be sure to add a db record for the thumbnail in the photos table
                    //$img->fit(100)->save($ticketPath . "tn-" . $photo->name);
                }        
            } // end of foreach
        }

        return self::buildAjaxResponse(TRUE, 'Save successful!'); 

        //return self::buildAjaxResponse(FALSE, 'Ticket Creation Failed. Please try again. If the problem persists, please contact White Knight Support.');
    }

    // Called when the user submits the update ticket form.
    public function update() {
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $input = Input::json()->all();

        $validationResult = Self::validateWebForm($input);
        if ($validationResult['result'] == FALSE) {
            return self::buildAjaxResponse(FALSE, $validationResult['error']); // Validation result will be displayed to user in an alert box
        }

        $ticket = Ticket::find($input['ticket_id']);

        if($ticket->company_id == $loggedUser->company_id){

            Self::deleteCurrentPhotosIfDeleted($input['currentPhotos'], $ticket);

            // user should not be able to edit worker_id so it has been disabled on the front end
            // they still could theoritically change it but the repercussions are not a big deal. 
            $ticket->setFields($input); 
            $ticket->created_by_admin_id = $loggedUser->admin_id; // Could be a different admin updating it
              
            if($ticket->save()) {
                $ticketId = $ticket->ticket_id;

                // Out with the old
                Self::deleteOldManagementTicketReview($ticketId);
                // In with the new
                Self::createManagementTicketReview($ticketId);

                $companyLevel = Config::get('api::storagePaths.companyLevel');
                $companyId = $loggedUser->company_id;
                $workerId = $ticket->worker_id;

                // This is where changing the worker ID could complicate things
                $ticketPath = "{$companyLevel}/{$companyId}/tickets/{$workerId}/{$ticketId}/";

                // Path should already exist, so don't need to create

                // If the user has uploaded new pictures
                foreach($input['newPhotos'] as $picture) {
                    // Confirm that they actually attached a picture
                    if(isset($picture['fileData']['modifiedDataURL'])){

                        // Creating the file object - resize image to standard ticket size
                        $img = Image::make($picture['fileData']['modifiedDataURL'])->resize(400, 300); // Image Facade is Intervention image manipulation tool

                        // Creating the data object in the DATABASE
                        $photo = new Photo();
                        $photo->name = Photo::generatePhotoName();
                        $photo->path = $ticketPath . $photo->name;
                        $photo->imageable_id = $ticket->ticket_id;
                        $photo->worker_id = $ticket->worker_id;
                        $ticket->photos()->save($photo);

                        // Save image file to directory
                        $img->save($photo->path);
                    }        
                }
            }
        } else {
            die('You do not have access to this data. Your IP address has been logged.');
        }

        return self::buildAjaxResponse(TRUE, 'Save successful!'); 
    }

    // Delete currently saved photos if the user has specified to delete them
    // ATTENTION: This should only be called after verification is complete 
    private function deleteCurrentPhotosIfDeleted ($currentPhotosArray, $ticket){
        foreach($currentPhotosArray as $currentPhoto){
            if($currentPhoto['deleted'] == true){

                // Find photo record and make sure it is theirs so that they can't tamper with it -- ticket should already be verified
                $photo = Photo::where('name', '=', $currentPhoto['id'])
                              ->where('worker_id', '=', $ticket->worker_id)
                              ->first();

                if($photo) { 
                    // Delete the actual photo file
                    if(is_file($photo->path)){
                        unlink($photo->path);
                    }

                    // remove record
                    $photo->delete();
                }
            }
        }
    }

    private function deleteOldManagementTicketReview($ticketId) {
        $review = SafetyFormReview::where('reviewable_type', 'Ticket')
                                  ->where('reviewable_id', $ticketId)
                                  ->first();

        if($review) {
            $review->delete();
        }
    }

    // This code is actually duplicated from within routes.php ... Normally the user would click a button which posts to that route
    // But in this case, the user does so indirectly by creating a ticket, so this private function is used within the ticket save method.
    private function createManagementTicketReview($ticketId) {
        $loggedUser = Auth::user();
        $class = 'Ticket';
        $resourceId = $ticketId;
        $ticketObj = Ticket::findOrFail($resourceId);

        if (!$ticketObj->review instanceof SafetyFormReview){
            $review = new SafetyFormReview;
            $reviewerName = trim($loggedUser->first_name.' '. $loggedUser->last_name);
            $review->reviewer_name = $reviewerName? $reviewerName : $loggedUser->email;
            $review->added_by = $loggedUser->admin_id;
            $review->ts = time();
            $review->created_at = date('Y-m-d H-i-s ZZZ');
            $review->reviewable_type = $class;
            $review->reviewable_id = $resourceId;
            $review->save();
            return true;
        }

        return false;
    }

    public function deleteTicket() {
        $loggedUser = Auth::user();
        $input = Input::json()->all();

        $ticketId = intval($input[0]); // From the input, only the ticket ID is being passed in to the first array slot
        $ticket = Ticket::find($ticketId);

        // very handy tool
        //dd(DB::getQueryLog());

        // Very important security check so that the user can't just enter whatever ticket ID they want
        if($ticket->company_id == $loggedUser->company_id){
            $ticketId = $ticket->ticket_id; // Used after ticket is deleted
            $workerId = $ticket->worker_id; // Used after ticket is deleted

            $ticket->review()->delete();
            $ticket->photos()->delete();
            $ticket->delete();

            // Files are store in Company > Tickets > Worker ID > Ticket ID
            $sm = new StorageManager;
            $sm->deleteTicketFolder($loggedUser->company_id, $workerId, $ticketId);

            return self::buildAjaxResponse(TRUE, 'Deletion successful.'); 
        }
        else {
            return self::buildAjaxResponse(FALSE, 'Ticket Deletion Failed. Please try again. If the problem persists, please contact White Knight Support.');
        }

        return self::buildAjaxResponse(FALSE, 'Ticket Deletion Failed. Please try again. If the problem persists, please contact White Knight Support.');
    }

    // Exports a PDF of the specified ticket
    public function exportTicket($ticketId) {
        $loggedUser = Auth::user();

        if(!$loggedUser) {
            return Redirect::to('/');
        }

        $ticket = Ticket::find($ticketId);

        if($ticket && $ticket->company_id == $loggedUser->company_id) {
            $data['ticket'] = $ticket;
            $html = View::make('webApp::tickets.export', $data)->render();
            $pdf = PDF::loadHTML($html)->setOrientation('landscape');
            $workerName = $ticket->worker->first_name . " " . $ticket->worker->last_name;
            $filename = $ticket->type_name . '-Ticket-' . $workerName . "-" . date('d-M-Y') . '.pdf';
            return $pdf->download($filename);            
        }  
    }

    public function mailTicket() {
        $loggedUser = Auth::user();

        if(!$loggedUser) {
            return Redirect::to('/');
        }

        $email = Input::get('email');
        $ticketId = Input::get('ticketId');
        $ticket = Ticket::find($ticketId);

        if($ticket && $ticket->company_id == $loggedUser->company_id) {
            $data['ticket'] = $ticket;
            $workerName = $ticket->worker->first_name . " " . $ticket->worker->last_name;
            $filename = $ticket->type_name . '-Ticket-' . $workerName . "-" . date('d-M-Y') . '.pdf';
            $html = View::make('webApp::tickets.export', $data)->render();
            $pdf = PDF::loadHTML($html)->setOrientation('landscape');
            $nameForDoc = $loggedUser->company->company_name . '-Ticket-' . $ticket->type_name . " " . date('d-M-Y');
            $file = StorageManager::saveTempPDFDocument($pdf, $nameForDoc);
            $data = array('email' => $email,
                'object' => $ticket,
                'type' => 'Ticket - ' . $data['ticket']->type_name . " (" . $workerName . ")",
                'attach' => $file); 

            return Self::sendAttachmentTicketEmail($data);   
        }
        else {
            die('You do not have permission to perform this action. Your IP address has been logged');
        }        
    }

}
