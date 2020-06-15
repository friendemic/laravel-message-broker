# Laravel Message Broker

Produce and consume messages from a Message Broker (Kafka) in Laravel.

## Requirements

* PHP >= 7.2.0
* Laravel >= 5.6
* [RdKafka extension](https://arnaud.le-blanc.net/php-rdkafka-doc/phpdoc/book.rdkafka.html)

## Installation

in composer.json:

```json
"repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/friendemic/laravel-message-broker.git"
    }
],
"require": {
    "friendemic/laravel-message-broker": "dev-master"
},
```

## Configuration

In app.php:

```php
'providers' => [
    \Friendemic\MessageBroker\MessageBrokerProvider::class,
],
'aliases' => [
    'MessageBroker' => Friendemic\MessageBroker\Facades\MessageBroker::class,
]
```

Publish vendor configuration:

```$
php artisan vendor:publish --provider="Friendemic\MessageBroker\MessageBrokerProvider"
```

## Usage

### Send message to producer topic
```php
MessageBroker::send('some-topic', 'message payload', 'message-key');
```

### Consume message 
```php
while (true) {
    MessageBroker::consumeNext('some-topic', 120000, function(string $payload) {
        echo "Received Payload: {$payload}";
    });
}
```
