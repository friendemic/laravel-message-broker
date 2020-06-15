<?php 

namespace Friendemic\MessageBroker\Contracts;

use Closure;

interface Broker {
  public function send(string $topicName, string $message, string $key = null): void;
  public function consumeNext(string $topicName, int $timeout, Closure $handler): void;
}
