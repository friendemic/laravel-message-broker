<?php

namespace Friendemic\MessageBroker;

use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class MessageBrokerManager 
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The active broker instances.
     *
     * @var array
     */
    protected $brokers = [];

    /**
     * Create a new message broker manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a message broker broker instance.
     *
     * @param  string  $name
     * @return Broker
     */
    public function broker(string $name = null)
    {
        $name = $name ?: $this->getDefaultBroker();

        // If we haven't created this broker, we'll create it based on the config
        // provided in the application and cache it for later
        if (! isset($this->brokers[$name])) {
            $this->brokers[$name] = $this->makeBroker($name);
        }

        return $this->brokers[$name];
    }

    /**
     * Make broker instance
     *
     * @param string $name
     * @return Broker
     */
    protected function makeBroker(string $name)
    {
        $config = $this->configuration($name);
        if (! isset($config['driver'])) {
            throw new InvalidArgumentException('A driver must be specified.');
        }
        switch ($config['driver']) {
            case 'kafka':
                return $this->createKafkaBroker($config);
        }

        throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]");
    }

    /**
     * Get the configuration for a broker.
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function configuration(string $name): array
    {
        $name = $name ?: $this->getDefaultBroker();

        // To get the message broker configuration, we will just pull each of the
        // broker configurations and get the configurations for the given name.
        // If the configuration doesn't exist, we'll throw an exception and bail.
        $brokers = $this->app['config']['message_broker.brokers'];

        if (is_null($config = Arr::get($brokers, $name))) {
            throw new InvalidArgumentException("Message broker [{$name}] not configured.");
        }

        return $config;
    }
  
    /**
     * Get the default broker name.
     *
     * @return string
     */
    public function getDefaultBroker(): string
    {
        return $this->app['config']['message_broker.default'];
    }

    /**
     * Kafka broker creator
     *
     * @return Brokers\Kafka
     */
    public function createKafkaBroker(array $config)
    {
        return new Brokers\Kafka($config);
    }

    /**
     * Return all of the created brokers.
     *
     * @return array
     */
    public function getBrokers()
    {
        return $this->brokers;
    }

    /**
     * Dynamically pass methods to the default broker.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->broker()->$method(...$parameters);
    }
}
