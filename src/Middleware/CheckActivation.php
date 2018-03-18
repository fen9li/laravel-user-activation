<?php

namespace Fen9li\LaravelUserActivation\Middleware;

use Closure;
use voicelib\User;
use Fen9li\LaravelUserActivation\Exceptions\UserNotFoundException;

class CheckActivation
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
        // find the user by user email in login form
        $user = User::where('email', $request->email)->first();

        if ($user === null) { throw new UserNotFoundException(); }

        if ($user->activated == false) {
            return redirect('/activate/resend')->with('message','Must activate your user account by verifying your email first!');
        }

        return $next($request);
    }
}
