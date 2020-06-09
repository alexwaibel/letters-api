<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;

use Validator;

use App\User;
use App\Contact;
use App\Letter;

class LetterController extends Controller
{
    public function __construct() {
      $this->middleware('auth:api');
      $this->middleware('throttle:60,1');
      $this->middleware('token-expire');
    }

    // get_letters()
    // Allows admin to get a paginated list of letters.
    // Page Limit: 20
    // Use the 'page' GET attribute to specify page
    public function get_letters(Request $request) {
      $user = $request->user();

      if ($user->type == "admin") {
        $letters = Letter::paginate(20);
      } else {
        $letter = Letter::where("user_id", $user->id)->paginate(20);
      }

      return api_response("OK", "", $letters);
    }

    public function get_letter(Request $request, $id) {
      $user = $request->user();

      $l = Letter::find($id);

      if (!$l) {
        return api_response("ERROR", "Invalid Letter ID", []);
      }

      if ($l->user_id != $user->id) {
        if ($user->type != "admin") {
          return api_response("ERROR", "Unauthorized", []);
        }
      }

      return api_response("OK", "", $l);
    }

    public function create_letter(Request $request) {
      $user = $request->user();

      if ($user->credit <= 0) {
        return api_response("ERROR", "Reached Letter Limit", []);
      }

      $data = json_decode($request->getContent(), true);

      $validator = Validator::make($data, [
        'letter_id' => 'nullable',
        'contact_id' => 'required',
        'content' => 'required',
        'is_draft' => 'nullable',
        's3_img_url' => 'nullable'
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors();

        return api_response("ERROR", "Validation Error", $errors);
      }

      // Grab the Letter that already exists OR
      // create a new Letter object.
      if (isset($data['letter_id'])) {
        $letter = Letter::find($data['letter_id']);

        if (!$letter) {
          return api_response("ERROR", "Invalid Letter ID", []);
        }
      } else {
        $letter = new Letter;
      }

      // Get Contact by contact_id
      $contact = Contact::find($data['contact_id']);

      if (!$contact) {
        return api_response("ERROR", "Invalid Contact ID", []);
      }

      if ($contact->user_id != $user->id) {
        if ($user->type != "admin") {
          return api_response("ERROR", "Unauthorized", []);
        }
      }

      $letter->user_id = $user->id;
      $letter->contact_id = $data['contact_id'];
      $letter->content = $data['content'];


      // Determine if $color should be true or false
      $color = false;

      if (isset($data['s3_img_url'])) {
        $color = true;
        $letter->attached_img_src = $data['s3_img_url'];
      }

      if (isset($data['is_draft'])) {
        $letter->sent = false;
        $letter->save();

        return api_response("OK", "Saved Draft", $letter);
      }


      // At this point, we know that the letter IS NOT a draft
      // So let's prepare to send it.

      if (env("LOB_TESTING") == true) {
        $lob_key = env("LOB_TEST_KEY");
      } else {
        $lob_key = env("LOB_KEY");
      }

      $lob = new \Lob\Lob($lob_key);


      // Setup FROM ADDRESS information
      $from_name = $user->first_name . " " . $user->last_name;
      $from_address_1 = $user->addr_line_1;
      $from_address_2 = $user->addr_line_2;
      $from_city = $user->city;
      $from_state = $user->state;
      $from_zip = $user->postal;
      $from_country = $user->country;


      // Setup TO ADDRESS information
      $to_name = $contact->first_name . " " . $contact->last_name . ", " . $contact->inmate_number;
      $to_facility_name = $contact->facility_name;

      $to_line_2 = "";

      if ($contact->unit) {
        $to_line_2 = $to_line_2 . "Unit #" . $contact->unit . " ";
      }

      if ($contact->dorm) {
        $to_line_2 = $to_line_2 . "Dorm #" . $contact->dorm . " ";
      }

      $to_address = $contact->facility_address;
      $to_city = $contact->facility_city;
      $to_state = $contact->facility_state;
      $to_zip = $contact->facility_postal;


      // setup lob_from array
      $lob_from = array(
          'name' => $from_name,
          'address_line1' => $from_address_1,
          'address_line2' => $from_address_2,
          'address_city' => $from_city,
          'address_state' => $from_state,
          'address_zip' => $from_zip,
          'address_country' => $from_country
      );


      // Setup lob_to array
      if (strlen($to_line_2) > 0) {
        $lob_to = array(
          'name' => $to_name,
          'company' => $to_facility_name,
          'address_line1' => $to_address,
          'address_line2' => $to_line_2,
          'address_city' => $to_city,
          'address_state' => $to_state,
          'address_zip' => $to_zip
        );
      } else {
        $lob_to = array(
          'name' => $to_name,
          'company' => $to_facility_name,
          'address_line1' => $to_address,
          'address_city' => $to_city,
          'address_state' => $to_state,
          'address_zip' => $to_zip
        );
      }


      // SEND THE LETTER!!!
      $data = Carbon::now()->toFormattedDateString();
      $lob_letter = null;

      $letter->verify_token = Str::random(20);
      $letter->save();

      try {
        $lob_letter = $lob->letters()->create(array(
          'to' => $lob_to,
          'from' => $lob_from,
          'file' => url("/public/letter/html/" . $letter->verify_token),
          'description' => 'Letter from ' . $user->first_name . " " . $user->last_name . " to Inmate # " . $contact->inmate_number,
          'color' => $color,
          'double_sided' => false
        ));
      } catch (\Lob\Exception\ValidationException $e) {
        $url_issue = strpos($e->getMessage(), "URL") !== false;
        $html_issue = strpos($e->getMessage(), "HTML") !== false;

        $letter->lob_message = $e->getMessage();

        if ($url_issue or $html_issue) {
          // pass
        } else {
          $letter->lob_validation_error = true;
        }
      }


      // If the letter has been sent correctly,
      // lets update some values in the letter object
      if ($lob_letter) {
        $letter->lob_id = $lob_letter["id"];
        $letter->sent = true;

        $user->credit -= 1;
        $user->save();
      } else {
        $letter->sent = false;
      }

      $letter->save();

      if ($letter->sent) {
        return api_response("OK", "Letter Sent", $letter);
      } else {
        return api_response("ERROR", "Lob Error", $letter->lob_message);
      }
    }
}
