<?php

namespace Friendemic\MessageBroker\Contracts;

use Friendemic\MessageBroker\Exceptions\SerializeToProtobufNotImplementedException;
use Google\Protobuf\Internal\Message;
use Illuminate\Database\Eloquent\Model;

interface MessageHandler
{
    /**
     * Return a new instance of a protobuf message class.
     *
     * @return Message
     */
    public function newProtobufMessageInstance(): Message;

    /**
     * Check if the message broker is configured.
     *
     * @return bool
     */
    public function enabled(): bool;

    /**
     * Prepare a serialized message to send to the Kafka brokers.
     *
     * @param  Model  $model
     * @return MessageHandler
     * @throws SerializeToProtobufNotImplementedException
     */
    public function prepare(Model $model): MessageHandler;

    /**
     * Send the serialized message to the Kafka brokers.
     *
     * @param  string|null  $topic
     */
    public function send(string $topic = null): void;
}
