<?php namespace App\Modules\Api;
 
class ServiceProvider extends \App\Modules\ServiceProvider {
 
    public function register()
    {
        parent::register('api');
    }
 
    public function boot()
    {
        parent::boot('api');
    }
 
}