<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function ($app) use($router) {
    $router->group(['namespace' => 'Api'], function() use ($router)
    {
        // Api v1
        $router->group(['prefix' => 'v1'], function ($app) use($router) {
            $router->group(['namespace' => 'v1'], function() use ($router)
            {
                $router->get('/', function () use ($router) {
                    return "api/v1";
                });
                
                $router->group(['prefix' => 'auth'], function () use($router){
                    $router->post('login','AuthController@login');
                    $router->post('register','AuthController@register');
                    $router->post('logout','AuthController@logout');
                    $router->post('forgetPassword','AuthController@forgetPassword');
                    $router->post('resetPassword','AuthController@resetPassword');
                    $router->post('emailConfirmation','AuthController@emailConfirmation');
                    
                    // $router->post('changePassword','AuthController@changePassword');
                    // $router->post('changeEmail','AuthController@changeEmail');
                    // $router->post('changeNameAndAddress','AuthController@changeNameAndAddress');

                    $router->post('changeAccountInfo','AuthController@changeAccountInfo');

                    $router->get('userinfo','AuthController@userinfo');
                    
                });

                // Profile
                $router->group(['prefix' => 'files'], function () use($router) {
                    $router->post('deleteFile','FileController@deleteFile');
                    $router->post('uploadPicture','FileController@uploadPicture');
                 });

            });
        });
    });
});