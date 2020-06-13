<?php 

namespace Friendemic\MessageBroker\Contracts;

interface Broker {
  public function send(string $topicName, string $message, string $key = null):void;
}
