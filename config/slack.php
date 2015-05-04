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

    'api-url'   => 'https://slack.com/api/',
    'api-token' => env('SLACK_API_TOKEN'),

    'post-url' => env('SLACK_POST_URL'),

];
