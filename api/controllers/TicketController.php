<?php

class TicketController extends ApiController {
    
    /**
     * This handles the get Ticket list call
     * 
     * @return JSONResponse 
     */
    public function getAllTicketsForWorker(){
        // In the future we MUST use this..
        // $worker = Worker::getByAPIKey(Input::get('apiKey'));
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $tickets = $worker->tickets;

        $tickets = Ticket::where('worker_id', $worker->worker_id)->with(['photos'])->get();

        if($tickets->first()){
            foreach ($tickets as $ticket) {
                $ticket->photoList = $ticket->extractPhotoIds();

                $review = $ticket->review;
                $ticket->approver_name = $review->reviewer_name;
                $ticket->approval_date = $review->created_at;
                $ticket->approval_date_ts = $review->ts;
                $ticket->approver_id = $review->added_by; 
                $approver = Admin::find($review->added_by);
                $ticket->approver_phone_number = $approver->phone_number;

                // Don't want to include all the review info
                unset($ticket->review);
            }

            return $this->createResponse(200, $tickets);
        }

        return $this->createResponse(404);
    }

    public function getAllSubsectionsMetadata() {
        // In the future we MUST use this..
        // $worker = Worker::getByAPIKey(Input::get('apiKey'));
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $safetyManual = SafetyManual::where('company_id', '=', $worker['company_id'])->first();

        if($safetyManual){
            $safetyManualSubsectionsMetadata = $safetyManual->subsections->toArray();
            $metaDataList = array();

            foreach($safetyManualSubsectionsMetadata as $subsection) {
                array_push($metaDataList, ['subsection_id' => $subsection['subsection_id'], 'updated_at' => $subsection['created_at']]);
            }

            return $this->createResponse(200, $metaDataList);
        }

        return $this->createResponse(404);
    }
    
    public function getSection($sectionId){
        // Should be API key...
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $section = SafetyManualSection::find($sectionId);
        $section['subsections'] = $section->subsections;

        foreach($section['subsections'] as $subsection) {
            // remove unneeded content to reduce size of data
            $subsection['subsection_content'] = "";
        }

        return $this->createResponse(200, $section->toArray());
    }

    public function getSubsection($subsectionId){
        // Should be API key...
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $subsection = SafetyManualSubsection::find($subsectionId);
        $subsection['subsection_content'] = ''; // remove unecessary data

        return $this->createResponse(200, $subsection);
    }

    public function getSafetyManualPhotoIdList(){
        // Should be API key...
        $worker = Worker::getByAuthToken(Input::get('authToken'));

        $safetyManualId = $worker->company->safetyManual->safety_manual_id;
        $photoIdList = Photo::where('imageable_type', 'SafetyManual')
                            ->where('imageable_id', $safetyManualId)
                            ->lists('name');

        return $this->createResponse(200, $photoIdList);
    }  

    public function getTicketPhoto($photoId){
        $photo = Photo::getPhotoByPhotoName($photoId);
        return $this->createImageResponse($photo->path);
    } 

    public function getTicketApprovalSignature($adminId) {
        $sm = new StorageManager;
        $signaturePath = $sm->getAdminSignature($adminId);

        if($signaturePath){
            return AjaxController::createImageResponse($signaturePath);
        } else {
            App::abort(404);
        }  
    }
}