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

}
