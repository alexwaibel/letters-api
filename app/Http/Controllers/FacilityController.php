<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Facility;
use App\User;

class FacilityController extends Controller
{
  public function __construct() {
    $this->middleware('auth:api');
    $this->middleware('throttle:60,1');
    $this->middleware('token-expire');
  }

  // get_facilities()
  // Allows admin to get a paginated list of facilities.
  // Page Limit: 20
  // Use the 'page' GET attribute to specify page
  public function get_facilities(Request $request) {
    $user = $request->user();

    if ($user->type != "admin") {
      return api_response("ERROR", "Unauthorized", []);
    }

    $facilities = Facility::paginate(20);

    return api_response("OK", "", $facilities);
  }

  public function get_facility(Request $request, $id) {
    $user = $request->user();

    if ($user->type != "admin") {
      return api_response("ERROR", "Unauthorized", []);
    }

    $f = Facility::find($id);

    if (!$f) {
      return api_response("ERROR", "Invalid Facility ID", []);
    }

    if ($f->user_id != $user->id) {
      if ($user->type != "admin") {
        return api_response("ERROR", "Unauthorized", []);
      }
    }

    return api_response("OK", "", $f);
  }

  public function create_facility(Request $request) {
    $user = $request->user();

    if ($user->type != "admin") {
      return api_response("ERROR", "Unauthorized", []);
    }

    $data = json_decode($request->getContent(), true);

    $validator = Validator::make($data, [
      'full_name' => 'required|max:255',
      'name' => 'required|max:255',
      'address' => 'required|max:255',
      'city' => 'required|max:255',
      'state' => 'required|max:2',
      'postal' => 'required|max:20',
      'link' => 'nullable',
      'federal' => 'nullable',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();

      return api_response("ERROR", "Validation Error", $errors);
    }

    $f = new Facility;

    $f->name = $data['name'];
    $f->full_name = $data['full_name'];
    $f->address = $data['address'];
    $f->city = $data['city'];
    $f->state = $data['state'];
    $f->postal = $data['postal'];

    if (isset($data['link'])) {
      $f->link = $data['link'];
    }

    if (isset($data['federal'])) {
      $f->federal = true;
    } else {
      $f->federal = false;
    }

    $f->save();

    return api_response("OK", "", $f);
  }

  public function update_facility(Request $request, $id) {
    $user = $request->user();

    $data = json_decode($request->getContent(), true);

    $f = Facility::find($id);

    if (!$f) {
      return api_response("ERROR", "Invalid Facility ID", []);
    }

    if ($user->type != "admin") {
      return api_response("ERROR", "Unauthorized", []);
    }

    $validator = Validator::make($data, [
      'full_name' => 'required|max:255',
      'name' => 'required|max:255',
      'address' => 'required|max:255',
      'city' => 'required|max:255',
      'state' => 'required|max:2',
      'postal' => 'required|max:20',
      'link' => 'nullable',
      'federal' => 'nullable',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();

      return api_response("ERROR", "Validation Error", $errors);
    }

    $f->name = $data['name'];
    $f->full_name = $data['full_name'];
    $f->address = $data['address'];
    $f->city = $data['city'];
    $f->state = $data['state'];
    $f->postal = $data['postal'];

    if (isset($data['link'])) {
      $f->link = $data['link'];
    }

    if (isset($data['federal'])) {
      $f->federal = true;
    } else {
      $f->federal = false;
    }

    $f->save();

    return api_response("OK", "", $f);
  }
}
