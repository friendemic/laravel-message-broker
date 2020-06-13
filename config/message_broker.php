<?php return [

    /*
    |--------------------------------------------------------------------------
    | Default Message Broker
    |--------------------------------------------------------------------------
    |
    | A default message broker connection to use from list below
    |
    */

    'default' => env('MESSAGE_BROKER', 'kafka'),

    /*
    |--------------------------------------------------------------------------
    | Brokers
    |--------------------------------------------------------------------------
    |
    | These are the message brokers setup for the application
    |
    */

    'brokers' => [

        'kafka' => [
            'driver'      => 'kafka',
            'broker_list' => env('KAFKA_BROKERS', null),
            'debug'       => false,
        ],
    ]
];
