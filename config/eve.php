<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Config options for eve APIs etc
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'avatar_sizes'  => [32, 64, 128, 256, 512, 1024],
    'avatar_url'  => 'https://image.eveonline.com/Character/',
    'pheal_cache' => env('PHEAL_CACHE', 'redis'),
    'batch-size' => 500,
    'static-cache-minutes' => 10080,

    'esi-base' => 'https://esi.evetech.net',
    'affiliation-route' => '/v1/characters/affiliation/',
    'names-route' => '/v2/universe/names/',


];
