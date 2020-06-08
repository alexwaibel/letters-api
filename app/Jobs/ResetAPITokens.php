<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Carbon\Carbon;

use App\User;

class ResetAPITokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = Carbon::now();
        $expires = Carbon::now()->addHours(2);
        $users = User::where("api_token_expires", "<", $now)->get();

        foreach ($users as $u) {
          $u->api_token = Str::random(80);
          $u->api_token_expires = $expires;
          $u->save();
        }
    }
}
