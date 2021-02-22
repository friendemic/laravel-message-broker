<?php

namespace Friendemic\MessageBroker\Exceptions;

use Exception;
use Throwable;

/**
 * Class SerializeToProtobufNotImplementedException
 * @package Friendemic\MessageBroker\Exceptions
 */
class SerializeToProtobufNotImplementedException extends Exception
{
    /**
     * SerializeToProtobufNotImplementedException constructor.
     *
     * @param  string  $identifier
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct(string $identifier, $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Model [%s] does not implement the toSerializedPb method.', $identifier);

        parent::__construct($message, $code, $previous);
    }
}
