<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Config options for the zKill bot
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'base_url' => 'https://zkillboard.com/api/kills/',
    'kill_link' => 'https://zkillboard.com/kill/',
    'typename_link' => 'https://api.eveonline.com/eve/typeName.xml.aspx',
    'min_value' => 1000000000,
    'name' => 'DankBot',
    'emoji' => ':ptb:',

    'corps' => [
        'ptb' => [
            'id' => 101116365,   // using W.A.S.P. ID because zKill cant cope with PTB swag (PTB ID: 398598576)
            'channel' => 'api_test'
        ],
    ],

];
