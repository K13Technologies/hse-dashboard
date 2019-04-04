<?php

class UserManagementController extends AjaxController {
    
    public function manageUsersView(){
        $loggedUser = Auth::user();
        $loggedUserRole = $loggedUser->role;
        $data['admins'] = Admin::getAdminListForAdmin($loggedUser);
        $data['canAdd'] = UserRole::canAdd($loggedUserRole);
        $data['canEdit'] = UserRole::canEdit($loggedUserRole);
        $data['user'] = $loggedUser;
        $data['companies'] = Company::getFormatedForAdd();
        return View::make('webApp::user-management.manage-users',$data);
    }
    
    
    public function addUserAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP has been logged.');
        }
        $input = Input::all();
        if ($loggedUser->isAdmin()){
            $input['role_id'] = UserRole::COMPANY_ADMIN;
        }else{
            $input['company_id'] = $loggedUser->company_id;
        }
        
        if (!array_key_exists($input['role_id'], UserRole::canAdd($loggedUser->role))){
                die('You are not authorized to perform this function. Your IP has been logged.');
        }
        
        $validator = Validator::make($input, 
                array(
                    'role_id' => 'required|exists:user_roles,user_role_id',
                    'company_id' => 'required|exists:companies,company_id',
                    'email' => 'required|email|unique:admins,email'
                ) 
           );
        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('email','company_id'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }
        $admin = new Admin();
        $password = str_random(5);
        $admin->setFields($input, Hash::make($password));
        $admin->save();
        $data = array('email'=>$admin->email,
                      'fullname'=>$admin->first_name.' '.$admin->last_name,
                      'password'=>$password);

        // Change this back to Mail::queue
        Mail::send('webApp::emails.registration', $data, function($message) use ($data){
            $message->to($data['email'])->cc('leo@whiteknightsafety.com');
            $message->subject('White Knight Safety Dashboard Login!');
        }); 
        $admin->load('company','role');
        
        //Queue::push('WorkerQuantumHandler',array('companyId'=>$admin->company_id),'wkss');
        
        return self::buildAjaxResponse(TRUE, $admin);
        
    }
    
    public function editUserAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP has been logged.');
        }
        $input = array();
        foreach (Input::all() as $key=>$val){
            $key = str_replace('edit_', '', $key);
            $input[$key] = $val;
        }
        
        if ($loggedUser->isAdmin()){
            $input['role_id'] = UserRole::COMPANY_ADMIN;
        }else{
            $input['company_id'] = $loggedUser->company_id;
        }    
        $adminId = (int)$input['admin_id'];
        $validator = Validator::make($input, 
            array(
                'role_id' => 'required|exists:user_roles,user_role_id',
                'company_id' => 'required|exists:companies,company_id',
                'email' => "required|email|unique:admins,email,$adminId,admin_id"
            ) 
        );
        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('email','company_id'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }
        if (!array_key_exists($input['role_id'], UserRole::canEdit($loggedUser->role))){
                die('You are not authorized to perform this function. Your IP has been logged.');
        }
        
        $admin = Admin::find($input['admin_id']);
        $admin->setFields($input);
        $admin->save();
        return self::buildAjaxResponse(TRUE, $admin);
        
    }
    
    
    public function updateProfileAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP has been logged.');
        }
        $input = array();
        foreach (Input::all() as $key=>$val){
            $key = str_replace('profile_', '', $key);
            $input[$key] = $val;
        }
        
        $validatorRules = array(
                'first_name' => 'required',
                'last_name' => 'required',
                'phone_number' => 'required'
            );
        if(!$loggedUser->signature() && !Input::has('signature')){
            $validatorRules['signature']='required';
        }
        
        $validator = Validator::make($input,$validatorRules);
        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('first_name','last_name','signature','phone_number'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }
        
        
        $loggedUser->setFields($input);
        $loggedUser->save();
        
        
        
        if (Input::has('signature')){
            $binary = explode(',',$input['signature'])[1];
            $photoBinary = base64_decode($binary);
            $sm = new StorageManager();
            $sm->saveAdminSignature($loggedUser, $photoBinary);
        }
        
        
        return self::buildAjaxResponse(TRUE, $loggedUser);
    }
    
    public function deleteUserAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP has been logged.');
        }
        $adminId = str_replace('delete_', '', Input::get('delete_admin_id', null));
        $admin = Admin::find($adminId);
        if (!array_key_exists($admin->role_id, UserRole::canEdit($loggedUser->role))){
                die('You are not authorized to perform this function. Your IP has been logged.');
        }
        $admin->delete();
        
        //Queue::push('WorkerQuantumHandler',array('companyId'=>$admin->company_id),'wkss');
        
        return self::buildAjaxResponse(TRUE);
        
    }
   
}