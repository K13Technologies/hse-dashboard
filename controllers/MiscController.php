<?php

class MiscController extends AjaxController {
    
    public function indexView(){
        $loggedUser = Auth::user();
        $data = array();
        if ($loggedUser->isAdmin()){
            return Redirect::to('company-management');
            //return View::make('webApp::indexes.admin',$data);
        }

        $treesSaved = Company::getTotalTreesSavedForCompany($loggedUser);
        $wholeTreesCount = floor($treesSaved); 
        $treesCountFraction = $treesSaved - $wholeTreesCount; 

        $data['wholeTreesSavedCount'] = $wholeTreesCount;
        $data['treesCountPercentage'] = round($treesCountFraction * 100, 2); // Bring it to a percentage and limit to 2 decimal places
        $data['recentFormActivities'] = Company::getRecentFormActivityForCompany($loggedUser);
        $data['expiringTickets'] = Company::getTicketExpiryNotificationsForCompany($loggedUser, 10);
        
        return View::make('webApp::indexes.dashboard', $data);
    }
    
    public function downloadAppAction(){
         $appPath = Config::get('api::storagePaths.iOSapp');
         return Response::download($appPath);
    }
    
    public function faqPageView(){
        $data['shouldAdd'] = FALSE;
        $data['faqs'] = FAQ::all();
        return View::make('webApp::faq/view',$data);
    }
    
    public function faqAddPageView(){
        $data['shouldAdd'] = TRUE;
        $data['faqs'] = FAQ::all();
        return View::make('webApp::faq/view',$data);
    }
    
    public function faqAddPageAction(){
        if (trim(Input::get('question'))!='' && trim(Input::get('answer'))!=''){
            FAQ::create(Input::all());
            return Redirect::to('faq/manage');
        }else{
            return Redirect::to('faq/manage')->with('addError','All fields are mandatory');
        }
    }
    
    public function faqEditPageAction(){
        if (Input::get('question') && Input::get('answer')){
            $faq = FAQ::find(Input::get('faq_id'));
            $faq->question = trim(Input::get('question'));
            $faq->answer = trim(Input::get('answer'));
            $faq->save();
            return Redirect::to('faq/manage');
        }else{
            return Redirect::to('faq/manage')->with('editError',array('id'=>Input::get('faq_id'),'text'=>'All fields are mandatory'));
        }
    }
    
    public function faqDeletePageAction(){
        $faq = FAQ::find(Input::get('delete_faq_id'));
        if ($faq instanceOf FAQ){
            $faq->delete();
        }
        return self::buildAjaxResponse(TRUE);
    }
}