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
            // pass additional configuration options here
            // see // https://github.com/edenhill/librdkafka/blob/master/CONFIGURATION.md
            // 'config'      => [
            //     'group.id' => 'myConsumerGroup'
            // ], 
        ],
    ]
];
