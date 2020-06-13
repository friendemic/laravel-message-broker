<?php 

namespace Friendemic\MessageBroker\Brokers;

use Friendemic\MessageBroker\Contracts\Broker;
use RdKafka\Conf;
use RdKafka\Producer;

class Kafka implements Broker 
{
    /**
     * Producer flush timeout in milliseconds
     */
    const FLUSH_TIMEOUT = 10000;
    
    /**
     * RdKakfa configuration
     *
     * @var Confg
     */
    protected $conf;

    /**
     * RdKafka producer
     *
     * @var Producer
     */
    protected $producer;

    /**
     * Kafka broker constructor
     * 
     * @param array $config
     */
    public function __construct(array $config) 
    {
        $conf = new Conf();
      
        $conf->set('metadata.broker.list', $config['broker_list']);

        if ($config['debug']) {
            $conf->set('log_level', LOG_DEBUG);
            $conf->set('debug', 'all');
        }
        $this->conf = $conf;
    }

    /**
     * Set producer instance
     *
     * @param Producer $producer
     * @return void
     */
    public function setProducer(Producer $producer): void
    {
        $this->producer = $producer;
    }

    /**
     * Get producer instance
     *
     * @return Producer
     */
    public function getProducer(): ?Producer
    {
        return $this->producer;
    }

    /**
     * Resolves producer
     * Gets existin or creates new from conf
     *
     * @return Producer
     */
    public function producer(): Producer
    {
        $producer = $this->getProducer();

        if (is_null($producer)) {
            $producer = new Producer($this->conf);
            $this->producer = $producer;
        }
        

        return $producer;
    }

    /**
     * Produce and send message to broker
     *
     * @param string $topicName
     * @param string $message
     * @param string $key
     * @return void
     */
    public function send(string $topicName, string $message, string $key = null): void 
    {
        $producer = $this->producer();

        $topic = $producer->newTopic($topicName);
        
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message, $key);

        $result = $producer->flush(self::FLUSH_TIMEOUT);

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            throw new \Exception('librdkafka unable to perform flush, messages might be lost');
        }
    }
}
