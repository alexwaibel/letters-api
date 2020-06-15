<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Validator;
use Hash;
use Str;

use Carbon\Carbon;

use App\User;

/**
 * @group Authentication
 * 
 * APIS for handling authenticating users
 */
class AuthController extends Controller
{
    public function __construct() {
      $this->middleware('throttle:60,1');
    }

    /**
     * Authenticate a user by token
     *
     * @bodyParam token string required The remember token from the original login request. Example: TB3rodjv4eJsm1Rp2xRHf9JC6hk6dfnh5N3u5mOWJNUIY10BSmdZoDPFnMIUITDm4nPmktzmSbwdSZJQfk1w4QENEoCC4QTM1MvX
     *
     * @response {
     *   "date": 1591997000,
     *   "status": "OK",
     *   "message": "",
     *   "data": "ruUJKEUzeNrGLtrhYW7vGbikyVpns4rVsQ7O616qRxthO0Mm8nI9TaTYkiR9vE2QPnVpCuCPnWBuPkgL"
     * }
     */
    public function login_with_token(Request $request) {
      $data = json_decode($request->getContent(), true);

      $validator = Validator::make($data, [
        'token' => 'required'
      ]);

      if ($validator->passes()) {
        // Validate User
        $u = User::where('remember_token', $data['token'])->first();

        if (!$u) {
          return api_response(400, "ERROR", "Invalid Token", []);
        }

        $token = Str::random(80);
        $u->api_token = hash('sha256', $token);
        $u->api_token_expires = Carbon::now()->addHours(2);

        if (!$u->api_token) {
          $u->api_token = hash('sha256', $token);
          $u->api_token_expires = Carbon::now()->addHours(2);
          $u->save();
        }

        if ($u->api_token_expires < Carbon::now()) {
          $u->api_token = hash('sha256', $token);
          $u->api_token_expires = Carbon::now()->addHours(2);
          $u->save();
        }

        $u->save();

        return api_response(200, "OK", "", $token);
      }

      return api_response(400, "ERROR", "Missing Fields", []);
    }

     /**
     * Authenticate a user by email and password
     * 
     * For use if the user's rememver token is no longer valid.
     * 
     * @bodyParam email string required The email of the account to be logged in. Example: tim01@smith.com
     * @bodyParam password string required The password of the account to be logged in. Example: password1234
     * 
     * @response {
     *   "date": 1591997000,
     *   "status": "OK",
     *   "message": "",
     *   "data": {
     *     "token": "147WecYkPYL5LppPIg5m5LY5d9NBoBUyn6Z65lPnuwfiahY4B86zCmcKFx6S0sJoSz3TCSrDNOCmjPZn",
     *     "remember": "TB3rodjv4eJsm1Rp2xRHf9JC6hk6dfnh5N3u5mOWJNUIY10BSmdZoDPFnMIUITDm4nPmktzmSbwdSZJQfk1w4QENEoCC4QTM1MvX"
     *    }
     * }
     */
    public function login(Request $request) {
      $data = json_decode($request->getContent(), true);

      $validator = Validator::make($data, [
        'email' => 'required|exists:users',
        'password' => 'required'
      ]);

      if ($validator->passes()) {
        // Validate User
        $u = User::where('email', $data['email'])->first();

        if (!$u) {
          return api_response(400, "ERROR", "Invalid Email", []);
        }

        if (!Hash::check($data['password'], $u->password)) {
          return api_response(400, "ERROR", "Invalid Password", []);
        }

        $token = Str::random(80);
        $u->api_token = hash('sha256', $token);
        $remember_token = $u->remember_token;
        $u->api_token_expires = Carbon::now()->addHours(2);

        if (!$u->api_token) {
          $u->api_token = hash('sha256', $token);
          $u->api_token_expires = Carbon::now()->addHours(2);
          $u->save();
        }

        if ($u->api_token_expires < Carbon::now()) {
          $u->api_token = hash('sha256', $token);
          $u->api_token_expires = Carbon::now()->addHours(2);
          $u->save();
        }

        if (!$remember_token) {
          $remember_token = Str::random(100);
          $u->remember_token = $remember_token;
          $u->save();
        }

        $u->save();

        $res = [
          'token' => $token,
          'remember' => $remember_token
        ];

        return api_response(200, "OK", "", $res);
      }

      return api_response(400, "ERROR", "Missing Fields", []);
    }
 
