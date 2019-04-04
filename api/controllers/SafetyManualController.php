<?php

class SafetyManualController extends ApiController {
    
    /**
     * This handles the get Vehicle list call
     * 
     * @return JSONResponse 
     */
    public function getSectionList(){
        // In the future we MUST use this..
        // $worker = Worker::getByAPIKey(Input::get('apiKey'));
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $safetyManual = SafetyManual::where('company_id', '=', $worker['company_id'])->first();
        //$safetyManual = SafetyManual::getFullManualForCompany($worker['company_id']);

        if($safetyManual){
            $sectionList = $safetyManual->sections; 
            return $this->createResponse(200, $sectionList);
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

    public function getSafetyManualPhoto($photoId){
        $photo = Photo::getPhotoByPhotoName($photoId);
        return $this->createImageResponse($photo->path);
    } 
}