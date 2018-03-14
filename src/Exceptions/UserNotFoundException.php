<?php

namespace Fen9li\LaravelUserActivation\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    //
    protected $message = 'No user found for the given request.';
}
