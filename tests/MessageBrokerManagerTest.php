<?php

use Friendemic\MessageBroker\MessageBrokerManager;
use Friendemic\MessageBroker\Brokers\Kafka;

class MessageBrokerManagerTest extends TestCase
{

    /** @test */
    function messageBrokerManager_registers()
    {
        // get manager
        $manager = app('message_broker');
        // expect brokers to be empty initially
        $this->assertCount(0, $manager->getBrokers());

        // assert can create kafka broker (from default)
        $kafka = $manager->broker();
        $this->assertTrue(is_a($kafka, Kafka::class));
    }

}
