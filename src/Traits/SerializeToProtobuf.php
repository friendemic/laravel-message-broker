<?php

namespace Friendemic\MessageBroker\Traits;

use Doctrine\DBAL\Types\Types;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\Timestamp;
use Illuminate\Support\Str;

trait SerializeToProtobuf
{
    /**
     * @var Message
     */
    protected $protobufInstance;

    /**
     * Convert an Eloquent model instance to it's Protobuf counterpart.
     *
     * @return Message
     */
    public function toProtobuf(): Message
    {
        foreach ($this->attributesToArray() as $name => $value) {
            $column = $this->getConnection()->getDoctrineColumn($this->getTable(), $name);

            $methods = collect([
                sprintf('set%s', Str::title($name)),
                sprintf('set%sUnwrapped', Str::title($name))
            ]);

            $exists = $methods->some(function ($v) {
                return method_exists($this, $v);
            });

            // Verify either the default setter or the unwrapped helper setter methods exist.
            if (!$exists) {
                continue;
            }

            // Do any type conversion or data coalescing based on the data type as defined in the schema.
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
                    $this->protobufInstance->$methods[0]($timestamp);
                    break;
                default:
                    // Attempt to set the attribute value using the default setter.
                    // Otherwise use the "Unwrapped" helper setter.
                    try {
                        $this->protobufInstance->$methods[0]($value);
                    } catch (\Exception $exception) {
                        $this->protobufInstance->$methods[1]($value);
                    }

            }
        }

        return $this->protobufInstance;
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

    /**
     * Set the target protobuf instance used for serialization.
     *
     * @param  Message  $instance
     * @return $this
     */
    public function setProtobufMessageInstance(Message $instance): self
    {
        $this->protobufInstance = $instance;

        return $this;
    }
}
