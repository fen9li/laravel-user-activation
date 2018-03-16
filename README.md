**fen9li/laravel-user-activation** is a PHP package built for Laravel 5.5 to easily handle a user / email activation.

## VERSIONS

**This package has been tested with Laravel 5.5.28 successfully.**

## ABOUT

- [x] Generate and store a activation token for a registered user
- [x] Send an email with the activation token link
- [x] Handle the token activation
- [x] Set the user as activated
- [x] Resend activation token link

## PREREQUISITE

- [x] Fresh install Laravel project 5.5.28
- [x] Setup user auth
- [x] User model must be set as App\User::class
- [x] User table must be named as `users`

## INSTALLATION

This project can be installed via [Composer](http://getcomposer.org). To get the latest statble version of Laravel User Activation, add the following line to the require block of your composer.json file:

    {
        "require": {
             "fen9li/laravel-user-activation": "dev-develop"
        }
    }

You'll then need to run `composer install` or `composer update` to download the package and have the autoloader updated.

Or run the following command:

    composer require fen9li/laravel-user-activation:dev-develop

**No need to add the Service Provider manually.** Once Larvel User Verification is installed, it will be picked up by Laravel framework.

## CONFIGURATION

- [x] Ensure database configured in .env
- [x] Ensure email configured in .env

## Migration

The table representing the user must be updated with two new columns, `activated` and `activated_at`. This update will be performed by the migrations included with this package.

**It is mandatory that the two columns are on the same table where the user's email is stored. Please make sure you do not already have those fields on your user table.**

A new table `user_activations` will be created to handle activation token.

To run the migrations, run following command:

```
php artisan migrate
```

## Hook up

### Update 'app/Http/Controllers/Auth/RegisterController.php' as below

```
$ cat  app/Http/Controllers/Auth/RegisterController.php     
<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());
        return redirect()->route('activate.send',$user->id);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
$
```

### Update 'register.blade.php'

```
$ sed -n '3,14p' resources/views/auth/register.blade.php
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Register</div>

                <div class="panel-body">

    @include('laravel-user-activation::message')

                    <form class="form-horizontal" method="POST" action="{{ route('register') }}">
$
```

### Update 'app/Exceptions/Handler.php'

```
$ cat app/Exceptions/Handler.php
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

// user activation exceptions
use Fen9li\LaravelUserActivation\Exceptions\UserNotFoundException;
use Fen9li\LaravelUserActivation\Exceptions\ActivationLinkBrokenException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // user activation exceptions
        if ($exception instanceof UserNotFoundException)
        {
            return redirect('/register')->with('message',$exception->getMessage());
        }
        // user activation exceptions
        if ($exception instanceof ActivationLinkBrokenException)
        {
            return redirect('/activate/resend')->with('message',$exception->getMessage());
        }

        return parent::render($request, $exception);
    }
}
$
```

### Update 'app/Http/Kernel.php'

```
$ sed -n '53,61p' app/Http/Kernel.php
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'activation' => \Fen9li\LaravelUserActivation\Middleware\CheckActivation::class,
    ];
$
```

### Update 'app/Http/Controllers/Auth/LoginController.php'

```
$ sed -n '35,39p' app/Http/Controllers/Auth/LoginController.php
    public function __construct()
    {
        $this->middleware('activation',['only' => 'login']);
        $this->middleware('guest')->except('logout');
    }
$
```

## USAGE

### Routes

By default this packages ships with four routes.

```
$ php artisan route:list | grep activate
| GET|HEAD | activate/resend        | activate.resend      | Fen9li\LaravelUserActivation\ActivateController@showResendForm         | web                  |
| POST     | activate/resend        | activate.resend.post | Fen9li\LaravelUserActivation\ActivateController@resend                 | web                  |
| GET|HEAD | activate/send          | activate.send        | Fen9li\LaravelUserActivation\ActivateController@send                   | web                  |
| GET|HEAD | activate/{token}       | activate             | Fen9li\LaravelUserActivation\ActivateController@activate               | web                  |
$
```

### RELAUNCH THE PROCESS ANYTIME

At this point, after registration, an e-mail is sent to the user. Click the link within the e-mail and the user will be activated against the token.

If you want to regenerate and resend the verification token, should anything went wrong, you can try to login with your un activated account and you will be directed to 'resend activation link email' page automatically. You can then have a new activation link email.

## CONTRIBUTE

Feel free to comment, contribute and help. 

## LICENSE

Laravel User Activation is licensed under [The MIT License (MIT)](https://github.com/jrean/laravel-user-verification/blob/master/LICENSE).
