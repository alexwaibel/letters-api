<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Letter;

class Newsletter extends Model
{
    public function total_letters_sent() {
      return Letter::where('newsletter_id', $this->id)->count();
    }

    public function get_cost() {
      $letter_cost = env('LOB_LETTER_COST');
      $letter_extra_cost = env('LOB_LETTER_EXTRA_COST');

      $total_cost = 0.0;

      $letter_count = Letter::where('newsletter_id', $this->id)
                            ->where('sent', true)
                            ->where('lob_validation_error', false)
                            ->count();

      $total_cost += $letter_count * $letter_cost;
      $total_cost += ($this->page_count - 1) * $letter_extra_cost;

      return $total_cost;
    }
}
