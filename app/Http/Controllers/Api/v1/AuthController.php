<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\User;
use App\PasswordReset;
use App\Mail\ForgetPasswordRequestEmail;
use App\Mail\UserPasswordChanged;
use App\Mail\UserEmailConfirmation;

use Carbon\Carbon;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private $expire_date;
    private $now;
    private $sendEmail;

    public function __construct()
    {
        $this->expire_date = Carbon::now()->addDays(30);
        $this->now = Carbon::now();
        $this->sendEmail = false;
    }

/**
 * login function
 *
 * @param Request $request with user "email" and "password"
 * 
 * @return api_token on successful login
 */

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $res['success'] = false;
            $res['message'] = 'Your email or password incorrect!';
            return response($res,401);
        } else {
            if (Hash::check($password, $user->password)) {
               
                $api_token = $this->generateAccessTokenAndStoreIt($user->id);
                $res['success'] = true;
                $res['api_token'] = $api_token;
                return response($res);

            } else {
                $res['success'] = false;
                $res['message'] = 'You email or password incorrect!!!';
                return response($res,401);
            }
        }

    }

 /**
 * register function
 *
 * @param Request $request with "firstname", "lastname", "email", "password" and "password_confirmation" 
 * 
 * @return confirmation of registering 
 */

    public function register(Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
           
            'address' => 'nullable',
            'address_addition' => 'nullable',
            'postcode' => 'nullable',
            'city' => 'nullable',
            'country' => 'nullable',
        ]);

        $hasher = app()->make('hash');
        $firstname= $request->input('firstname');
        $lastname= $request->input('lastname');
        $email = $request->input('email');
        $password = $hasher->make($request->input('password'));

        $address = $request->address != null ?$request->input('address'):'';
        $address_addition = $request->address_addition != null ?$request->input('address_addition'):'';
        $postcode = $request->postcode != null ?$request->input('postcode'):'';
        $city = $request->city != null ?$request->input('city'):'';
        $country = $request->country != null ?$request->input('country'):'';

        // $address = isset($request->address)?$request->input('address'):'';
        // $address_addition = isset($request->address_addition)?$request->input('address_addition'):'';
        // $postcode = isset($request->postcode)?$request->input('postcode'):'';
        // $city = isset($request->city)?$request->input('city'):'';
        // $country = isset($request->country)?$request->input('country'):'';

        // $address = $request->input('address');
        // $address_addition = $request->input('address_addition');
        // $postcode = $request->input('postcode');
        // $city = $request->input('city');
        // $country = $request->input('country');
        
        
        $user = User::create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password,

            'address' => $address,
            'address_addition' => $address_addition,
            'postcode' => $postcode,
            'city' => $city,
            'country' => $country,
        ]);

        // this will generate and return a unique email confirmation token and also store it in user "email_confirmation_token" field
        $confirmation_code = $user->emailConfirmationToken();

        $user->save();


        $res['success'] = true;
        $res['message'] = 'Success register!';
        $res['data'] = $user;

        // send Confirmation email
        $data = array();
        $data =['first_name'=> $user->firstname,
                'email'=> $user->email,
                'email_confirmation_token'=> $confirmation_code,
            ];

        if ($this->sendEmail){
            Mail::to($user->email)
                ->send(new UserEmailConfirmation($data));
        }

        // generate a api token and log the user in
        $api_token = $this->generateAccessTokenAndStoreIt($user->id);
        $res['success'] = true;
        $res['api_token'] = $api_token;
        return response($res);
    }

    public function logout(Request $request)
    {
        if ($request->header('Authorization')) {
            $api_token = explode(' ', $request->header('Authorization'));

            DB::table('api_access_tokens')
                ->where('token', $api_token[1])
                ->update(['revoked' => true]);

            $res['success'] = true;
            $res['message'] = 'Logout successful!';
            return response($res);
        }
    }

    /**
     * forgetPassword function
     *
     * @param Request $request with user "email"
     * 
     * @return confirmation of sending the reset password email 
     */

    public function forgetPassword(Request $request)
    {
            $user = User::where('email',$request->email)->first();

            if ($user) {

                $reset_token = $user->resetToken();

                PasswordReset::create([
                  'email' => $user->email,
                  'token' => $reset_token,
                  'created_at' => $this->now,
                ]);
  
                 // send email
                 $data = array();
                 $data =['first_name'=> $user->firstname,
                         'last_name'=> $user->lastname,
                         'email'=> $user->email,
                         'reset_token'=> $reset_token,
                     ];
                if ($this->sendEmail){
                    Mail::to($user->email)
                        ->send(new ForgetPasswordRequestEmail($data));
                }

            }

            return response('An email has been sent to '.$request->email.' with further instructions to reset the password',200);
    }

 /**
 * resetPassword function
 *
 * @param Request $request with password reset "token" and the new "password"
 * 
 * @return confirmation of Password change
 */

    public function resetPassword(Request $request)
    {
            $this->validate($request, [
                'token' => 'required|string|max:8',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $passwordReset =  PasswordReset::where('token',$request->token)->where('used',false)->first();

            if (empty($passwordReset)) {
                return response('Unauthorized',401);
            }

            $user = User::where('email',$passwordReset->email)->first();

            $expireDate = Carbon::parse($passwordReset->created_at)->addHours(3);
            
            $isNotExpired = $expireDate > $this->now ? true : false;

            if ($user && $isNotExpired) {
                $hasher = app()->make('hash');
                $hashed_password = $hasher->make($request->password);

                $user->password =  $hashed_password;
                $user->save();

                $user->fill([
                    'password' => $hashed_password,
                ])->save();

                $passwordReset->used = true;
                $passwordReset->save();

                // send email
                $data = array();
                $data =['first_name'=> $user->firstname,
                        'last_name'=> $user->lastname,
                        'email'=> $user->email,
                    ];

                if ($this->sendEmail){
                    Mail::to($user->email)
                        ->send(new UserPasswordChanged($data));
                }

                //  password has been successfully changed, now we will generate a api token for user (log him in)
                $api_token = $this->generateAccessTokenAndStoreIt($user->id);
                $res['success'] = true;
                $res['api_token'] = $api_token;
                return response($res);
            } else {
                return response('Unauthorized',401);
            }
    }

    /**
     * changePassword function
     *
     * @param Request $request with Authenteication Bearer attached to it and the user "current_password" and "new_password"
     * 
     * @return confirmation of Password change
     */

    public function changePassword(Request $request)
    {
        if ($request->user()) {
            $this->validate($request, [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
            ]);

            $email = $request->user()->email;
            $current_password = $request->current_password;
            $new_password = $request->new_password;
    
            $user = User::where('email', $email)->first();
    
            if (Hash::check($current_password, $user->password)) {
                $hasher = app()->make('hash');
                $hashed_password = $hasher->make($request->new_password);  
                $user->fill([
                    'password' => $hashed_password,
                ])->save();

                // send email
                $data = array();
                $data =['first_name'=> $user->firstname,
                        'last_name'=> $user->lastname,
                        'email'=> $user->email,
                    ];

                if ($this->sendEmail){
                    Mail::to($user->email)
                        ->send(new UserPasswordChanged($data));
                }
                $res['success'] = true;
                return response($res);

            } else {
                $res['success'] = false;
                $res['message'] = 'Das aktuelle Passwort stimmt nicht überein.';
                return response($res, 500);
            }

        } else {
            return response('Unauthorized',401);
        }
    }

        /**
     * changeEmail the email address of the user profile
     *
     * @param  Request      $request HttpRequest object
     * @return Response     HttpResponse object
     */
    public function changeEmail(Request $request)
    {
        $new_email = $request->input('new_email');
        $user = User::where('email', $new_email)->first();
        if ($user) {
            $res['success'] = false;
            $res['message'] = 'This Email is already in use!';
            return response($res,403);
        } else {
            $confirmation_code = $request->user()->emailConfirmationToken();

            $request->user()->fill([
                'email' => $new_email,
                'email_confirmed' => false,
                'email_confirmation_token' => $confirmation_code,
            ])->save();

            // send email
            $data = array();
            $data =['first_name'=> $request->user()->firstname,
                    'last_name'=> $request->user()->lastname,
                    'email'=> $request->user()->email,
                    'email_confirmation_token'=> $confirmation_code,
                ];

            if ($this->sendEmail){
                Mail::to($user->email)
                    ->send(new UserEmailConfirmation($data));
            }

            
            $res['success'] = true;
            $res['email'] = $request->user()->email;
            $res['message'] = 'Successfully updated the Email.';
            return response($res,200);
        }
    }
        
    /**
     * Update name and address of user
     *
     * @param  Request      $request HttpRequest object
     * @return Response     HttpResponse object
     */
    public function changeNameAndAddress(Request $request)
    {
        $request->user()->fill([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'address' => $request->address,
            'address_addition' => $request->address_addition,
            'postcode' => $request->postcode,
            'city' => $request->city,
            'country' => $request->country,
        ])->save();

        return response("Successfully updated Name and Address", 200);
    }


    /**
     * emailConfirmation function
     *
     * @param Request $request with "email_confirmation_token"
     * 
     * @return confirmation of Password change
     */

    public function emailConfirmation(Request $request)
    {
        $this->validate($request, [
            'email_confirmation_token' => 'required|string',
        ]);

        $email_confirmation_token = $request->email_confirmation_token;

        $user = User::where('email_confirmation_token', $email_confirmation_token)->first();
        
        if (empty($user)) {
            return response('Unauthorized',401);
        }

        $user->fill([
            'email_confirmed' => true,
            'email_confirmation_token' => null,
        ])->save();

        // check if user is loggen in just response with success else generate a access token for him and send it back
        $isUserLoggedIn = $request->user()?true:false;
        
        if ($isUserLoggedIn) {
            $res['success'] = true;
            return response($res);
        } else {
            $api_token = $this->generateAccessTokenAndStoreIt($user->id);
            $res['success'] = true;
            $res['api_token'] = $api_token;
            return response($res);
        }
    }

    public function resendEmailConfirmationToken(Request $request)
    {
        $user = User::find($request->user()->id);
        // this will generate and return a unique email confirmation token and also store it in user "email_confirmation_token" field
        $confirmation_code = $user->emailConfirmationToken();
        $user->save();
            
        // send email
        $data = array();
        $data =['first_name'=> $user->firstname,
                'last_name'=> $user->lastname,
                'email'=> $user->email,
                'email_confirmation_token'=> $confirmation_code,
            ];

        if ($this->sendEmail){
            Mail::to($user->email)
                ->send(new UserEmailConfirmation($data));
        }
        

        $res['success'] = true;
        $res['message'] = 'Successfully resent the email confirmation token!';
        $res['data'] = $user;

    }

    /**
    * Return the requesting user info
    *
    * @return \Illuminate\Http\Response
    */
    public function userInfo(Request $request)
    {
        if ($request->user()) {
            return $request->user();
        } else {
            return response('Unauthorized',401);
        }
    }

    private function generateAccessTokenAndStoreIt($user_id)
    {
        $api_token = bin2hex(openssl_random_pseudo_bytes(64));

        DB::table('api_access_tokens')->insert(
            ['token' => $api_token,
            'user_id' =>  $user_id,
            'created_at' => $this->now,
            'expires_at' => $this->expire_date,
        ]
        );

        return $api_token;
    }

}
