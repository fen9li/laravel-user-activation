<?php

Route::get('activate/send', 'Fen9li\LaravelUserActivation\ActivateController@send')->name('activate.send');
Route::get('activate/resend', 'Fen9li\LaravelUserActivation\ActivateController@showResendForm')->name('activate.resend');
Route::post('activate/resend', 'Fen9li\LaravelUserActivation\ActivateController@resend')->name('activate.resend.post');
Route::get('activate/{token}', 'Fen9li\LaravelUserActivation\ActivateController@activate')->name('activate');
