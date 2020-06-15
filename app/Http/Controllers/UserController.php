<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\User;
use App\Org;
use App\OrgUser;
use App\Letter;
use App\Contact;

/**
 * @authenticated
 * @group Users
 * 
 * APIs for managing users
 */
class UserController extends Controller
{
    public function __construct() {
      $this->middleware('auth:api');
      $this->middleware('throttle:60,1');
      $this->middleware('token-expire');
    }

    /**
     * Retrieve paginated list of users
     * 
     * Must be authenticated as an admin.
     * 
     * @urlParam page The page of users to fetch
     */
    public function get_users(Request $request) {
      $user = $request->user();

      if ($user->type != "admin") {
        return api_response(401, "ERROR", "Unauthorized", []);
      }

      $users = User::paginate(20);

      return api_response(200, "OK", "", $users);
    }

    /**
     * Retrieve user profile details
     * 
     * Must be authenticated as an admin to get details of users other than the currently authenticated user.
     */
    public function get_user(Request $request, $id) {
      $user = $request->user();

      $u = User::find($id);

      if (!$u) {
        return api_response(404, "ERROR", "Invalid User ID", []);
      }

      if ($u->id != $user->id) {
        if ($user->type != "admin") {
          return api_response(401, "ERROR", "Unauthorized", []);
        }
      }

      return api_response(200, "OK", "", $u);
    }

    /**
     * Update profile details of a user
     * 
     * Must be authenticated as an admin to update details of other user's.
     * 
     * @bodyParam first_name string required First name of user. Example: John
     * @bodyParam last_name string required Last name of user. Example: Smith
     * @bodyParam address_line_1 string required First line of address of user. Example: 123 Test St.
     * @bodyParam address_line_2 string Second line of address of user. Example: APT 1
     * @bodyParam city string required City of user. Example: Atlanta
     * @bodyParam state string required Two digit state abbreviation of US state of user. Example: GA
     * @bodyParam country string required Country of user. Example: US
     * @bodyParam postal string required Zip code of user. Example: 31206
     * @bodyParam phone string required Phone number of user. Example: 111-222-3333
     */
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

        return api_response(400, "ERROR", "Validation Error", $errors);
      }

      if (!$u) {
        return api_response(404, "ERROR", "Invalid User ID", []);
      }

      if ($u->id != $user->id) {
        if ($user->type != "admin") {
          return api_response(401, "ERROR", "Unauthorized", []);
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

      return api_response(200, "OK", "", $u);
    }

    /**
     * Get all contatcts belonging to the authenticated user
     */
    public function get_contacts(Request $request) {
      $user = $request->user();

      $contacts = Contact::where("user_id", $user->id)->get();

      return api_response(200, "OK", "", $contacts);
    }

    /**
     * Get all letters belonging to the authenticated user
     */
    public function get_letters(Request $request) {
      $user = $request->user();

      $letters = Letter::where("user_id", $user->id)->get();

      return api_response(200, "OK", "", $letters);
    }

    /**
     * Get the organization to which the authenticated user belongs
     */
    public function get_org(Request $request) {
      $user = $request->user();

      $ou = OrgUser::where("user_id", $user->id)->first();
      $o = Org::find($ou->org_id);

      if (!$ou) {
        return api_response(404, "ERROR", "No organization.", []);
      }

      return api_response(200, "OK", $ou->role, $o);
    }
}
