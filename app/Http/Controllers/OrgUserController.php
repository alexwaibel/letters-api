<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Org;
use App\OrgUser;

use Validator;

class OrgUserController extends Controller
{
    public function __construct() {
      $this->middleware('auth:api');
      $this->middleware('throttle:60,1');
    }

    // get_org_users()
    // Allows admin to get a paginated list of org_users.
    // Page Limit: 20
    // Use the 'page' GET attribute to specify page
    public function get_org_users(Request $request) {
      $user = $request->user();

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $org_users = OrgUser::paginate(20);

      return api_response("OK", "", $org_users);
    }

    public function get_org_user(Request $request, $id) {
      $user = $request->user();

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $ou = OrgUser::find($id);

      if (!$ou) {
        return api_response("ERROR", "Invalid Org User ID", []);
      }

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      return api_response("OK", "", $ou);
    }

    public function create_org_user(Request $request) {
      $user = $request->user();

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $data = json_decode($request->getContent(), true);

      $validator = Validator::make($data, [
        'org_id' => 'required',
        'user_id' => 'required',
        'role' => 'required',
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors();

        return api_response("ERROR", "Validation Error", $errors);
      }

      $ou = new OrgUser;

      $ou->org_id = $data['org_id'];
      $ou->user_id = $data['user_id'];
      $ou->role = $data["role"];

      $ou->save();

      return api_response("OK", "", $ou);
    }

    public function update_org_user(Request $request, $id) {
      $user = $request->user();

      $data = json_decode($request->getContent(), true);

      $ou = OrgUser::find($id);

      if (!$ou) {
        return api_response("ERROR", "Invalid Org User ID", []);
      }

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $validator = Validator::make($data, [
        'org_id' => 'required',
        'user_id' => 'required',
        'role' => 'required',
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors();

        return api_response("ERROR", "Validation Error", $errors);
      }

      $ou->org_id = $data['org_id'];
      $ou->user_id = $data['user_id'];
      $ou->role = $data["role"];

      $ou->save();

      return api_response("OK", "", $ou);
    }
}
