<?php

class AuthController extends Controller {
    
    public function __construct() {
//        $this->responses = Config::get('api::responseConstants');
//        $this->storageManager = new StorageManager();
    }
    
    public function loginAction(){
        $email = Input::get('email');
        $password = Input::get('password');
        $tzOffset = Input::get('tz_offset');
        if (Auth::attempt(array('email' => $email, 
                                'password' => $password, 
                                'is_active' => 1), true)){
            $admin = Auth::user();
            $admin->tz_offset = $tzOffset;
            $admin->save();
            return Redirect::intended('/');
        }
        return Redirect::to('login')->with(array('error'=>'Invalid email or password '));
    }
    
    public function forgotPasswordAction(){
        $email = Input::get('email');
        $admin = Admin::getByEmail($email);
        if ( !$admin instanceof Admin ){
             return Redirect::to('forgot-password')->with(array('error'=>"Email doesn't exist"));
        }
        $admin->reset_token = Admin::generateResetToken();
        $admin->save();

        $recoveryLink = URL::to("reset-password/{$admin->reset_token}");

        $data = array('email'=>$admin->email,
                      'link'=>$recoveryLink);
        // Change this back to Mail::queue
        Mail::send('webApp::emails.password-recovery', $data, function($message) use ($data){
            $message->to($data['email'])->subject('White Knight Safety - Password Recovery Instructions ');
        });

        $message = "Instructions for password recovery have been sent to your email";

        return Redirect::to('forgot-password')->with(array('message' => $message ));
    }
    
    
    public function resetPasswordAction($resetToken){
        $admin = Admin::getByResetToken($resetToken);
        
        $password = Input::get('password');
        $confirm = Input::get('confirm');

        if ($password != $confirm){
             return Redirect::to("reset-password/{$resetToken}")->with(array('error'=>"Passwords do not match"));
        }
        
        $admin->resetPassword($password);
        $admin->save();
        
        Auth::loginUsingId($admin->admin_id);

        return Redirect::to('company-management');
    }
}
