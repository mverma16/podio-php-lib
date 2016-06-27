<?php

Route::group(['middleware' => 'web'], function () {
    Route::get('podio/settings', ['as' => 'podio-settings', 'uses' => 'App\Plugins\Podio\Controllers\SettingsController@index']);

    Route::patch('reseller/postsettings', 'App\Plugins\Podio\Controllers\SettingsController@update');
});
