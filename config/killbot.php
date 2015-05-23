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
    'ship_renders' => 'https://image.eveonline.com/Render/',
    'min_value' => 1000000000,
    'min_capsule_value' => 100000000,
    'capsule_type_ids' => array(670,33328),
    'name' => 'DankBot',
    'emoji' => ':ptb:',

    'corps' => [
        'ptb' => [
            'id' => 398598576,
            'channel' => 'ptb',
            'active' => true
        ],
//        'wasp' => [
//            'id' => 101116365,
//            'channel' => 'api_test',
//            'active' => false
//        ],
    ],
];
