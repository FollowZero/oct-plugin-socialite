<?php

Route::group(
    [
        'middleware' => 'web',
        'prefix' => 'socialite',
        'namespace' => 'Plus\Socialite\Http\Controllers',
    ],
    function () {


        Route::get(
            'authorize/{profile?}',
            'SocialiteController@authorize'
        );
        Route::get(
            'callback/{profile?}',
            'SocialiteController@callback'
        );

        Route::get(
            'test',
            'SocialiteController@test'
        );

    }
);






