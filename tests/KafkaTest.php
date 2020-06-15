<?php

use Friendemic\MessageBroker\Brokers\Kafka;

class KafkaTest extends TestCase
{
    /** @test */
    function kafkaBroker_producer()
    {
        // set config
        $config = [
            'driver'      => 'kafka',
            'broker_list' => 'some.where.at:port',
            'debug'       => false,
        ];
        $kafka = new Kafka($config);

        // producer should not be set on instantiation
        $this->assertNull($kafka->getProducer());

        // calling ->producer() should instatiate default Producer instance
        $producer = $kafka->producer();
        $this->assertTrue(is_a($producer, \RdKafka\Producer::class));
        $this->assertTrue(is_a($kafka->getProducer(), \RdKafka\Producer::class));

        // test can set producer by passing a mock
        $mock = $this->createMock(\RdKafka\Producer::class);
        $mock->expects($this->once())
             ->method('newTopic')
             ->with($this->equalTo('foo'));
        $kafka->setProducer($mock);
        $kafka->getProducer()->newTopic('foo');
    }

    /** @test */
    function kafkaBroker_sends()
    {
        // set config
        $config = [
            'driver'      => 'kafka',
            'broker_list' => 'some.where.at:port',
            'debug'       => false,
        ];
        $kafka = new Kafka($config);

        // Create a mock for the RdKafka\ProducerTopic class.
        $topicMock = $this->createMock(\RdKafka\ProducerTopic::class);
        $topicMock->expects($this->once())
             ->method('produce')
             ->with(
                $this->equalTo(RD_KAFKA_PARTITION_UA),
                $this->equalTo(0),
                $this->equalTo("test message"),
                $this->equalTo("test-key")
                );

        // Create a mock for the RdKafka\Producer class.
        $producerMock = $this->createMock(\RdKafka\Producer::class);
        $producerMock->expects($this->once())
             ->method('newTopic')
             ->willReturn($topicMock);

        $producerMock->expects($this->once())
            ->method('flush')
            ->willReturn(0);

        $kafka->setProducer($producerMock);
        $kafka->send('test-topic', 'test message', 'test-key');
    }

    /** @test */
    function kafkaBroker_consumer()
    {
        // set config
        $config = [
            'driver'      => 'kafka',
            'broker_list' => 'some.where.at:port',
            'debug'       => false,
            'config'      => [
                'group.id' => 'myConsumerGroup'
            ]
        ];
        $kafka = new Kafka($config);

        // consumer should not be set on instantiation
        $this->assertNull($kafka->getConsumer());

        // calling ->consumer() should instatiate default Producer instance
        $consumer = $kafka->consumer();
        $this->assertTrue(is_a($consumer, \RdKafka\KafkaConsumer::class));
        $this->assertTrue(is_a($kafka->getConsumer(), \RdKafka\KafkaConsumer::class));

        // test can set consumer by passing a mock
        $mock = $this->createMock(\RdKafka\KafkaConsumer::class);
        $mock->expects($this->once())
             ->method('consume')
             ->with($this->equalTo(10000));
        $kafka->setConsumer($mock);
        $kafka->getConsumer()->consume(10000);
    }

    /** @test */
    function kafkaBroker_consumes_next()
    {
        // set config
        $config = [
            'driver'      => 'kafka',
            'broker_list' => 'some.where.at:port',
            'debug'       => false,
            'config'      => [
                'group.id' => 'myConsumerGroup'
            ]
        ];
        $kafka = new Kafka($config);

        // Create a mock for the RdKafka\KafkaConsumer class.
        $consumerMock = $this->createMock(\RdKafka\KafkaConsumer::class);
        // expect topic subscription
        $consumerMock->expects($this->once())
             ->method('subscribe')
             ->with($this->equalTo(['test-topic']))
             ->willReturn(null);

        // mock consume and return message
        $mockMessage = new \RdKafka\Message;
        $mockMessage->payload = 'test message';
        $consumerMock->expects($this->once())
            ->method('consume')
            ->willReturn($mockMessage);

        // expect commit
        $consumerMock->expects($this->once())
            ->method('commitAsync')
            ->with($this->equalTo($mockMessage))
            ->willReturn(null);

        // setup consumer and message handler
        $kafka->setConsumer($consumerMock);
        $called = '';
        $handler = function(string $payload) use (&$called) {
            $called = $payload;
        };
        $kafka->consumeNext('test-topic', 120*1000, $handler);
        $this->assertEquals('test message', $called);

        // call again to make sure subscribe is not called again
        // mock consume and return message
        $mockMessage2 = new \RdKafka\Message;
        $mockMessage2->payload = 'test message2';
        $consumerMock2 = $this->createMock(\RdKafka\KafkaConsumer::class);
        $consumerMock2->expects($this->once())
            ->method('consume')
            ->willReturn($mockMessage2);

        // expect commit
        $consumerMock2->expects($this->once())
            ->method('commitAsync')
            ->with($this->equalTo($mockMessage2))
            ->willReturn(null);
        $kafka->setConsumer($consumerMock2);
        $kafka->consumeNext('test-topic', 120*1000, $handler);
    }
}
