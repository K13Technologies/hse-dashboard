<?php namespace App\Modules\webApp;
 
class ServiceProvider extends \App\Modules\ServiceProvider {
 
    public function register()
    {
        parent::register('webApp');
    }
 
    public function boot()
    {
        parent::boot('webApp');
    }
 
}