    /**
     * Register a new user
     * 
     * @bodyParam email string required The email of the account to be created. Example: tim01@smith.com
     * @bodyParam password string required The password of the account to be created. Example: password1234
     * @bodyParam password_confirmation string required Repeat of the password of the account to be created. Example: password1234
     * @bodyParam first_name string required First name of user. Example: John
     * @bodyParam last_name string required Last name of user. Example: Smith
     * @bodyParam address_line_1 string required First line of address of user. Example: 123 Test St.
     * @bodyParam address_line_2 string Second line of address of user. Example: APT 1
     * @bodyParam city string required City of user. Example: Atlanta
     * @bodyParam state string required Two digit state abbreviation of US state of user. Example: GA
     * @bodyParam country string required Country of user. Example: US
     * @bodyParam referer string required Entity who referred the user to Ameelio Letters. Example: Facebook
     * @bodyParam postal string required Zip code of user. Example: 31206
     * @bodyParam phone string required Phone number of user. Example: 111-222-3333
     * @bodyParam s3_img_url string AWS S3 URL of the profile picture of user.
     * 
     * @response {
     *   "date": 1591997000,
     *   "status": "OK",
     *   "message": "",
     *   "data": {
     *     "email": "tim01@smith.com",
     *     "first_name": "Tim01",
     *     "last_name": "Smith",
     *     "phone": "111-222-3333",
     *     "addr_line_1": "123 Test St.",
     *     "profile_img_path": "https://ameelio-letters-staging-images.s3.amazonaws.com/images/avatars/12319898123/.png",
     *     "city": "Atlanta",
     *     "state": "GA",
     *     "postal": "31206",
     *     "country": "US",
     *     "referer": "Other",
     *     "api_token_expires": "2020-06-12T23:23:19.305885Z",
     *     "credit": 3,
     *     "updated_at": "2020-06-12T21:23:19.000000Z",
     *     "created_at": "2020-06-12T21:23:19.000000Z",
     *     "id": 15
     *   }
     * }
     */
    public function register(Request $request) {
      $data = json_decode($request->getContent(), true);

      $validator = Validator::make($data, [
        'email' => 'required|unique:users',
        'password' => 'required|confirmed',
        'first_name' => 'required|min:1',
        'last_name' => 'required|min:1',
        'phone' => 'required',
        'address_line_1' => 'required',
        'address_line_2' => 'string|nullable',
        'city' => 'required',
        'state' => 'required',
        'postal' => 'required',
        'referer' => 'required',
        'country' => 'required'
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors();

        return api_response(400, "ERROR", "Validation Error", $errors);
      }

      $new_user = new User;

      $new_user->email = $data['email'];
      $new_user->password = Hash::make($data['password']);
      $new_user->first_name = $data['first_name'];
      $new_user->last_name = $data['last_name'];
      $new_user->phone = $data['phone'];
      $new_user->addr_line_1 = $data['address_line_1'];

      if (isset($data['address_line_2'])) {
        $new_user->addr_line_2 = $data['address_line_2'];
      }

      if (isset($data['s3_img_url'])) {
        $new_user->profile_img_path = $data['s3_img_url'];
      } else {
        $new_user->profile_img_path = Storage::disk('s3')->url('images/avatars/avatar.svg');
      }

      $new_user->city = $data['city'];
      $new_user->state = $data['state'];
      $new_user->postal = $data['postal'];
      $new_user->country = $data['country'];
      $new_user->referer = $data['referer'];

      $new_user->api_token = Hash::make(Str::random(80));
      $new_user->api_token_expires = Carbon::now()->addHours(2);

      $new_user->credit = 3.0;

      $new_user->save();

      return api_response(200, "OK", "", $new_user);
    }
}
