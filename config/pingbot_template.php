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

    'api-url'               => '',
    'api-token'             => env('SLACK_API_TOKEN'),

    'post-url'              => env('SLACK_POST_URL'),

    'command-hash'          => env('PING_TOKEN'),

    'ping-allowed-channels' => [
        'G04G7KMFM',    // p_fc_chat (Specific to a given slack team)
        'G04HPDE74',    // cap_fcs
    ],
    'ping-announce-channel' => 'p_fc_chat',

    'ping-bot-name' => 'Ghost of Empress Jamyl',
    'ping-bot-emoji' => ':jamyl:',

    'ping-bots' => [
        'fc' => [
            'destination'   => 'p_fc_pings',
            'title'         => 'FC Group Ping',
            'pre-text'      => "",
            'color'         => 'warning',
            'announce'      => false
        ],
        'test' => [
            'destination'   => 'api_test',
            'title'         => ':bridge: Titan Ping',
            'pre-text'      => "A member of the FC team has sent the following message:\n",
            'color'         => 'good',
            'announce'      => false
        ],
        'all' => [
            'destination'   => '#p-fleet',
            'title'         => 'Ping',
            'pre-text'      => "",
            'color'         => '#439FE0',
            'announce'      => false
        ],
    ],

    'help-text' => "The ping bot is now multi-function!\n"
        ."Usage: /ping <type> [message]\n"
        ."Available types:\n"
        ."all - Sends ping to chatadel, goes to all registered users.\n"
        ."register - sets how your name will appear (/ping register Ortho Less) for example\n"
        ."fc - Sends ping to p_fc_pings.",

];
