<?php

namespace Friendemic\MessageBroker\MessageHandlers;

use Friendemic\MessageBroker\Contracts\MessageHandler;
use Friendemic\MessageBroker\Exceptions\SerializeToProtobufNotImplementedException;
use Friendemic\MessageBroker\Facades\MessageBroker;
use Google\Protobuf\Internal\Message;
use Illuminate\Database\Eloquent\Model;

abstract class BaseMessageHandler implements MessageHandler
{
    /**
     * @var string|null
     */
    protected $serializedPb;

    /**
     * @var string|null
     */
    protected $topic;

    /**
     * Return a new instance of a protobuf message class.
     *
     * @return Message
     */
    abstract public function newProtobufInstance(): Message;

    /**
     * Check if the message broker is configured.
     *
     * @return bool
     */
    public function enabled(): bool
    {
        $brokers = config('message_broker.brokers.kafka.broker_list');

        return $brokers && $brokers !== "";
    }

    /**
     * Prepare a serialized message to send to the Kafka brokers.
     *
     * @param  Model  $model
     * @return MessageHandler
     * @throws SerializeToProtobufNotImplementedException
     */
    public function prepare(Model $model): MessageHandler
    {
        if (method_exists($model, 'toSerializedPb')) {
            $this->serializedPb = base64_encode($model->toSerializedPb());
        } else {
            throw new SerializeToProtobufNotImplementedException(get_class($model) ?? $model->getTable());
        }

        return $this;
    }

    /**
     * Send the serialized message to the Kafka brokers.
     *
     * @param  string|null  $topic
     * @throws \Exception
     */
    public function send(string $topic = null): void
    {
        if ($this->serializedPb) {
            MessageBroker::send($topic ?? $this->topic, $this->serializedPb);
        }
    }
}
