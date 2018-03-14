<?php

namespace Fen9li\LaravelUserActivation\Exceptions;

use Exception;

class ActivationLinkBrokenException extends Exception
{
    //
    protected $message = 'The activation link is broken.';
}
