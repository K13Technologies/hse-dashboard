<?php

class SafetyManualWebController extends AjaxController {

	// Used for creating new safety manuals
	private static $defaultSectionTitles = array('Using the Safety Manual',
												 'Introduction to the Safety Manual',
												 'Health & Safety Policies',
												 'Hazard Assessments',
												 'Safe Work Practices',
												 'Safe Job Procedures',
												 'Company Rules',
												 'Personal Protective Equipment',
												 'Preventative Maintenance',
												 'Training & Communication',
												 'Inspections',
												 'Investigations & Reporting',
												 'Emergency Preparedness and Response',
												 'Records and Statistics',
												 'Legislation',
												 'WHMIS & TDG',
												 'Orientation',
												 'Violence and Harassment',
												 'Drug & Alcohol',
												 'Commercial Vehicle Regulations',
												 'Light Duty',
												 'Waste Management / Environmental Policy',
												 'Safety Alerts & Bulletins',
												 'Contractor Management',
												 'ISNetworld RAVS Information',
												 'PICS Information',
												 'Integrating Electronic Safety Software'
												);

    public function safetyManualView() {
        $loggedUser = Auth::user();
        $data['user'] = $loggedUser;
         
        $safetyManual = SafetyManual::getFullManualForCompany($loggedUser->company_id);

        if(!$safetyManual){
            // Company does not yet have one, so create it 
            $safetyManual = Self::createCompanySafetyManual($loggedUser->company_id);
        }
         
        $data['safetyManual'] = $safetyManual;
        
        return View::make('webApp::safety-manual.view', $data);
    }

    public function safetyManualSectionView($sectionId) {
        $loggedUser = Auth::user();
        $data['user'] = $loggedUser;
         
	    $safetyManual = SafetyManual::getFullManualForCompany($loggedUser->company_id);

	    if(!$safetyManual){
	    	// Company does not yet have one, so create it 
	    	$safetyManual = Self::createCompanySafetyManual($loggedUser->company_id);
	    }
         
        $data['safetyManual'] = $safetyManual;

        if($sectionId == 'swp'){
            $result = SafetyManualSection::where('safety_manual_id', '=', $safetyManual->safety_manual_id)
                                                    ->where('is_SWP', '=', 1)
                                                    ->first(['section_id']);
            $data['sectionId'] = $result->section_id;
        }
        else if ($sectionId == 'sjp'){
            $result = SafetyManualSection::where('safety_manual_id', '=', $safetyManual->safety_manual_id)
                                                    ->where('is_SJP', '=', 1)
                                                    ->first(['section_id']);
            $data['sectionId'] = $result->section_id;
        }
        else {
            $data['sectionId'] = $sectionId;
        }
        
        return View::make('webApp::safety-manual.sections.view', $data);
    }

    public function safetyManualRevisionView() {
        $loggedUser = Auth::user();
        $safetyManual = SafetyManual::where('company_id', '=', $loggedUser->company_id)->first();
        
        if($safetyManual && $safetyManual->safety_manual_id){
            $revisions = SafetyManualRevision::where('safety_manual_id', $safetyManual->safety_manual_id)
                                            ->orderBy('created_at', 'DESC')
                                            ->get();
            $data['revisions'] = $revisions;
        }
        else{
            $data['revisions'] = array();
        }
            
        return View::make('webApp::safety-manual.revisions.list', $data);
    }

    private function createCompanySafetyManual($companyId) {
    	$safetyManual = new SafetyManual;
    	$safetyManual->company_id = $companyId;

    	if($safetyManual->save()) {
    		foreach(Self::$defaultSectionTitles as $title){
    			$section = new SafetyManualSection;
    			$section->section_title = $title;
    			$section->safety_manual_id = $safetyManual->safety_manual_id;

                if($title == 'Safe Work Practices')
                    $section->is_SWP = true; // This special section type must be accounted for
                
                if($title == 'Safe Job Procedures')
                    $section->is_SJP = true; // This special section type must be accounted for

    			$section->save();
    		}
    	}

    	return SafetyManual::getFullManualForCompany($companyId);
    }

    // This function is a beast and could use separation...
    public function editSafetyManualAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $input = Input::json()->all();

        $safetyManual = SafetyManual::getFullManualForCompany($loggedUser->company_id);

