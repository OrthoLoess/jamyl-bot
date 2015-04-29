<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Config options for the Slack Pingbot
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'api-token' => env('SLACK_API_TOKEN'),

    'post-url' => env('SLACK_POST_URL'),

    'slash-hashes' => [
        'fcping' => env('FCPING_HASH'),
        'titanping' => env('TITANPING_HASH'),
    ],

];
