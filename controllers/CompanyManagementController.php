<?php
use Carbon\Carbon;

class CompanyManagementController extends AjaxController {
    
    const VIEWPORT_IMAGE_WIDTH = 570;

    public function manageCompaniesView(){
        $loggedUser = Auth::user();
        $data['admins'] = Admin::getAdminListForAdmin($loggedUser);
        $data['user'] = $loggedUser;
        if ($loggedUser->role_id == UserRole::ADMIN){
            $data['companies'] = Company::orderBy('company_name')->get()->all();
            return View::make('webApp::company-management.manage-companies',$data);
        }else{
            $company = $loggedUser->company;
            $company->load('divisions');
            $data['company'] = $company;
            return View::make('webApp::company-management.manage-company-structure',$data);
        }
    }
    
    public function manageOneCompanyView($companyId){
        $company = Company::find($companyId);
        if (!$company instanceof Company){
            App::abort(404);
        }
        $company->load('divisions','phoneNumbers','radioStations');
        $data['company'] = $company;
        Auth::user()->company_id = $companyId;
        return View::make('webApp::company-management.manage-company-structure',$data);
    }
    
    // This is called when a super user adds an enterprise company. 
    public function addEnterpriseCompanyAction(){
        if (!Request::ajax()){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $input = Input::all();
        $validator = Validator::make($input, 
            array(
                'company_name' => 'required|unique:companies',
            ) 
       );

        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('company_name'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }
        
        $company = new Company();
        $company->company_name = $input['company_name'];
        $company->is_enterprise = true;
        $company->subscription_ends_at = Carbon::now()->addDays(365);
        $company->save();

        return self::buildAjaxResponse(TRUE, $company);
    }
    
    public function editCompanyAction(){
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = array();
        
        foreach (Input::all() as $key=>$val){
            $key = str_replace('edit_', '', $key);
            $input[$key] = $val;
        }
        $company = Company::find($input['company_id']);
        
        if(array_key_exists('subscription_ends_at', $input)){
            $input['company_name'] = $company->company_name;
        }
        $validator = Validator::make($input, 
            array(
                'company_name' => "required|unique:companies,company_name,{$company->company_id},company_id",
                'subscription_ends_at' => 'date'
            ) 
        );
        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('company_name'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }
        
        $company = Company::find($input['company_id']);
        $company->company_name = $input['company_name'];

        if ($company->is_enterprise){
            $company->subscription_ends_at = $input['subscription_ends_at'].substr($company->subscription_ends_at, 10);
        }elseif($company->onTrial()){
            // Admin wants to edit the trial end date if this is set 
            // This check ensures that the request doesn't fail if the user is only modifying the company name
            if(isset($input['subscription_ends_at'])){
                //  Note that the input field is called subscription_ends_at, but really the field we are chaning in the DB
                // is trial_ends_at. I suppose this was done so that the same modal could be used for trial dates and subscription dates
                $company->trial_ends_at = $input['subscription_ends_at'].substr($company->trial_ends_at, 10); 
            }
        }

        $company->save();
        return self::buildAjaxResponse(TRUE, $company);
    }
    
    public function deleteCompanyAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $companyId = str_replace('delete_', '', Input::get('delete_company_id', null));
        $company = Company::find($companyId);
        if (!$company instanceOf Company){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }else{
            $company->forceDelete();
        }
    }
    
    public function getCompanyProjectsAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $companyId = Input::get('company', null);
        $company = Company::find($companyId);
        if (!$company instanceof Company){
             return self::buildAjaxResponse(TRUE,array());
        }
        if (!Auth::user()->isAdmin() && Auth::user()->company_id!=$companyId){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $groups = Group::getAllForCompany($companyId, Group::GET_ALL_INFO);
        return self::buildAjaxResponse(TRUE,$groups);
    }
    
    public function getCompanyDivisionsAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $companyId = Input::get('company', null);
        $company = Company::find($companyId);
        if (!$company instanceof Company){
             return self::buildAjaxResponse(TRUE,array());
        }
        if (!Auth::user()->isAdmin() && Auth::user()->company_id!=$companyId){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        return self::buildAjaxResponse(TRUE,$company->divisions);
    }
    
    public function getDivisionBusinessUnitsAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $divisionId = Input::get('division', null);
        $division = Division::find($divisionId);
        if (!$division instanceof Division){
             return self::buildAjaxResponse(TRUE,array());
        }
        if (!Auth::user()->isAdmin() && $division->company_id != Auth::user()->company_id){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        return self::buildAjaxResponse(TRUE,$division->businessUnits);
    }
    
    public function getBusinessUnitGroupsAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $businessUnitId = Input::get('business_unit', null);
        $businessUnit = BusinessUnit::find($businessUnitId);
        if (!$businessUnit instanceof BusinessUnit){
             return self::buildAjaxResponse(TRUE,array());
        }
        if ( !Auth::user()->isAdmin() &&
                $businessUnit->division->company_id != Auth::user()->company_id){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        return self::buildAjaxResponse(TRUE,$businessUnit->groups);
    }
    
    public function addDivisionAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = Input::all();
        if(!Auth::user()->isAdmin()){
            $input['company_id'] = Auth::user()->company_id;
        }
        $validator = Validator::make($input, 
                array(
                    'division_name' => 'required',
                    'company_id' => 'required'
                ) 
           );
        if ($validator->fails()){
            return self::buildAjaxResponse(FALSE, 'Please fill in all the fields');
        }
        
        $division = new Division();
        $division->division_name = $input['division_name'];
        $division->company_id = $input['company_id'];
  
