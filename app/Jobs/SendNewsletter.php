<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Newsletter;
use App\Contact;
use App\Letter;
use App\User;
use App\Org;

class SendNewsletter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $newsletter;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $contacts = Contact::where('org_id', $this->newsletter->org_id)->get();

        $user = User::find($this->newsletter->user_id);
        $org = Org::find($this->newsletter->org_id);

        $from_name = $org->name;
        $from_address_1 = $org->address_line_1;
        $from_address_2 = $org->address_line_2;
        $from_city = $org->city;
        $from_state = $org->state;
        $from_zip = $org->postal;

        $lob_from = array(
          'name' => $from_name,
          'address_line1' => $from_address_1,
          'address_line2' => $from_address_2,
          'address_city' => $from_city,
          'address_state' => $from_state,
          'address_zip' => $from_zip
        );

        if (env("LOB_TESTING") == true) {
          $lob_key = env("LOB_TEST_KEY");
        } else {
          $lob_key = env("LOB_KEY");
        }

        $lob = new \Lob\Lob($lob_key);


        foreach ($contacts as $contact) {
          // Lob TO ADDRESS information
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
          // END Lob TO ADDRESS information

          $color = true;
          $pdf_path = \Storage::disk('s3')->url('newsletter/pdf/' . $this->newsletter->pdf_path);

          $new_letter = new Letter;

          $new_letter->user_id = $this->newsletter->user_id;
          $new_letter->newsletter_id = $this->newsletter->id;

          $new_letter->contact_id = $contact->id;
          $new_letter->content = "";
          $new_letter->attached_img_src = null;

          // SEND LETTER
          $error = false;

          try {
            $lob_letter = $lob->letters()->create(array(
              'to' => $lob_to,
              'from' => $lob_from,
              'file' => $pdf_path,
              'description' => 'Newsletter (' . $this->newsletter->name . ') from ' . $org->name . ' to Inmate #' . $contact->inmate_number,
              'color' => $color,
              'double_sided' => false
            ));

          } catch (\Lob\Exception\ValidationException $e) {
            $url_issue = strpos($e->getMessage(), "URL") !== false;
            $html_issue = strpos($e->getMessage(), "HTML") !== false;

            $new_letter->lob_message = $e->getMessage();
            $new_letter->sent = false;
            $error = true;
          }

          if ($error) {
            $new_letter->lob_validation_error = true;
            $new_letter->sent = false;

            $this->newsletter->status = "error";
          } else {
            $new_letter->lob_id = $lob_letter["id"];
            $new_letter->sent = true;

            $this->newsletter->status = "sent";
          }

          $new_letter->save();

          $this->newsletter->save();
        }

    }
}
