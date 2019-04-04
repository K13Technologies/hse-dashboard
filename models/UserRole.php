<?php

class UserRole extends Eloquent {


        const ADMIN = 10;
        const COMPANY_ADMIN = 3;
        const HEATH_ADMIN = 2;
        const ACCOUNT_ADMIN = 1;
    
	protected $table = 'user_roles';
        protected $primaryKey = 'user_role_id';
        public $timestamps = false;
        
        public static function getRights(){
            $refl = new ReflectionClass(__CLASS__);
            $constants =  $refl->getConstants();
            $timestamps = array('CREATED_AT','UPDATED_AT','DELETED_AT');
            return array_except($constants, $timestamps);
        }
        
        public static function canAdd(UserRole $role){
            $userLevel = Config::get("webApp::userRoles");
            if (($role->user_role_id) == self::ADMIN ){
                return array(self::COMPANY_ADMIN=>$userLevel[$role->user_role_id]['name']);
            }else{
                $rights = self::getRights();
                $res = array();
                foreach ($rights as $r){
                    if ($r <= $role->user_role_id){
                        $res[$r] = $userLevel[$r]['name'];
                    }
                }
                return $res;
            }
        }
        
        public static function canEdit(UserRole $role){
            $userLevel = Config::get("webApp::userRoles");
            if (($role->user_role_id) == self::ADMIN ){
                return array(self::COMPANY_ADMIN=>$userLevel[$role->user_role_id]['name']);
            }else{
                $rights = self::getRights();
                $res = array();
                foreach ($rights as $r){
                    if ($r <= $role->user_role_id){
                        $res[$r] = $userLevel[$r]['name'];
                    }
                }
                return $res;
            }
        }
        
        public static function canSee(UserRole $role){
//            if (($role->user_role_id) == self::ADMIN ){
//                return array(self::COMPANY_ADMIN);
//            }else{
                $rights = self::getRights();
                $res = array();
                foreach ($rights as $r){
                  
                    if ($r <= $role->user_role_id){
                        $res[] = $r;
                    }
                }
                return $res;
//            }
        }
}