        $error = DB::transaction(function() use ($safetyManual, $input)
        {
            if ($safetyManual->save()) {

                // This will be used to ensure that if the user has deleted a section, the ID associated with that section is in fact theirs
                $currentSectionIds = SafetyManualSection::where('safety_manual_id', '=', $safetyManual->safety_manual_id)->lists('section_id');

                // will be used to help determine which photos in the filesystem need to be deleted, if any. 
                // since it is calculated for each subsection it will be a multidimensional array
                $newCurrentPhotoIds = array(); 

                foreach($input['sections'] as $inputSection) 
                {   
                    // This check skips items which may have been created and deleted on the UI within the same session.
                    // For example, the user adds a section and then immediately deletes it before saving. We want to skip those.
                    // In words, it will be considered if it has an ID, or if it does not have an ID AND it hasn't been marked as deleted
                    // The same applies for subsections. 
                    if ($inputSection['section_id'] || (!$inputSection['section_id'] && !$inputSection['isDeleted'])){
                        $section = SafetyManualSection::firstOrNew(array('section_id' => $inputSection['section_id'],
                                                                         'safety_manual_id' => $safetyManual->safety_manual_id));

                        if($section instanceof SafetyManualSection &&  $section->safety_manual_id == $safetyManual->safety_manual_id) {
                            if(!$inputSection['isDeleted']){
                                $section->setFields($inputSection);
                                $section->save();

                                // This will be used to ensure that if the user has deleted a subsection, the ID associated with that subsection is in fact theirs
                                $currentSubsectionIdsForSection = SafetyManualSubsection::where('section_id', '=', $section->section_id)->lists('subsection_id');

                                foreach($inputSection['subsections'] as $inputSubsection) {
                                    if ($inputSubsection['subsection_id'] || (!$inputSubsection['subsection_id'] && !$inputSubsection['isDeleted']) ){
                                        // Passed the useless item test -- now create a new subsection, edit the existing record, or delete the existing record.
                                        $subsection = SafetyManualSubsection::firstOrNew(array('subsection_id' => $inputSubsection['subsection_id'],
                                                                                               'section_id' => $section->section_id,
                                                                                               'safety_manual_id' => $safetyManual->safety_manual_id));

                                        if($subsection instanceof SafetyManualSubsection){
                                            if(!$inputSubsection['isDeleted']){
                                                // delete the subsection
                                                $subsection->setFields($inputSubsection);

                                                if($inputSubsection['showEditor']) {
                                                    // This method is extremely processor heavy and time consuming, so only run it if the user has voluntarily
                                                    // pressed the "Edit Subsection Content" button and made the editor appear, where they may or may not have made
                                                    // changes. Even though it's priminitive, it should decrease save time quite a bit.
                                                    $subsection->subsection_mobile_content = Self::convertSubsectionToMobileImageReferences($inputSubsection['subsection_content']);
                                                }

                                                // Always check to see if the content is empty (such as in situations where they add a new subsection with no content)
                                                if(!$inputSubsection['subsection_content']) {
                                                    $subsection->subsection_mobile_content = ""; // We don't want the field to be NULL or else the app will crash
                                                }
                                                
                                                $subsection->save();

                                                array_push($newCurrentPhotoIds, Self::getCurrentImageIdsForSubsection($inputSubsection['subsection_content'])); 
                                            }
                                            else if (in_array($subsection->subsection_id, $currentSubsectionIdsForSection)) {
                                                // By this point we already know that the subsection has been marked for deletion, so just check that they are actually allowed to delete 
                                                // the specific subsection_id in question before actually deleting it. 
                                                Self::deleteSubsectionWithSubsectionId($subsection->subsection_id);
                                            }
                                        }
                                    }
                                }
                            }
                            else if (in_array($section->section_id, $currentSectionIds)) {
                                // By this point we already know that the section has been marked for deletion, so just check that they are actually allowed to delete 
                                // the specific section_id in question before actually deleting it. 
                                Self::deleteSectionWithSectionId($section->section_id);
                            }
                        }
                    }
                }

                Self::removeUnusedSafetyManualPhotos($safetyManual->safety_manual_id, $newCurrentPhotoIds);
            }

            Self::increaseSafetyManualVersionNumber($safetyManual);
            Self::addSafetyManualRevisionRecord($safetyManual, $input['revision_description']);
            $safetyManual->save();
        });

