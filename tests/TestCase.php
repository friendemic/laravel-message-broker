<?php

use Orchestra\Testbench\TestCase as BaseTestCase;
use Friendemic\MessageBroker\MessageBrokerProvider;

class TestCase extends BaseTestCase
{

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // set config
        $app['config']->set('message_broker', [
            'default' => 'kafka',
            'brokers' => [
                'kafka' => [
                  'driver'      => 'kafka',
                  'broker_list' => 'some.where.at:port',
                  'debug'       => false,
                ],
            ]
        ]);
    }

    /**
     * Get package service providers.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'Friendemic\MessageBroker\MessageBrokerProvider'
        ];
    }
    
    protected function getPackageAliases($app)
    {
        return [
            'MessageBroker' => 'Friendemic\MessageBroker\Facades\MessageBroker'
        ];
    }

}
