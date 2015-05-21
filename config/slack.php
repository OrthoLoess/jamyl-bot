<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Config options for the Slack bot
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'api-url'   => 'https://provibloc.slack.com/api/',
    'api-token' => env('SLACK_API_TOKEN'),
    'admin-token' => env('SLACK_ADMIN_TOKEN'),
    'register-token' => env('SLACK_REGISTER_TOKEN'),

    'post-url' => env('SLACK_POST_URL'),

    'token-expression' => '/api_token: \'([a-zA-Z0-9-]*)\'/',

    'auto-join-channels' => ['C04G7GNLT'],

    'command-tokens' => [
        '/portrait' => env('SLACK_PORTRAIT_TOKEN'),
    ],

    'cache-time' => 5,

    'jamyl-id' => 'U04JN1L0C',

];
