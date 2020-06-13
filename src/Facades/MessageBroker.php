<?php

namespace Friendemic\MessageBroker\Facades;

use Illuminate\Support\Facades\Facade;

class MessageBroker extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'message_broker';
    }
}
