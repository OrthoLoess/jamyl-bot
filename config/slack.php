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

    'auto-join-channels' => ['C04G7GNLT', 'C050GQPC0'],

    'command-tokens' => [
        '/portrait' => env('SLACK_PORTRAIT_TOKEN'),
        '/punk'     => env('SLACK_PUNK_TOKEN'),
    ],

    'cache-time' => 5,

    'jamyl-id' => 'U04JN1L0C',

    'channels-to-manage' => [
        'G04FM566L',    // api_test
        'G04V6HSHC',    // group_test
        'G04G7KMFM',    // p_fc_chat
        'G04G7KRUD',    // p_fc_pings
        'G04GC8QMX',    // titan_pings
        'G04HPDE74',    // cap_fcs
        'G04QGUA5H',    // p-scouts
        'G04N41BB8',    // profidence
        'G077ZP93P',    // a_gfa
        'G07HYTANM',    // cva_c
        'G0A8VGD47',    // c_caps
        'G0ALFSVDZ',    // no_caps
        'G0D7SCFHT',    // scrubs_and_shitlords
        'G0J2KRB5G',    // tlos
    ],

];
