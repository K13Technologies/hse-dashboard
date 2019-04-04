<?php

class DailySigninWebController extends AjaxController {
    
    public function dailySigninAction($groupId = NULL, $signDate = NULL){
        $loggedUser = Auth::user();
        
        if ($signDate == NULL){
            $signDate = date('Y-m-d',time()-$loggedUser->tz_offset);
            if ($groupId == NULL){
                $data['user'] = $loggedUser;
                $data['companies'] = Company::getFormatedForAdd();
                if (!$loggedUser->isAdmin()){
                    $groups = Group::getAllForCompany($loggedUser->company_id, Group::GET_ALL_INFO);
                }else{
                    $groups = array();
                }
                $data['groupsForCompany'] = $groups;
                $data['group'] = NULL;
                $data['company'] = NULL;
                $data['signDate'] = $signDate;
                $data['signins'] = array();
                return View::make('webApp::daily-signin.view',$data);
            }else{
                return Redirect::to("daily-signin/{$groupId}/{$signDate}");
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
            $signins = DailySignin::getForGroupForDay($group, $signDate);
            $data['signins'] = $signins;
            $data['workers'] = $group->getAllWorkersForSignin();
            $data['user'] = $loggedUser;
            $data['companies'] = Company::getFormatedForAdd();
            $data['group'] = $group;
            $data['company'] = $group->businessUnit->division->company;
            $data['groupsForCompany'] = Group::getAllForCompany($data['company']->company_id, Group::GET_ALL_INFO);
            $data['signDate'] = $signDate;
            return View::make('webApp::daily-signin.view',$data);
        }
    }
    
    public function dailySigninExportAction($groupId, $signDate){
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
            $signins = array();
            if( $signDate!=NULL ){
                $signins = DailySignin::getForGroupForDay($group, $signDate);
            }
            $data['signins'] = $signins;
            $data['workers'] = $group->getAllWorkersForSignin();
            $data['user'] = $loggedUser;
            $data['companies'] = Company::getFormatedForAdd();
            $data['group'] = $group;
            $data['bu'] = $data['group']->businessUnit;
            $data['division'] = $data['bu']->division;
            $data['company'] = $data['division']->company;
            $data['signDate'] = $signDate;

            $html = View::make('webApp::daily-signin.export',$data)->render();

            $pdf = PDF::loadHTML($html)->setOrientation('landscape');
            $filename = "DailySignin_report_for_project_".$group->group_name."_on_".$signDate.".pdf";
            return $pdf->download($filename);
        }
    }
    
    
    public function dailySigninMailAction($groupId, $signDate){
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
            $signins = array();
            if( $signDate!=NULL ){
                $signins = DailySignin::getForGroupForDay($group, $signDate);
            }
            $data['signins'] = $signins;
            $data['workers'] = $group->getAllWorkersForSignin();
            $data['user'] = $loggedUser;
            $data['companies'] = Company::getFormatedForAdd();
            $data['group'] = $group;
            $data['bu'] = $data['group']->businessUnit;
            $data['division'] = $data['bu']->division;
            $data['company'] = $data['division']->company;
            $data['signDate'] = $signDate;

            $html = View::make('webApp::daily-signin.export',$data)->render();

            $pdf = PDF::loadHTML($html)->setOrientation('landscape');
            
            $filename = "DailySignin_report_for_project_".$group->group_name."_on_".$signDate;
            $path = StorageManager::saveTempDailySignin($pdf,$filename);

            $data = array('email'=>$email,
                          'project'=>$group->group_name,
                          'signDate' =>$signDate,
                          'type'=>"Daily Sign In for {$group->group_name} on $signDate",
                          'attach'=>$path);
            return self::sendAttachmentReportEmail($data);
        }
    }
}