        if ($error == NULL) {
            return self::buildAjaxResponse(TRUE, 'Edit Successful.'); 
        } else {
            return self::buildAjaxResponse(FALSE, 'Edit Failed. Please try again. If the problem persists, please contact White Knight Support.');
        }
    }

    // This list could be compared to what is in the safety manual folder so that items can be deleted if necessary 
    private function getCurrentImageIdsForSubsection($subsectionContent) {
        $dom = new DOMDocument;

        if(!$subsectionContent){
            return array();
        }

        //Set libxml_use_internal_errors(true) before calling loadHTML. This will prevent errors from bubbling up to your default error handler. And you can then get at them (if you desire) using other libxml error functions.       
        libxml_use_internal_errors(true);
        $dom->loadHTML($subsectionContent);
        libxml_use_internal_errors(false);

        $images = $dom->getElementsByTagName('img');

        $photoNames = [];

        foreach ($images as $image) {
            // Get the src
            $src = $image->getAttribute('src');
            //Break up the src
            $pieces = explode("/", $src);
            // push onto photoNames the name of the image
            $photoName = array_pop($pieces);
            array_push($photoNames, $photoName);
        }

        return $photoNames;
    }

    // Eventually this would be asynch. 
    private function removeUnusedSafetyManualPhotos($safetyManualId, $photoIdsInContent) {
        $success = true;
        $sm = new StorageManager();

        $allPhotoIdsForManual = Photo::where('imageable_type', 'SafetyManual')
                                     ->where('imageable_id', $safetyManualId)
                                     ->lists('name');


        // If there are still photos in the manual
        if (!empty($photoIdsInContent)) {
           // The photo ids in the subsections is a multidimensional array, and this flattens it to prepare it for the array diff below.
           $photoIdsInContent = call_user_func_array('array_merge', $photoIdsInContent); 
           $deletablePhotoIds = array_diff($allPhotoIdsForManual, $photoIdsInContent);
        } else {
            // No photos in content - photos were deleted
            $deletablePhotoIds = $allPhotoIdsForManual;
        }

        foreach ($deletablePhotoIds as $photoId) {
            $photo = Photo::where('name', $photoId)->first();

            if($sm->deletePhoto($photo)){
                $photo->delete(); //remove from db 
            } else {
                // Do something
            }
        }

        return $success;
    }

    // This function changes all external image references to local image references so that the iOS app can use its locally stored images.  
    // By doing it this way, the webapp already knows what the image name will be and the iOS app aleady knows what the image name will be.
    // When the image is saved on the device it is saved as imagename.jpg (ex. 782g2gtd2vgdghe.jpg)
    private function convertSubsectionToMobileImageReferences ($content) {
        $dom = new DOMDocument;

        if(!$content){
            return '';
        }   

        //Set libxml_use_internal_errors(true) before calling loadHTML. This will prevent errors from bubbling up to your default error handler. And you can then get at them (if you desire) using other libxml error functions.       
        libxml_use_internal_errors(true);
        $dom->loadHTML($content);
        libxml_use_internal_errors(false);

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $image) {
            // Get the src
            $src = $image->getAttribute('src');
            //Break up the src
            $pieces = explode("/", $src);
            //Only take the last part of the src which is the image name
            $photoName = array_pop($pieces);
            //print_r($image->getAttribute('src'));
            $image->setAttribute('src', $photoName . ".jpg");
        }

        // This makes the text bigger on the iOS device so that it can be read without zooming.
        $node = $dom->getElementsByTagName('body')->item(0);
        $node->setAttribute('style','font-size: 3em');

        $html = $dom->saveHTML();

        return $html;
    }

    private function increaseSafetyManualVersionNumber ($safetyManual) {
        $majorVersion = $safetyManual->major_version_number;

        // First save event for new manual
        if($majorVersion == 0) {
            $safetyManual->major_version_number = 1;
            $safetyManual->minor_version_number = 0;
            return;
        }

        $minorVersion = $safetyManual->minor_version_number;
        $minorVersion++;
        $safetyManual->minor_version_number = $minorVersion;

        // Right now it can only go up to 999 -- perform the rollover 
        if($minorVersion == 1000) {
            $safetyManual->minor_version_number = 0;
            $safetyManual->major_version_number = $majorVersion + 1;
        }
    }

    private function addSafetyManualRevisionRecord ($safetyManual, $revisionDescription) {
        $loggedUser = Auth::user();
        $revision = new SafetyManualRevision;
        $revision->safety_manual_id = $safetyManual->safety_manual_id;
        $revision->major_version_number = $safetyManual->major_version_number;
        $revision->minor_version_number = $safetyManual->minor_version_number;
        $revision->admin_id = $loggedUser->admin_id;
        $revision->ip_address = Request::getClientIp();
        $revision->revision_description = $revisionDescription;
        $revision->save();
    }

    private function deleteSectionWithSectionId ($sectionId) {
        $section = SafetyManualSection::findOrFail($sectionId);

        if ($section) {   
            $section->delete(); // In the future this would likely be a soft delete for audit purposes
        }
    }

    private function deleteSubsectionWithSubsectionId ($subsectionId) {
        $subsection = SafetyManualSubsection::findOrFail($subsectionId);

        if ($subsection) {
            // In the future this would likely be a soft delete for audit purposes
            $subsection->delete();
        }
    }
    
    // Exports a PDF of the manual based on the input parameters
    public function ExportSafetyManual($sectionId = NULL, $subsectionId = NULL) {
        $loggedUser = Auth::user();
        $safetyManual = SafetyManual::where('company_id', '=', $loggedUser->company_id)->first();

        if($safetyManual) {
            $data['manual'] = $safetyManual;
            // All fields should be filled (unsaved items have an id of 0)
            if(!isset($sectionId) && !isset($subsectionId)){
                //Export the entire manual
                $html = View::make('webApp::safety-manual.export-manual', $data)->render();
                $pdf = PDF::loadHTML($html)->setOrientation('landscape');
                $filename = 'Safety-Manual-' . date('d-M-Y') . '.pdf';
            } 
            else if (isset($sectionId) && !isset($subsectionId)) {
                // Export the section
                $data['section'] = SafetyManualSection::where('safety_manual_id', '=', $safetyManual->safety_manual_id)
                                                      ->where('section_id', '=', $sectionId)
                                                      ->first();

                // TODO: CHECK THAT THIS WAS POPULATED WITH A RECORD

                $html = View::make('webApp::safety-manual.export-section', $data)->render();
                $pdf = PDF::loadHTML($html)->setOrientation('landscape');
                $filename = 'Safety-Manual-Section-' . date('d-M-Y') . '.pdf';
            }
            else if(isset($sectionId) && isset($subsectionId)) {
                // Export the subsection
                $data['subsection'] = SafetyManualSubsection::where('safety_manual_id', '=', $safetyManual->safety_manual_id)
                                                      ->where('section_id', '=', $sectionId)
                                                      ->where('subsection_id', '=', $subsectionId)
                                                      ->first();
                
                // TODO: CHECK THAT THIS WAS POPULATED WITH A RECORD

                $html = View::make('webApp::safety-manual.export-subsection', $data)->render();
                $pdf = PDF::loadHTML($html)->setOrientation('landscape');
                $filename = 'Safety-Manual-Subsection-' . date('d-M-Y') . '.pdf';
            }

            return $pdf->download($filename);
        }   
    }

    public function uploadImageAction(){
        if(!Auth::user()) {
            return Redirect::to('/'); // see if this can be moved out of the controller
        }

        if ( Input::hasFile('imagefile')) { 
            $inputFile = Input::file('imagefile');
        } else {
            $error = "It looks like you didn't upload anything. Please close this box and try again.";
            return View::make('webApp::safety-manual._image-upload', array('location' => NULL, 'error' => $error));
        }

        // Allow images and PDFs
        if(!substr($inputFile->getMimeType(), 0, 5) == 'image' || !$inputFile->getMimeType() == 'application/pdf') {
            $error = "You have submitted an invalid file type. Please try again. (Filetype uploaded: " . $inputFile->getMimeType() . ")";
            return View::make('webApp::safety-manual._image-upload', array('location' => NULL, 'error' => $error));
        }

        // Declare items which will be used for all allowed file types
        $company = Auth::user()->company;
        $companyId = $company->company_id;
        $safetyManual = SafetyManual::getFullManualForCompany($companyId);

        if(!$safetyManual){
            die('You do not have access to this functionality. Your IP address has been logged.');
        }

        $companyLevel = Config::get('api::storagePaths.companyLevel');
        $pathToCompanySMFolder = $companyLevel.'/'.$companyId.'/safety-manual/';

        // Create the safety manual directory
        if(!is_dir("{$companyLevel}/{$companyId}/safety-manual")){
            mkdir("{$companyLevel}/{$companyId}/safety-manual", 0777, true);
        }
        
        // PDF -- WARING: THIS DOES NOT CURRENTLY HAVE A PAGE LIMIT 
        if($inputFile->getMimeType() == 'application/pdf'){
            try 
            {
                // throw new Exception("TEST EXCEPTION"); 
                $tempName = md5(time().rand()) . '.pdf';
                $tempDest = Config::get('api::storagePaths.companyLevel');
                $fullPath = "{$tempDest}/{$tempName}";  
                // Stores the temp file in the data directory
                $inputFile->move($tempDest, $tempName);

                $im = new Imagick();
                $im->setResolution(200, 200); 
                $im->readimage("{$tempDest}/{$tempName}"); // This reads all of the contents of the file
                $numPages = $im->getNumberImages();
                $im->setImageFormat('jpeg');  
                $tempImagesName = md5(time().rand()); // the unique identifier for one or more images that come out of the PDF
                $im->writeimages($tempDest . "/" . $tempImagesName . ".jpg", false); // This false flag make it so that each image is saved as a distict file
                $im->clear();
                $im->destroy();
 
                $allImagePaths = array(); // Storage for all records of each temp image path
                
                if ($numPages > 1){
                    // Multiple images which will be named(ex. jasdhjhed-0.jpg, jasdhjhed-1.jpg)
                    for($i = 0; $i < $numPages; $i++) {
                        $imgDir =  $tempDest . "/" . $tempImagesName . "-" . $i . ".jpg";
                        $im = new Imagick($imgDir);
                        $im->scaleImage(1000, 0);
                        $im->writeImage($imgDir);
                        $im->clear();
                        $im->destroy();

                        array_push($allImagePaths, $imgDir);
                    }
                } else if($numPages == 1){
                    //If there is only one image saved, it will be named (ex. jasdhjhed.jpg)
                    $imgDir =  $tempDest . "/" . $tempImagesName . ".jpg"; 
                    $im = new Imagick($imgDir);
                    $im->scaleImage(1000, 0);
                    $im->writeImage($imgDir);
                    $im->clear();
                    $im->destroy();
                    array_push($allImagePaths, $imgDir);
                } 

                // Delete the PDF file --  it is no longer needed
                unlink("{$tempDest}/{$tempName}");

                // Save DB record and move temporary files
                if(count($allImagePaths)){
                    $allPhotoWebURLs = array();
                    foreach($allImagePaths as $imgPath){
                        $safetyManualPhotoDetails = array('path' => $pathToCompanySMFolder, 'name' => Photo::generatePhotoName()); 
                        $photo = new Photo();
                        $photo->path = $safetyManualPhotoDetails['path'] . $safetyManualPhotoDetails['name'];
                        $photo->name = $safetyManualPhotoDetails['name'];
                        $photo->imageable_id = $safetyManual->safety_manual_id;
                        rename($imgPath, $photo->path);
                        $safetyManual->photos()->save($photo);
                        $photo->save();
                        $photoLocation = Photo::generic($photo->name);

                        array_push($allPhotoWebURLs, $photoLocation);
                    }
                    
                    // return an array of photo locations that can be injected into TinyMCE  
                    //return View::make('webApp::safety-manual._image-upload', array('location' => NULL, 'locationArray' => json_encode($allPhotoWebURLs), 'error' => NULL)); 
                    return View::make('webApp::safety-manual._image-upload', array('location' => NULL, 'locationArray' => $allPhotoWebURLs, 'error' => NULL)); 
                }  else {
                    return View::make('webApp::safety-manual._image-upload', array('location' => NULL, 'locationArray' => NULL, 'error' => 'Unable to save photos from PDF. Please try again. If the problem persists, let us know.')); 
                }                   
            }
            catch (Exception $e) {
                $error = "There was a problem processing the PDF file. (" . $e->getMessage() . ")";
                return View::make('webApp::safety-manual._image-upload', array('location' => NULL, 'error' => $error));
            } 
        }

        // **********************************************************************
        // IMAGE PROCESSING -- if it reaches this point, the upload is an image. 
        try 
        {
            $imgPath = Self::uploadImageFile();

            if($imgPath) {
                $safetyManualPhotoDetails = array('path' => $pathToCompanySMFolder, 'name' => Photo::generatePhotoName()); 
                $photo = new Photo();
                $photo->path = $safetyManualPhotoDetails['path'] . $safetyManualPhotoDetails['name'];
                $photo->name = $safetyManualPhotoDetails['name'];
                $photo->imageable_id = $safetyManual->safety_manual_id;
                rename($imgPath, $photo->path);
                $safetyManual->photos()->save($photo);
                $photo->save();
                $photoLocation = Photo::generic($photo->name);

                return View::make('webApp::safety-manual._image-upload', array('location' => $photoLocation, 'error' => NULL));
            } 
            else {
                // Would probably want to communicate this to the user somehow
                $error = 'Unable to save image. Please close this box and try again. If the problem persists, please let us know.';
                return View::make('webApp::safety-manual._image-upload', array('location' => NULL, 'error' => $error));
            }
        }
        catch (Exception $e) {
            $error = 'Unable to upload image. Please close this box and try again. If the problem persists, please let us know. (' . $e->getMessage() . ")";
            return View::make('webApp::safety-manual._image-upload', array('location' => NULL, 'error' => $error));
        } 
    }

    public function uploadImageFile() 
    { 
        // Note: GD library is required for this function
        // apt-get install php5-gd

        // new unique filename
        $tempName = md5(time().rand());
        $tempDest = Config::get('api::storagePaths.companyLevel');
        $fullPath = "{$tempDest}/{$tempName}";

        // the Image Facade is Intervention image manipulation tool
        $img = Image::make(Input::file('imagefile')); 

        // Create the image file and restrict size 
        if($img->width() > 800) {
            // resize the image to a width of 400 and constrain aspect ratio (auto height)
            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        } 

        $img->save($fullPath);
        
        return $fullPath;
    }

    public function mailSafetyManual() {
        $loggedUser = Auth::user();

        if(!$loggedUser) {
            return Redirect::to('/');
        }

        $email = Input::get('email');
        $sectionId = Input::get('sectionId');
        $subsectionId = Input::get('subsectionId');
        $safetyManual = SafetyManual::getFullManualForCompany($loggedUser->company_id);

        if($safetyManual) {

            $data['manual'] = $safetyManual;
            $nameForDoc = $loggedUser->company->company_name . '-Safety-Manual-' . date('d-M-Y');

            if(!$sectionId && !$subsectionId) {
                // Email the whole manual 
                $html = View::make('webApp::safety-manual.export-manual', $data)->render();
                $pdf = PDF::loadHTML($html)->setOrientation('landscape');
                $file = StorageManager::saveTempPDFDocument($pdf, $nameForDoc);
                $data = array('email' => $email,
                    'object' => $safetyManual,
                    'type' => 'Safety Manual',
                    'attach' => $file);                
            } 
            else if($sectionId && !$subsectionId) {
                // Email the section
                $data['section'] = SafetyManualSection::where('safety_manual_id', '=', $safetyManual->safety_manual_id)
                                                      ->where('section_id', '=', $sectionId)
                                                      ->first();

                $html = View::make('webApp::safety-manual.export-section', $data)->render();
                $pdf = PDF::loadHTML($html)->setOrientation('landscape');
                $file = StorageManager::saveTempPDFDocument($pdf, $nameForDoc);
                $data = array('email' => $email,
                    'object' => $safetyManual,
                    'type' => 'Safety Manual - ' . $data['section']->section_title,
                    'attach' => $file); 
            }
            else if($sectionId && $subsectionId) {
                // Email the subsection
                $data['subsection'] = SafetyManualSubsection::where('safety_manual_id', '=', $safetyManual->safety_manual_id)
                                                      ->where('section_id', '=', $sectionId)
                                                      ->where('subsection_id', '=', $subsectionId)
                                                      ->first();

                $html = View::make('webApp::safety-manual.export-subsection', $data)->render();
                $pdf = PDF::loadHTML($html)->setOrientation('landscape');
                $file = StorageManager::saveTempPDFDocument($pdf, $nameForDoc);
                $data = array('email' => $email,
                    'object' => $safetyManual,
                    'type' => 'Safety Manual - ' . $data['subsection']->subsection_title,
                    'attach' => $file); 
            }

            return Self::sendAttachmentSafetyManualEmail($data);   
        }
        else {
            die('You do not have permission to perform this action. Your IP address has been logged');
        }        
    }
}