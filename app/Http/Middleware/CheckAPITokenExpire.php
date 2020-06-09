<?php

namespace App\Http\Middleware;

use Closure;

class CheckAPITokenExpire
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user->api_token_expires < \Carbon\Carbon::now()) {
          return response(api_response("ERROR", "Expired Token", []), 401);
        }
        return $next($request);
    }
}
