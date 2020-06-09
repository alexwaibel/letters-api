<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\User;
use App\Org;
use App\OrgUser;
use App\Letter;
use App\Contact;

class UserController extends Controller
{
    public function __construct() {
      $this->middleware('auth:api');
      $this->middleware('throttle:60,1');
      $this->middleware('token-expire');
    }

    // get_users()
    // Allows admin to get a paginated list of users.
    // Page Limit: 20
    // Use the 'page' GET attribute to specify page
    public function get_users(Request $request) {
      $user = $request->user();

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $users = User::paginate(20);

      return api_response("OK", "", $users);
    }

    public function get_user(Request $request, $id) {
      $user = $request->user();

      $u = User::find($id);

      if (!$u) {
        return api_response("ERROR", "Invalid User ID", []);
      }

      if ($u->id != $user->id) {
        if ($user->type != "admin") {
          return api_response("ERROR", "Unauthorized", []);
        }
      }

      return api_response("OK", "", $u);
    }

    public function update_user(Request $request, $id) {
      $user = $request->user();

      $u = User::find($id);

      $data = json_decode($request->getContent(), true);

      $validator = Validator::make($data, [
        'first_name' => 'required|max:255',
        'last_name' => 'required|max:255',
        'phone' => 'required|min:10',
        'addr_line_1' => 'required|max:255',
        'addr_line_2' => 'min:0|max:50',
        'city' => 'required|max:255',
        'state' => 'required|max:255',
        'postal' => 'required|max:10',
        'country' => 'required|max:255',
        's3_img_url' => 'nullable'
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors();

        return api_response("ERROR", "Validation Error", $errors);
      }

      if (!$u) {
        return api_response("ERROR", "Invalid User ID", []);
      }

      if ($u->id != $user->id) {
        if ($user->type != "admin") {
          return api_response("ERROR", "Unauthorized", []);
        }
      }

      $u->first_name = $data['first_name'];
      $u->last_name = $data['last_name'];
      $u->phone = $data['phone'];
      $u->addr_line_1 = $data['addr_line_1'];
      $u->addr_line_2 = $data['addr_line_2'];
      $u->city = $data['city'];
      $u->state = $data['state'];
      $u->postal = $data['postal'];
      $u->country = $data['country'];

      if (isset($data['s3_img_url'])) {
        $u->profile_img_path = $data['s3_img_url'];
      }

      $u->save();

      return api_response("OK", "", $u);
    }

    public function get_contacts(Request $request) {
      $user = $request->user();

      $contacts = Contact::where("user_id", $user->id)->get();

      return api_response("OK", "", $contacts);
    }

    public function get_letters(Request $request) {
      $user = $request->user();

      $letters = Letter::where("user_id", $user->id)->get();

      return api_response("OK", "", $letters);
    }

    public function get_org(Request $request) {
      $user = $request->user();

      $ou = OrgUser::where("user_id", $user->id)->first();
      $o = Org::find($ou->org_id);

      return api_response("OK", $ou->role, $o);
    }
}
