<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserEmailConfirmation;


/**
 * Is responsible for all updates and changes on the user profile
 */
class ProfileController extends Controller
{

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Returns the profile of the current logged in user
     *
     * @return User object
     */
    public function loadUserProfile()
    {
        return $request->user();
    }

    /**
     * Update the password of the user
     *
     * @param  Request      $request HttpRequest object
     * @return Response     HttpResponse object
     */
    public function updatePassword(Request $request)
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

                Mail::to($user->email)
                    ->send(new UserPasswordChanged($data));

                //  password has been successfully changed, now we will generate a api token for user (log him in)
                $api_token = $this->generateAccessTokenAndStoreIt($user->id);
                $res['success'] = true;
                $res['api_token'] = $api_token;
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
     * Update the email address of the user profile
     *
     * @param  Request      $request HttpRequest object
     * @return Response     HttpResponse object
     */
    public function updateEmail(Request $request)
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

            Mail::to($request->user()->email)
                ->send(new UserEmailConfirmation($data));

            
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
    public function updateNameAndAddress(Request $request)
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
}
