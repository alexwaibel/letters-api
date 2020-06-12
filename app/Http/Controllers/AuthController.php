<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Validator;
use Hash;
use Str;

use Carbon\Carbon;

use App\User;

class AuthController extends Controller
{
    public function __construct() {
      $this->middleware('throttle:60,1');
    }

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
