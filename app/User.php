<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\OrgUser;
use App\Org;
use App\Letter;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'phone', 'referer', 'addr_line_1', 'addr_line_2',
        'country', 'city', 'state', 'postal', 'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin($org) {
      $org_user = OrgUser::where('org_id', $org->id)
                         ->where('user_id', $this->id)
                         ->where('role', 'admin')
                         ->first();

      if ($org_user) {
        return true;
      }

      return false;
    }

    public function org() {
      $org_user = OrgUser::where('user_id', $this->id)
                         ->where('role', 'admin')
                         ->first();

      if ($org_user) {
        $org = Org::find($org_user->org_id);

        if ($org) {
          return $org;
        } else {
          return null;
        }
      }

      return null;
    }

    public function total_letters_sent() {
      return Letter::where('user_id', $this->id)
                   ->where('sent', true)
                   ->where('lob_validation_error', false)
                   ->where('newsletter_id', null)
                   ->count();
    }

    public function letters() {
      return Letter::where('user_id', $this->id)
                   ->where('sent', true)
                   ->where('lob_validation_error', false)
                   ->where('newsletter_id', null)
                   ->orderBy('created_at', 'DESC')
                   ->get();
    }

    public function drafts() {
      return Letter::where('user_id', $this->id)
                   ->where('sent', false)
                   ->where('lob_validation_error', false)
                   ->where('newsletter_id', null)
                   ->get();
    }
}
