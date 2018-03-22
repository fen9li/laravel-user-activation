<?php

namespace Fen9li\LaravelUserActivation\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use voicelib\User;
use Fen9li\LaravelUserActivation\Mail\EmailToken;

// user verification Exceptions
use Fen9li\LaravelUserActivation\Exceptions\UserNotFoundException;
use Fen9li\LaravelUserActivation\Exceptions\ActivationLinkBrokenException;

trait ActivatesUsers
{

    /**
     * Handle the user activation request
     *
     * Illuminate\Http\Request $request
     * @return Response
     *
     * @throws \Fen9li\LaravelUserActivation\Exceptions\TokenMismatchException
     */
    public function activate(Request $request)
    {
        $this->validateActivationRequest($request);

        //get the request token
        $requestSegments = $request->segments();
        array_shift($requestSegments);
        $requestToken = array_shift($requestSegments);

        //get the user requests activation
        $user = $this->getUserByEmail($request->input('email'));

        //get the saved token for this user
        $savedToken = $this->getSavedTokenByEmail($request->input('email'));

        if ($requestToken == $savedToken) {
            $this->markUserActivated($user);
            $this->clearToken($savedToken);
            $this->guard()->login($user, 'true');
            return redirect(config('userActivation.redirects.redirectToAfterActivation'));
        } else {
            throw new ActivationLinkBrokenException();
        }
    }

    /**
     * clear saved token
     *
     * @param  string  $token
     * @return void
     */
    protected function clearToken($token)
    {
        DB::table(config('userActivation.tables.user_activations'))
            ->where('token', $token)
            ->delete();
    }

    /**
     * Mark user as activated
     *
     * @param  App\User $user
     * @return void
     */
    protected function markUserActivated($user)
    {
        $user->activated = true ;
        $user->activated_at = Carbon::now();
        $user->save();
    }

    /**
     * Get the guard to be used after activation succeeds.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get the saved token by e-mail.
     *
     * @param  string $email
     * @return string $token
     *
     * @throws \Fen9li\LaravelUserActivation\Exceptions\ActivationLinkBrokenException
     */
    protected function getSavedTokenByEmail($email)
    {
        $token = DB::table(config('userActivation.tables.user_activations'))
                 ->where('email', $email)
                 ->first(['token']);
        if ($token === null) {
            // clear broken record in database table
               DB::table(config('userActivation.tables.user_activations'))
                   ->where('email', $email)
                   ->delete();
            //
            throw new ActivationLinkBrokenException();
        }
        return $token->token;
    }

    /**
     * Get the user by e-mail.
     *
     * @param  string  $email
     * @return Evenboom\User $user
     *
     * @throws \Fen9li\LaravelUserActivation\Exceptions\UserNotFoundException
     */
    protected function getUserByEmail($email)
    {
        $user = User::where('email', $email)->first();
        if ($user === null) {
            throw new UserNotFoundException();
        }
        return $user;
    }

    /**
     * Validate the verification link.
     *
     * @param  string  $token
     * @return Response
     */
    protected function validateActivationRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
           throw new ActivationLinkBrokenException();
        }
    }

    /**
     * Get a validator for an incoming activation request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|email',
        ]);
    }

    /**
     * Display the user activate link resend view.
     *
     * @return \Illuminate\Http\Response
     */
    public function showResendForm()
    {
        return view('laravel-user-activation::resend');
    }

    /**
     * Generate the activation token.
     *
     * @return string|bool
     */
    protected function generateToken()
    {
        return hash_hmac('sha256', str_random(40), config('app.key'));
    }

    /**
     * Update token in table user_activations
     *
     * @param  string  $email, $token
     * @return void
     */
    protected function updateToken($email,$token)
    {
       // delete old record if exists
       $old = DB::table(config('userActivation.tables.user_activations'))
                   ->where('email', $email)
                   ->first();
       if ($old !== null)
          { 
            DB::table(config('userActivation.tables.user_activations'))
                   ->where('email', $email)
                   ->delete();
          }

       // insert new record
       DB::table(config('userActivation.tables.user_activations'))
            ->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
    }

    /**
     * Insert token in table user_activations
     *
     * @param  string  $email, $token
     * @return void
     */
    protected function saveToken($email,$token)
    {
        DB::table(config('userActivation.tables.user_activations'))
            ->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
    }

    /**
     * email user account activation link
     *
     * @param  \App\User  $user
     * @param  string $token
     * @return mixed
     */
    public function emailToken(User $user, $token)
    {
        Mail::to($user)->send(new EmailToken($user,$token));
    }
}
