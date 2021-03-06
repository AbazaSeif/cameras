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
use GrahamCampbell\GitHub\Facades\GitHub;
use Github\Api\Deployment;
Auth::routes();
Route::group(['middleware' => 'auth'], function () {
    Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);
    Route::get('/cameras', ['as' => 'cameras', 'uses' => 'CameraController@index']);
    Route::get('/cameras/create', ['as' => 'cameras.create', 'uses' => 'CameraController@create']);
    Route::post('/cameras', ['as' => 'cameras.store', 'uses' => 'CameraController@store']);
    Route::get('/cameras/{camera}', ['as' => 'cameras.edit', 'uses' => 'CameraController@edit']);
    Route::put('/cameras/{camera}', ['as' => 'cameras.update', 'uses' => 'CameraController@update']);
    Route::delete('/cameras/{camera}', ['as' => 'cameras.delete', 'uses' => 'CameraController@delete']);
    Route::get('/video/proxy/{camera}', ['as' => 'cameras.proxy', 'uses' => 'CameraController@proxy']);
});

Route::get('create-deploy', function() {
    $data = GitHub::api('deployment')->create('esalazarv', 'cameras', array('ref' => 'master'));
    dd($data);
});

Route::get('git-test', function() {
    $manager = new App\Services\DeployManager();
    $manager->checkDeploys('esalazarv', 'cameras');
});