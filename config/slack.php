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

    'post-url' => env('SLACK_POST_URL'),

    'token-expression' => '/api_token: \'([a-zA-Z0-9-]*)\'/',

    'auto-join-channels' => ['C04G7GNLT'],

];
