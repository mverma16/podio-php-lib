<?php

namespace App\Plugins\Podio;

class ServiceProvider extends \App\Plugins\ServiceProvider
{
    public function register()
    {
        parent::register('Podio');
    }

    public function boot()
    {
        parent::boot('Podio');
    }
}
