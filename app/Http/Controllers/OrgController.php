<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Org;

use Validator;

class OrgController extends Controller
{
    public function __construct() {
      $this->middleware('auth:api');
      $this->middleware('throttle:60,1');
      $this->middleware('token-expire');
    }

    // get_orgs()
    // Allows admin to get a paginated list of orgs.
    // Page Limit: 20
    // Use the 'page' GET attribute to specify page
    public function get_orgs(Request $request) {
      $user = $request->user();

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $orgs = Org::paginate(20);

      return api_response("OK", "", $orgs);
    }

    public function get_org(Request $request, $id) {
      $user = $request->user();

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $o = Org::find($id);

      if (!$o) {
        return api_response("ERROR", "Invalid Org ID", []);
      }

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      return api_response("OK", "", $o);
    }

    public function create_org(Request $request) {
      $user = $request->user();

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $data = json_decode($request->getContent(), true);

      $validator = Validator::make($data, [
        'name' => 'required|max:255',
        'business_name' => 'required|max:255',
        'ein' => 'required|max:255',
        'website' => 'required|max:255',
        'address_line_1' => 'required|max:255',
        'address_line_2' => 'nullable',
        'city' => 'required',
        'state' => 'required',
        'postal' => 'required',
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors();

        return api_response("ERROR", "Validation Error", $errors);
      }

      $o = new Org;

      $o->name = $data['name'];
      $o->business_name = $data['business_name'];
      $o->ein = $data['ein'];
      $o->website = $data['website'];
      $o->address_line_1 = $data['address_line_1'];

      if (isset($data['address_line_2'])) {
        $o->address_line_2 = $data['address_line_2'];
      }

      $o->city = $data['city'];
      $o->state = $data['state'];
      $o->postal = $data['postal'];

      $o->paid_balance = 0.0;

      $o->save();

      return api_response("OK", "", $o);
    }

    public function update_org(Request $request, $id) {
      $user = $request->user();

      $data = json_decode($request->getContent(), true);

      $o = Org::find($id);

      if (!$o) {
        return api_response("ERROR", "Invalid Org ID", []);
      }

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $validator = Validator::make($data, [
        'name' => 'required|max:255',
        'business_name' => 'required|max:255',
        'ein' => 'required|max:255',
        'website' => 'required|max:255',
        'address_line_1' => 'required|max:255',
        'address_line_2' => 'nullable',
        'city' => 'required',
        'state' => 'required',
        'postal' => 'required',
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors();

        return api_response("ERROR", "Validation Error", $errors);
      }

      $o->name = $data['name'];
      $o->business_name = $data['business_name'];
      $o->ein = $data['ein'];
      $o->website = $data['website'];
      $o->address_line_1 = $data['address_line_1'];

      if (isset($data['address_line_2'])) {
        $o->address_line_2 = $data['address_line_2'];
      }

      $o->city = $data['city'];
      $o->state = $data['state'];
      $o->postal = $data['postal'];

      $o->paid_balance = 0.0;

      $o->save();

      return api_response("OK", "", $o);
    }
}
