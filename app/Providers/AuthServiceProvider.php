<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use DateTime;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->header('Authorization')) {
                $request_api_token = explode(' ', $request->header('Authorization'))[1];
          
                $api_token = DB::table('api_access_tokens')
                     ->select('token','user_id')
                     ->where('token', $request_api_token)
                     ->whereDate('expires_at','>',new DateTime())
                     ->where('revoked',false)
                     ->first();

                if (!empty($api_token)) {    
                    $user = User::where('id',$api_token->user_id)->first();
                    if (!empty($user)) {
                        return $user;
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }

            }
        });
    }
}
