<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\User;
use App\Contact;

class ContactController extends Controller
{
    public function __construct() {
      $this->middleware('auth:api');
      $this->middleware('throttle:60,1');
    }

    // get_contacts()
    // Allows admin to get a paginated list of contacts.
    // Page Limit: 20
    // Use the 'page' GET attribute to specify page
    public function get_contacts(Request $request) {
      $user = $request->user();

      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }

      $contacts = Contact::paginate(20);

      return api_response("OK", "", $contacts);
    }

    public function get_contact(Request $request, $id) {
      $user = $request->user();

      $c = Contact::find($id);

      if (!$c) {
        return api_response("ERROR", "Invalid Contact ID", []);
      }

      if ($c->user_id != $user->id) {
        if ($user->type != "admin") {
          return api_response("ERROR", "Unauthorized", []);
        }
      }

      return api_response("OK", "", $c);
    }

    public function create_contact(Request $request) {
      $user = $request->user();

      $data = json_decode($request->getContent(), true);

      $validator = Validator::make($data, [
        'first_name' => 'required|max:255',
        'last_name' => 'required|max:255',
        'inmate_number' => 'required|max:255',
        'facility_name' => 'required|max:255',
        'facility_address' => 'required|max:255',
        'facility_city' => 'required|max:255',
        'facility_state' => 'required|max:2',
        'facility_postal' => 'required|max:20',
        'relationship' => 'required|max:255',
        's3_img_url' => 'nullable',
        'dorm' => 'nullable',
        'unit' => 'nullable',
        'org_id' => 'nullable'
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors();

        return api_response("ERROR", "Validation Error", $errors);
      }

      $c = new Contact;

      $c->first_name = $data['first_name'];
      $c->last_name = $data['last_name'];
      $c->inmate_number = $data['inmate_number'];
      $c->facility_name = $data['facility_name'];
      $c->facity_address = $data['facility_address'];
      $c->facility_city = $data['facility_city'];
      $c->facility_state = $data['facility_state'];
      $c->facility_postal = $data['facility_postal'];
      $c->relationship = $data['relationship'];

      if (isset($data['s3_img_url'])) {
        $c->profile_img_path = $data['s3_img_url'];
      }

      if (isset($data['facility_dorm'])) {
        $c->dorm = $data['facility_dorm'];
      }

      if (isset($data['facility_unit'])) {
        $c->unit = $data['facility_unit'];
      }

      $c->save();

      return api_response("OK", "", $c);
    }

    public function update_contact(Request $request, $id) {
      $user = $request->user();

      $data = json_decode($request->getContent(), true);

      $c = Contact::find($id);

      if (!$c) {
        return api_response("ERROR", "Invalid Contact ID", []);
      }

      $validator = Validator::make($data, [
        'first_name' => 'required|max:255',
        'last_name' => 'required|max:255',
        'inmate_number' => 'required|max:255',
        'facility_name' => 'required|max:255',
        'facility_address' => 'required|max:255',
        'facility_city' => 'required|max:255',
        'facility_state' => 'required|max:2',
        'facility_postal' => 'required|max:20',
        'relationship' => 'required|max:255',
        's3_img_url' => 'nullable',
        'dorm' => 'nullable',
        'unit' => 'nullable',
        'org_id' => 'nullable'
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors();

        return api_response("ERROR", "Validation Error", $errors);
      }

      $c->first_name = $data['first_name'];
      $c->last_name = $data['last_name'];
      $c->inmate_number = $data['inmate_number'];
      $c->facility_name = $data['facility_name'];
      $c->facity_address = $data['facility_address'];
      $c->facility_city = $data['facility_city'];
      $c->facility_state = $data['facility_state'];
      $c->facility_postal = $data['facility_postal'];
      $c->relationship = $data['relationship'];

      if (isset($data['s3_img_url'])) {
        $c->profile_img_path = $data['s3_img_url'];
      }

      if (isset($data['facility_dorm'])) {
        $c->dorm = $data['facility_dorm'];
      }

      if (isset($data['facility_unit'])) {
        $c->unit = $data['facility_unit'];
      }

      $c->save();

      return api_response("OK", "", $c);
    }
}
