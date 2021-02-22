<?php

namespace Friendemic\MessageBroker\Traits;

use Doctrine\DBAL\Types\Types;
use Friendemic\MessageBroker\MessageHandlers\BaseMessageHandler;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\Timestamp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 * @mixin BaseMessageHandler
 */
trait SerializeToProtobuf
{
    /**
     * Convert an Eloquent model instance to it's Protobuf counterpart.
     *
     * @return Message
     */
    public function toProtobuf(): Message
    {
        $instance = $this->newProtobufInstance();

        foreach ($this->attributesToArray() as $name => $value) {
            $column = $this->getConnection()->getDoctrineColumn($this->getTable(), $name);
            $method = sprintf('set%s', Str::title($name));

            if (!method_exists($this, $method)) {
                continue;
            }

            switch ($column->getType()->getName()) {
                case Types::DATE_IMMUTABLE:
                case Types::DATE_MUTABLE:
                case Types::DATEINTERVAL:
                case Types::DATETIME_IMMUTABLE:
                case Types::DATETIME_MUTABLE:
                case Types::DATETIMETZ_IMMUTABLE:
                case Types::DATETIMETZ_MUTABLE:
                    $timestamp = new Timestamp();
                    $timestamp->setSeconds(strtotime($value));
                    $instance->$method($timestamp);
                    break;
                default:
                    $instance->$method($value);
            }
        }

        return $instance;
    }

    /**
     * Serialize an Eloquent model instance.
     *
     * @return string
     */
    public function toSerializedPb(): string
    {
        return $this->toProtobuf()->serializeToString();
    }
}
