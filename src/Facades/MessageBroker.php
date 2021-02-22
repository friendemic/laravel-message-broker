<?php

namespace Friendemic\MessageBroker\Facades;

use Friendemic\MessageBroker\Brokers\Kafka;
use Illuminate\Support\Facades\Facade;

/** @mixin Kafka */
class MessageBroker extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'message_broker';
    }
}
