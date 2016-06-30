<?php

Route::group(['middleware' => 'web'], function(){
    Route::get('podio/settings',['as' => 'podio-settings', 'uses' => 'App\Plugins\Podio\Controllers\SettingsController@index']);
    Route::post('post-podio-setting',['as' => 'post-podio-setting', 'uses' =>'App\Plugins\Podio\Controllers\SettingsController@postSetting']);
    Route::get('podio/app-authentication', ['as' => 'podio-settings2',
    	'uses' => 'App\Plugins\Podio\Controllers\SettingsController@orgIndex']);
    Route::get('podio/space', ['as' => 'podio-settings3', 'uses' => 'App\Plugins\Podio\Controllers\SettingsController@spaceIndex']);
    Route::post('podio/create-app', ['as' => 'podio-settings4', 'uses' => 'App\Plugins\Podio\Controllers\SettingsController@createApp']);
});
