<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;

use App\User;
use App\Donor;

class DonateController extends Controller
{
    public function process_donate(Request $request) {
      if (env('STRIPE_PROD')) {
        Stripe::setApiKey(env('STRIPE_PROD_SECRET'));
      } else {
        Stripe::setApiKey(env('STRIPE_TEST_SECRET'));
      }

      $data = json_decode($request->getContent(), true);

      $validator = Validator::make($data, [
        'stripeToken' => 'required',
        'email' => 'required|email',
        'amount' => 'required',
        'type' => 'required',
        'first_name' => 'required',
        'last_name' => 'required'
      ]);

      $token = $data['stripeToken'];
      $email = $data['email'];
      $amount = $data['amount'];
      $type = $data['type'];

      $first_name = $data['first_name'];
      $last_name = $data['last_name'];

      $customer = Customer::create(array(
        'email' => $email,
        'card' => $token
      ));

      if ($type == "one-time") {
        $charge = Charge::create(array(
          'customer' => $customer->id,
          'amount' => $amount,
          'currency' => 'usd'
        ));
      } elseif ($type == "monthly") {
        $sub = Subscription::create(array(
          'customer' => $customer->id,
          'items' => [
            [
              'plan' => env('STRIPE_MONTHLY_ID'),
              'quantity' => $amount/100
            ]
          ]
        ));
      }

      $existing_donor = Donor::where('email', $email)->first();

      if ($existing_donor) {
        $existing_donor->amount += $amount/100;

        $existing_donor->save();
      } else {
        $new_donor = new Donor;
        $new_donor->email = $email;
        $new_donor->first_name = $first_name;
        $new_donor->last_name = $last_name;
        $new_donor->amount = $amount / 100.00;

        $new_donor->manage_url = md5(uniqid());
        $new_donor->cancel_url = md5(uniqid());

        if ($type == "one-time") {
          $new_donor->monthly = false;
        } else {
          $new_donor->monthly = true;
          $new_donor->stripe_id = $sub->id;
        }

        $new_donor->newsletter = $request->input('newsletter') ? true : false;

        $new_donor->save();
      }

      return api_response("OK", "Donation Accepted", []);
    }
}
