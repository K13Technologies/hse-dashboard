<?php
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class AuthUser extends Eloquent implements UserInterface, RemindableInterface {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admins';
        protected $primaryKey = 'admin_id';
        public $timestamps = true;
        protected $softDelete= true;
        public static $unguarded = true;
        protected $hidden = array('password');
        
        public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	public function getAuthPassword()
	{
		return $this->password;
	}
        
        public function getReminderEmail()
        {
            return $this->email;
        }       
}