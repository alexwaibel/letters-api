<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\OrgUser;
use App\User;

class Org extends Model
{
    protected $fillable = ['name', 'business_name', 'ein', 'website', 'address_line_1',
                           'address_line_2', 'city', 'state', 'postal'];

    public function volunteers() {
      $volunteers = [];

      $org_users = OrgUser::where('org_id', $this->id)->get();

      foreach ($org_users as $ou) {
        $u = User::find($ou->user_id);

        if ($u) {
          $l = Letter::where('user_id', $ou->user_id)->where('sent', 1)->get();
          $u->setAttribute('letters', $l);
          $d = Letter::where('user_id', $ou->user_id)->where('sent', 0)->get();
          $u->setAttribute('drafts', $d);
          $volunteers[] = $u;
        }
      }

      return $volunteers;
    }

    public function total_letters_sent() {
      $total_letters_sent = 0;

      $org_users = OrgUser::where('org_id', $this->id)->get();

      foreach ($org_users as $ou) {
        $u = User::find($ou->user_id);

        $total = $u->total_letters_sent();

        $total_letters_sent += $total;
      }

      $newsletters = Newsletter::where('org_id', $this->id)->get();

      foreach ($newsletters as $n) {
        $total_letters_sent += $n->total_letters_sent();
      }

      return $total_letters_sent;
    }

    public function volunteer_count() {
      return OrgUser::where('org_id', $this->id)->count();
    }

    public function newsletters() {
      return Newsletter::where('org_id', $this->id)->get();
    }

    public function total_unpaid_balance() {
      $letter_cost = env('LOB_LETTER_COST');
      $letter_extra_cost = env('LOB_LETTER_EXTRA_COST');

      $total_letters_sent = 0;

      $total_cost = 0.0;

      $org_users = OrgUser::where('org_id', $this->id)->get();

      foreach ($org_users as $ou) {
        $u = User::find($ou->user_id);

        $letters = Letter::where('user_id', $u->id)->get();

        foreach ($letters as $l) {
          $total_letters_sent += 1;
          $total_cost += $letter_cost;
          if ($l->page_count) {
            if ($l->page_count > 1) {
              $total_cost += ($l->page_count - 1) * $letter_extra_cost;
            }
          }
        }

      }

      $total_cost = $total_cost - $this->paid_balance;

      return number_format($total_cost, 2);
    }
}
