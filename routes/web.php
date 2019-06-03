<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth']], function(){

    Route::get('/', 'SitesController@index');

    Route::get('sites/create/step-1','SitesController@createStep1')->name('sites.create.step1');
    Route::post('sites/step-1','SitesController@storeStep1')->name('sites.store.step1');

    Route::post('sites/site-installed/{siteId}','SitesController@checkSiteInstalled')->name('sites.check-installed');
    Route::post('sites/check-ids-status','SitesController@checkIdsStatus')->name('sites.check-ids-status');

    Route::get('sites/create/step-2/{siteId}','SitesController@createStep2')->name('sites.create.step2');
    Route::post('sites/step-2/{siteId}','SitesController@storeStep2')->name('sites.store.step2');

    Route::get('sites','SitesController@index')->name('sites');

    /** Servers */
    Route::get('servers','ServersController@index')->name('servers');
    Route::get('servers/create/step-1','ServersController@createStep1')->name('servers.create.step1');
    Route::post('servers/step-1','ServersController@storeStep1')->name('servers.store.step1');
    Route::post('servers/check-status','ServersController@checkStatus')->name('servers.check-status');

    /** Versions */
    Route::get('versions','VersionsController@index')->name('versions');
    Route::get('versions/create','VersionsController@create')->name('versions.create');
    Route::post('versions','VersionsController@store')->name('versions.store');

    /** Update Site Version */
    Route::get('sites/update-version/{siteId}','UpdateSiteVersionController@edit')->name('sites.version.edit');
    Route::post('sites/update-version/{siteId}','UpdateSiteVersionController@update')->name('sites.version.update');
    Route::post('sites/get-site-version-services','UpdateSiteVersionController@getServices')->name('sites.version.services');
});


Route::group(['middleware' => ['guest']],function(){

    Route::get('/login','Auth\LoginController@showLoginForm')->name('login');
    Route::post('/login','Auth\LoginController@login');

});

Route::post('logout','Auth\LoginController@logout')->name('logout');
