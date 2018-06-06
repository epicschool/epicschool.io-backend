<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
// use Auth;
use App\Work;
use App\Question;
use App\Phase;
use App\Lesson;
use App\User;
use Carbon\Carbon;


class UserController extends Controller
{
  public function __construct()
  {
      // $this->middleware('auth');
  }

  /**
   * Return the requesting user info
   *
   * @return \Illuminate\Http\Response
   */
  public function userInfo(Request $request)
  {
      if ($request->user()) {
          $user['firstname'] = $request->user()->firstname;
          $user['lastname'] = $request->user()->lastname;
          $user['email'] = $request->user()->email;
          return response()->json($user);
      } else {
          return response('Unauthorized',401);
      }
  }
}
