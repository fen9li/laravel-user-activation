<?php

return [

   /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    */

   'redirects' => [
        'redirectToAfterActivation' => '/home',
    ],

   /*
    |--------------------------------------------------------------------------
    | Database tables
    |--------------------------------------------------------------------------
    */

    'tables' => [
        'users' => 'users',
        'user_activations' => 'user_activations',
    ],

   /*
    |--------------------------------------------------------------------------
    | User Activation Email
    |--------------------------------------------------------------------------
    */

    'uaemail' => [
        'subject' => 'From voicelib.com: Your User Account Activation Link',
        'replyTo' => 'arthur.f.li@gmail.com',
        'from' => 'arthur.f.li@gmail.com',
    ],
];
