<?php

namespace Fen9li\LaravelUserActivation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Fen9li\LaravelUserActivation\Traits\ActivatesUsers;
use Session;

class ActivateController extends Controller
{

    use ActivatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('web');
    }

    /**
     * Handle activation link resend request
     *
     * @param  Illuminate\Http\Request $request
     * $request->email
     *
     */
    public function resend(Request $request)
    {
        $this->validator($request->all());

        $user = $this->getUserByEmail($request->email);

        // create, save and email user_activations token
        $token = $this->generateToken();
        $this->updateToken($request->email, $token);
        $this->emailToken($user, $token);

        return redirect('/activate/resend')->with('success','We have send you the activation link. Please check your email ... ');
    }

    /**
     * Take over from register method in Auth/RegisterController.php.
     *
     * @param  Illuminate\Http\Request $request
     * user id passed in $request query array
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function send(Request $request)
    {

// dd($request->fullUrl());
// "http://localhost/activate/send?1"

        $userId = key($request->query());
        $user = User::findOrFail($userId);

        // create, save and email user_activations token
        $token = $this->generateToken();
        $this->updateToken($user->email, $token);
        $this->emailToken($user, $token);

        return redirect('/activate/resend')->with('success','We have send you the activation link. Please check your email ... ');
    }

}
