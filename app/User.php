<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

use App\PasswordReset;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'email','password','email_confirmation_token', 'email_confirmed', 'address','address_addition', 'postcode', 'city', 'country'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'email_confirmation_token','address','address_addition', 'postcode', 'city', 'country'
    ];

    /**
     * return a unique email confirmation token
     *
     * @return string Unique token
     */
    public function emailConfirmationToken() {
       
        $token = $this->generateSecureToken(15);
        // Check if that there isn't already a Password Reset with the same token
        while(User::where('email_confirmation_token', $token)->count() != 0) {
            // If there is already a Password Reset with the same token, generate a new one and check again
            $token = $this->generateSecureToken(15);
        }

       $this->email_confirmation_token = $token;
       
       return $token;
    }

    /**
     * Return a unique reference number
     *
     * @return string Unique token
     */
    public function resetToken() {
        $token = $this->generateSecureToken(8);

        // Check if that there isn't already a Password Reset with the same token
        while(PasswordReset::where('token', $token)->count() != 0) {
            // If there is already a Password Reset with the same token, generate a new one and check again
            $token = $this->generateSecureToken(8);
        }

        return $token;
    }

    /**
     * Generate a random reference number
     *
     * @return string random refno
     */
    private function generateSecureToken($length) {

        $token = "";
        $codeAlphabet  = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "0123456789";

        $max = strlen($codeAlphabet);

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max-1)];
        }

        return $token;
    }

    /**
     * Generates a crypto random number between a min and a max value
     *
     * @param   int $min Minimum value
     * @param   int $max Maximum value
     *
     * @return  A truly random number
     */
    private function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }
}
