<?php

class UserRoleSeeder extends DatabaseSeeder {
    
        public function run()
	{
            if(!UserRole::all()->count()){
                $roles = Config::get('webApp::userRoles');
                foreach (array_keys($roles) as $role){
                    UserRole::create(array('user_role_id'=>$role,
                                           'role_name'=>$roles[$role]['name']));
                }
            }
	}
}