<?php
use Illuminate\Auth\UserInterface;
use Laravel\Cashier\BillableTrait;
use Laravel\Cashier\BillableInterface;

class Admin extends Eloquent implements UserInterface
{
        
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
    
    private static $properties = array('first_name','last_name','email','phone_number',
                                    'role_id','company_id',);
        
    public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	public function getAuthPassword()
	{
		return $this->password;
	}
        
    public function role()
    {
            return $this->belongsTo('UserRole','role_id');
    }
    
    public function company()
    {
            return $this->belongsTo('Company','company_id');
    }
    
    public function resetPassword($password){
        $this->password = Hash::make($password);
        $this->reset_token = null;
    }
    
    
    public static function getByEmail($email){
        $result =  Admin::where('email', '=', $email)->get();
        if ($result->isEmpty()){
            return NULL;
        }
        return $result->first();
    }
    public static function getByResetToken($resetToken){
        $result =  Admin::where('reset_token', '=', $resetToken)->get();
        if ($result->isEmpty()){
            return NULL;
        }
        return $result->first();
    }
    
    public static function getAdminListForAdmin(Admin $admin){
        $visible = UserRole::canSee($admin->role);
        
        $query =  Admin::join('companies', 'admins.company_id', '=', 'companies.company_id')
                        ->orderBy('companies.company_name')
                        ->whereIn('role_id', $visible);
        if ($admin->role_id != UserRole::ADMIN){
            $query = $query->where('admins.company_id', '=', $admin->company_id);
        }
        return $query->get()->all();
    }
    
    public static function getAdminCountForCompany(Company $company){
        return Admin::where('admins.company_id', '=', $company->company_id)->get()->count();
    }
    
    public static function generateResetToken(){
       do { 
           $resetToken =  str_random(128);
           $admin = self::getByResetToken($resetToken);
       } while ($admin instanceof self);

       return $resetToken;
    }
    
    public function isAdmin(){
        return ($this->role_id == UserRole::ADMIN);
    }
    
    public function setFields(array $input, $password = null, $active = 1 ){
        
        foreach ($input as $key=>$val){
            if (in_array($key, self::$properties)){
                $this->$key = $val;
            }
        }
        if ($password){
            $this->password = $password;
        }
        
        $this->is_active = $active;
        return $this;
    }
    
    public function getRememberToken()
    {
        return $this->reset_token;
    }

    public function setRememberToken($value)
    {
        $this->reset_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'reset_token';
    }
    
    public function shouldDisplayTutorial(){
        if ($this->isAdmin()){
            return $this->show_tutorial;
        }else{
            return ($this->show_tutorial &&  $this->company->stripeIsActive())?1:0;
        }
    }
    public function shouldDisplayProfile(){
        $res = empty(Auth::user()->first_name) || empty(Auth::user()->last_name) || empty(Auth::user()->phone_number) || !Auth::user()->signature();
        return $res?1:0;
    }
    
    
    public function signature() {
        $sm = new StorageManager;
        return $sm->getAdminSignature($this->admin_id);
    }
}