        if(!$division->isUniqueForCompany()){
            return self::buildAjaxResponse(false, "Division already exists");
        }else{
            $division->save();
            return self::buildAjaxResponse(TRUE, $division);
        }
    }
    
    public function editDivisionAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = array();
        foreach (Input::all() as $key=>$val){
            $key = str_replace('edit_', '', $key);
            $input[$key] =  $val;
        }
        
        $validator = Validator::make($input, 
            array(
                'division_name' => 'required',
            ) 
        );
        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('division_name'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }

        $division = Division::find($input['division_id']);
        if (!$division instanceof Division){
             return self::buildAjaxResponse(TRUE);
        }
        if ($division->company_id != Auth::user()->company_id && 
                !Auth::user()->isAdmin()){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        
        $division->division_name = $input['division_name'];
        if(!$division->isUniqueForCompany()){
            return self::buildAjaxResponse(false, "Division already exists");
        }else{
            $division->save();
            return self::buildAjaxResponse(TRUE, $division);
        }  
    }
    
    public function deleteDivisionAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $divisionId = str_replace('delete_division_', '', Input::get('delete_division_id', null));
        
        $division = Division::find($divisionId);
        if (!$division instanceof Division){
             return self::buildAjaxResponse(FALSE);
        }
        if ($division->company_id != Auth::user()->company_id && 
                !Auth::user()->isAdmin()){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        if ($division->businessUnits()->count()){
            return self::buildAjaxResponse(false, "This Division still has business units");
        }else{
            $division->delete();
            return self::buildAjaxResponse(TRUE, $division);
        }   
    }
    
    
     public function addBusinessUnitAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = Input::all();
        $validator = Validator::make($input, 
                array(
                    'business_unit_name' => 'required',
                    'division_id' => 'required',
                ) 
           );
        if ($validator->fails()){
            return self::buildAjaxResponse(FALSE, 'Please fill in all the fields');
        }
        $division = Division::find($input['division_id']);
        if( !$division instanceof Division or 
            ($division->company_id != Auth::user()->company_id  && 
                !Auth::user()->isAdmin())){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $businessUnit = new BusinessUnit();
        $businessUnit->business_unit_name = $input['business_unit_name'];
        $businessUnit->division_id = $input['division_id'];
        if (!$businessUnit->isUniqueForDivision()){
            return self::buildAjaxResponse(FALSE, 'Business Unit already exists');
        }
        $businessUnit->save();
        return self::buildAjaxResponse(TRUE, $businessUnit);   
    }
    
    public function editBusinessUnitAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = array();
        foreach (Input::all() as $key=>$val){
            $key = str_replace('edit_', '', $key);
            $input[$key] = $val;
        }
        
        $validator = Validator::make($input, 
            array(
                'business_unit_name' => 'required',
            ) 
        );
        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('business_unit_name'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }
        
        $division = Division::find($input['division_id']);
        if( !$division instanceof Division or 
            ($division->company_id != Auth::user()->company_id  && 
                !Auth::user()->isAdmin())){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $businessUnit = BusinessUnit::find($input['business_unit_id']);
        
        if( !$businessUnit instanceof BusinessUnit or 
            ($businessUnit->division->company_id != Auth::user()->company_id && 
                !Auth::user()->isAdmin())){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        
        $businessUnit->business_unit_name = $input['business_unit_name'];
        $businessUnit->division_id = $input['division_id'];
        if (!$businessUnit->isUniqueForDivision()){
            return self::buildAjaxResponse(FALSE, 'Business Unit already exists');
        }
        $businessUnit->save();
        return self::buildAjaxResponse(TRUE, $businessUnit);
    }
    
    public function deleteBusinessUnitAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $businessUnitId = str_replace('delete_', '', Input::get('delete_business_unit_id', null));
        
        $businessUnit = BusinessUnit::find($businessUnitId);
        
        if( !$businessUnit instanceof BusinessUnit or 
                ($businessUnit->division->company_id != Auth::user()->company_id && 
                !Auth::user()->isAdmin())){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        if ($businessUnit->groups()->count()){
            return self::buildAjaxResponse(FALSE, "This Business Unit still has projects attached to it");
        }else{
            $businessUnit->delete();
            return self::buildAjaxResponse(TRUE,$businessUnit);
        }
    }
    
    
      public function addGroupAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = Input::all();
        $validator = Validator::make($input, 
                array(
                    'group_name' => 'required',
                    'business_unit_id' => 'required',
                ) 
           );
        if ($validator->fails()){
            return self::buildAjaxResponse(FALSE, 'Please fill in all the fields');
        }
        $businessUnit = BusinessUnit::find($input['business_unit_id']);
        if( !$businessUnit instanceof BusinessUnit or 
                ($businessUnit->division->company_id != Auth::user()->company_id  && 
                !Auth::user()->isAdmin())){
        }
        $group = new Group();
        $group->group_name = $input['group_name'];
        $group->business_unit_id = $input['business_unit_id'];
        if (!$group->isUniqueForBU()){
            return self::buildAjaxResponse(FALSE, 'Group already exists');
        }
        $group->save();
        return self::buildAjaxResponse(TRUE, $group);
    }
    
    public function editGroupAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = array();
        foreach (Input::all() as $key=>$val){
            $key = str_replace('edit_', '', $key);
            $input[$key] = $val;
        }
        
        $validator = Validator::make($input, 
            array(
                'group_name' => 'required',
            ) 
        );
        if ($validator->fails()){
            return self::buildAjaxResponse(FALSE, 'Please fill in all the fields');
        }
        
        $businessUnit = BusinessUnit::find($input['business_unit_id']);
        if( !$businessUnit instanceof BusinessUnit or 
            ($businessUnit->division->company_id != Auth::user()->company_id  && 
                !Auth::user()->isAdmin())){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $group = Group::find($input['group_id']);
        
        if( !$group instanceof Group or 
            ($group->businessUnit->division->company_id != Auth::user()->company_id  && 
                !Auth::user()->isAdmin())){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        
        $group->group_name = $input['group_name'];
        $group->business_unit_id = $input['business_unit_id'];
        if (!$group->isUniqueForBU()){
            return self::buildAjaxResponse(FALSE, 'Group already exists');
        }
        $group->save();
        return self::buildAjaxResponse(TRUE, $group);
    }
    
    public function deleteGroupAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $groupId = str_replace('delete_', '', Input::get('delete_group_id', null));
        
        $group = Group::find($groupId);
        
        if( !$group instanceof Group or 
            ($group->businessUnit->division->company_id != Auth::user()->company_id  && 
                !Auth::user()->isAdmin())){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        if ($group->workers()->count()){
            return self::buildAjaxResponse(FALSE, "You can't delete a project that still has workers attached to it");
        }else{
            $group->delete();
            return self::buildAjaxResponse(TRUE,$group);
        }
    }
    
    public function logoView(){
        return View::make('webApp::company-logo.upload');
    }
    
    public function uploadLogoAction(){
        $shouldProcess = Input::get('photo_action')=='keep'?false:true;
        if ($shouldProcess){
            if (Input::get('x2')=='' or Input::get('y2')==''){
                $imgPath = $this->uploadImageFile();
            }else{
                $imgPath = $this->processImageFile();
            }
        }else{
            $imgPath = $this->uploadImageFile();
        }
        $companyLevel = Config::get('api::storagePaths.companyLevel');
        $company = Auth::user()->company;
        $companyId = $company->company_id;
        if(!is_dir("{$companyLevel}/{$companyId}")){
            mkdir("{$companyLevel}/{$companyId}", 0777);
        }
        $path = $companyLevel.'/'.$companyId.'/logo';
        rename($imgPath, $path);
        
        $companyPhotoDetails = array('path' => $path, 'name' => Photo::generatePhotoName()); 
        if ( ! $company->logo() instanceof Photo){
            $photo = new Photo();
            $photo->path = $companyPhotoDetails['path'];
            $photo->name = $companyPhotoDetails['name'];
            $company->photos()->save($photo);
        }
        return Redirect::to('upload-company-logo')->with('message','Logo uploaded successfully');
    }
    
    public function uploadImageFile() { // Note: GD library is required for this function
        if ( Input::hasFile('image_file') && Input::file('image_file')->getSize() < 2048 * 1024) {
            // new unique filename
            $tempName = md5(time().rand());
            $tempDest = Config::get('api::storagePaths.companyLevel');
            $fullPath = "{$tempDest}/{$tempName}";
            Input::file('image_file')->move($tempDest, $tempName);
            return $fullPath;
        }
    }
    
    public function processImageFile() { // Note: GD library is required for this function
        $fileDim = explode(' x ', Input::get('filedim'));
        $w = (int)Input::get('w');
        $h = (int)Input::get('h');
        $picWidth = (int)$fileDim[0];
        if ($picWidth > self::VIEWPORT_IMAGE_WIDTH){
            $rate = $picWidth / self::VIEWPORT_IMAGE_WIDTH;
        }else{
            $rate = 1;
        }
        $intendedWidth = $rate*$w;
        $intendedHeight = $rate*$h; // desired image result dimensions
        $startX = $rate*(float)Input::get('x1');
        $startY = $rate*(float)Input::get('y1');
        
        // if no errors and size less than 250kb
        if ( Input::hasFile('image_file') && Input::file('image_file')->getSize() < 2048 * 1024) {
            // new unique filename
            $tempName = md5(time().rand());
            // move uploaded file into cache folder
            $tempDest = Config::get('api::storagePaths.companyLevel');
            $fullPath = "{$tempDest}/{$tempName}";
            Input::file('image_file')->move($tempDest, $tempName);

            // change file permission to 644
            @chmod($fullPath, 0644);
            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                $aSize = getimagesize($fullPath); // try to obtain image info
                
                if (!$aSize) {
                    @unlink($fullPath);
                    return;
                }

                // check for image type
                switch($aSize[2]) {
                    case IMAGETYPE_JPEG:
                        $sExt = '.jpg';

                        // create a new image from file
                        $sourceImg = @imagecreatefromjpeg($fullPath);
                        break;
                    case IMAGETYPE_PNG:
                        $sExt = '.png';

                        // create a new image from file
                        $sourceImg = @imagecreatefrompng($fullPath);
                        break;
                    default:
                        return;
                }
                @unlink($fullPath);
                // create a new true color image
                $resultImg = @imagecreatetruecolor( $intendedWidth, $intendedHeight );

                imagecolortransparent($resultImg, imagecolorallocatealpha($resultImg, 0, 0, 0, 127));
                imagealphablending($resultImg, false);
                imagesavealpha($resultImg, true);

                // copy and resize part of an image with resampling
                imagecopyresampled($resultImg, $sourceImg, 0, 0, $startX, $startY, $intendedWidth, $intendedHeight, $intendedWidth, $intendedHeight);

                // define a result image filename
                // $sResultFileName = $sTempFileName.$sExt;
                $sResultFileName = "{$tempDest}/{$tempName}";
                switch($sExt){
                    case '.jpg': imagejpeg($resultImg, $sResultFileName, 100); break;
                    case '.jpeg': imagejpeg($resultImg, $sResultFileName, 100); break;
                    case '.png': imagepng($resultImg, $sResultFileName); break;
                }
                // output image to file

                return $sResultFileName;
            }
        }
    }
    
    public function addHelplineAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = Input::all();
        if(trim($input['title'])=="" OR trim($input['value'])==""){
               return self::buildAjaxResponse(FALSE, 'Please fill in all the fields');
        }
        $validator = Validator::make($input, 
                array(
                    'title' => 'required|max:100',
                    'value' => 'required|max:50',
                ) 
           );
        
        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('title','value'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }
        
        $helpline = new Helpline();
        $helpline->company_id = trim($input['company_id']);
        $helpline->type = trim($input['type']);
        $helpline->value = trim($input['value']);
        $helpline->title = trim($input['title']);
        $helpline->save();
        return self::buildAjaxResponse(TRUE, $helpline); 
    }
    
    public function deleteHelplineAction(){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = Input::all();
        $helpline = Helpline::find($input['helpline_id']);
        $helpline->delete();
        return self::buildAjaxResponse(TRUE, $helpline);
    }
        
    public function viewStatsAction($groupId = NULL, $refDate = NULL, $timeframe = NULL){
        $loggedUser = Auth::user();
        
        if ($refDate == NULL){
            $refDate = date('Y-m-d',time()-$loggedUser->tz_offset);
            if ($groupId == NULL){
                $data['user'] = $loggedUser;
                $data['companies'] = Company::getFormatedForAdd();
                if (!$loggedUser->isAdmin()){
                    $groups = Group::getAllForCompany($loggedUser->company_id, Group::GET_ALL_INFO);
                }else{
                    $groups = array();
                }
                $data['exportButtons'] = false;
                $data['groupsForCompany'] = $groups;
                $data['group'] = NULL;
                $data['company'] = NULL;
                $data['refDate'] = $refDate;
                $data['stats'] = NULL;
                $data['timeframe'] = NULL;
                return View::make('webApp::company-management.stats',$data);
            }else{
                return Redirect::to("daily-signin/{$groupId}/{$refDate}");
            }
        }
        if (!$loggedUser->isAdmin()){
            $allGroups = Group::getAllForCompany($loggedUser->company_id);
        } 
        $group = Group::find($groupId);
        if (!$group instanceof Group){
//            return Redirect::to('/');
        }elseif (!$loggedUser->isAdmin() &&  !in_array($groupId, $allGroups)){
            return Redirect::to('/');
        }else{
            $stats['nearMiss'] = NearMiss::getStats($groupId,$refDate,$timeframe);
            $stats['hazard'] = Hazard::getStats($groupId,$refDate,$timeframe);
            $stats['po'] = PositiveObservation::getStats($groupId,$refDate,$timeframe);
            $stats['incident'] = Incident::getStats($groupId,$refDate,$timeframe);
            $stats['signin'] = DailySignin::getStats($groupId,$refDate,$timeframe);
            $stats['flha'] = Flha::getStats($groupId,$refDate,$timeframe);
            $stats['tailgate'] = Tailgate::getStats($groupId,$refDate,$timeframe);
            $stats['inspection'] = Inspection::getStats($groupId,$refDate,$timeframe);
            
            $data['exportButtons'] = true;
            $data['stats'] = $stats;
            $data['user'] = $loggedUser;
            $data['companies'] = Company::getFormatedForAdd();
            $data['group'] = $group;
            $data['company'] = $group->businessUnit->division->company;
            $data['groupsForCompany'] = Group::getAllForCompany($data['company']->company_id, Group::GET_ALL_INFO);
            $data['refDate'] = $refDate;
            $data['timeframe'] = $timeframe;
            return View::make('webApp::company-management.stats',$data);
        }
    }
    
    public function exportStatsAction($groupId, $refDate, $timeframe ){
        $loggedUser = Auth::user();
        if (!$loggedUser->isAdmin()){
            $allGroups = Group::getAllForCompany($loggedUser->company_id);
        } 
        $group = Group::find($groupId);
        if (!$group instanceof Group){
//            return Redirect::to('/');
        }elseif (!$loggedUser->isAdmin() &&  !in_array($groupId, $allGroups)){
            return Redirect::to('/');
        }else{
            $stats['nearMiss'] = NearMiss::getStats($groupId,$refDate,$timeframe);
            $stats['hazard'] = Hazard::getStats($groupId,$refDate,$timeframe);
            $stats['po'] = PositiveObservation::getStats($groupId,$refDate,$timeframe);
            $stats['incident'] = Incident::getStats($groupId,$refDate,$timeframe);
            $stats['signin'] = DailySignin::getStats($groupId,$refDate,$timeframe);
            $stats['flha'] = Flha::getStats($groupId,$refDate,$timeframe);
            $stats['tailgate'] = Tailgate::getStats($groupId,$refDate,$timeframe);
            $stats['inspection'] = Inspection::getStats($groupId,$refDate,$timeframe);
            
            $data['stats'] = $stats;
            $data['user'] = $loggedUser;
            $data['group'] = $group;
            $data['company'] = $group->businessUnit->division->company;
            $data['refDate'] = $refDate;
            $data['timeframe'] = $timeframe;
            
            $html = View::make('webApp::company-management.stats-export',$data)->render();
            
            $pdf = PDF::loadHTML($html)->setOrientation('landscape');
            $filename = ucfirst($timeframe)."_report_for_".$group->group_name."_until_".$refDate.'.pdf';
            return $pdf->download($filename);
        }
    }
    
    public function mailStatsAction($groupId, $refDate, $timeframe ){
        $loggedUser = Auth::user();
        
        $email = Input::get('email');
        
        if (!$loggedUser->isAdmin()){
            $allGroups = Group::getAllForCompany($loggedUser->company_id);
        } 
        $group = Group::find($groupId);
        if (!$group instanceof Group){
//            return Redirect::to('/');
        }elseif (!$loggedUser->isAdmin() &&  !in_array($groupId, $allGroups)){
            return Redirect::to('/');
        }else{
            $stats['nearMiss'] = NearMiss::getStats($groupId,$refDate,$timeframe);
            $stats['hazard'] = Hazard::getStats($groupId,$refDate,$timeframe);
            $stats['po'] = PositiveObservation::getStats($groupId,$refDate,$timeframe);
            $stats['incident'] = Incident::getStats($groupId,$refDate,$timeframe);
            $stats['signin'] = DailySignin::getStats($groupId,$refDate,$timeframe);
            $stats['flha'] = Flha::getStats($groupId,$refDate,$timeframe);
            $stats['tailgate'] = Tailgate::getStats($groupId,$refDate,$timeframe);
            $stats['inspection'] = Inspection::getStats($groupId,$refDate,$timeframe);
            
            $data['stats'] = $stats;
            $data['user'] = $loggedUser;
            $data['group'] = $group;
            $data['company'] = $group->businessUnit->division->company;
            $data['refDate'] = $refDate;
            $data['timeframe'] = $timeframe;
            
            $html = View::make('webApp::company-management.stats-export',$data)->render();
            
            $pdf = PDF::loadHTML($html)->setOrientation('landscape');
            $filename = ucfirst($timeframe)."_report_for_".$group->group_name."_until_".$refDate.'.pdf';
            
            $path = StorageManager::saveTempStats($pdf,$filename);

            $data = array('email'=>$email,
                          'project'=>$group->group_name,
                          'refDate' =>$refDate,
                          'timeframe' =>$timeframe,
                          'type'=>ucfirst($timeframe).' report for '.$group->group_name.' until '. $refDate,
                          'attach'=>$path);
            return self::sendAttachmentStatsEmail($data);
        }
    }
}