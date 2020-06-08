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

class SendTestNewsletter implements ShouldQueue
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
        $user = User::find($this->newsletter->user_id);
        $contact = User::find($this->newsletter->user_id);
        $org = Org::find($this->newsletter->org_id);

        $new_letter = new Letter;

        $new_letter->user_id = $this->newsletter->user_id;
        $new_letter->newsletter_id = $this->newsletter->id;

        $new_letter->contact_id = -1;
        $new_letter->content = "";
        $new_letter->attached_img_src = null;

        if (env("LOB_TESTING") == true) {
          $lob_key = env("LOB_TEST_KEY");
        } else {
          $lob_key = env("LOB_KEY");
        }

        $lob = new \Lob\Lob($lob_key);

        $from_name = $user->first_name . " " . $user->last_name;
        $from_address_1 = $user->addr_line_1;
        $from_address_2 = $user->addr_line_2;
        $from_city = $user->city;
        $from_state = $user->state;
        $from_zip = $user->postal;
        $from_country = $user->country;

        $to_name = $contact->first_name . " " . $contact->last_name;
        $to_address = $contact->addr_line_1;
        $to_address_2 = $contact->addr_line_2;
        $to_city = $contact->city;
        $to_state = $contact->state;
        $to_zip = $contact->postal;

        $lob_from = array(
          'name' => $from_name,
          'address_line1' => $from_address_1,
          'address_line2' => $from_address_2,
          'address_city' => $from_city,
          'address_state' => $from_state,
          'address_zip' => $from_zip,
          'address_country' => $from_country
        );

        $lob_to = array(
          'name' => $to_name,
          'company' => $org->name,
          'address_line1' => $to_address,
          'address_line2' => $to_address_2,
          'address_city' => $to_city,
          'address_state' => $to_state,
          'address_zip' => $to_zip
        );

        $color = true;

        $pdf_path = \Storage::disk('s3')->url('newsletter/pdf/' . $this->newsletter->pdf_path);

        $error = false;

        try {
          $lob_letter = $lob->letters()->create(array(
            'to' => $lob_to,
            'from' => $lob_from,
            'file' => $pdf_path,
            'description' => 'Test Newsletter from ' . $user->first_name . " " . $user->last_name,
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
