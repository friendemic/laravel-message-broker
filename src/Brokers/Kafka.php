<?php 

namespace Friendemic\MessageBroker\Brokers;

use Friendemic\MessageBroker\Contracts\Broker;
use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\KafkaConsumer;
use Closure;

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
     * RdKafka consumer
     *
     * @var KafkaConsumer
     */
    protected $consumer;

    /**
     * Name of topic subscribed to
     *
     * @var string
     */
    protected $subscription;

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
        
        // set additional config options
        if (! empty($config['config'])) {
            foreach( $config['config'] as $key => $value) {
                $conf->set($key, $value); 
            }
        }

        $this->conf = $conf;
    }

    /**
     * Set producer instance
     *
     * @param Producer $producer
     * @return void
     */
    public function setProducer(?Producer $producer): void
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
     * Set consumer instance
     *
     * @param KafkaConsumer $consumer
     * @return void
     */
    public function setConsumer(KafkaConsumer $consumer): void
    {
        $this->consumer = $consumer;
    }

    /**
     * Get consumer instance
     *
     * @return KafkaConsumer
     */
    public function getConsumer(): ?KafkaConsumer
    {
        return $this->consumer;
    }

    /**
     * Resolves consumer
     * Gets existing or creates new from conf
     *
     * @return KafkaConsumer
     */
    public function consumer(): KafkaConsumer
    {
        $consumer = $this->getConsumer();

        if (is_null($consumer)) {
            $consumer = new KafkaConsumer($this->conf);
            $this->consumer = $consumer;
        }
        

        return $consumer;
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

        $this->setProducer(null);

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            throw new \Exception('librdkafka unable to perform flush, messages might be lost');
        }
    }

    /**
     * Consume next message on topic
     *
     * @param string $topicName
     * @param integer $timeout
     * @param Closure $handler
     * @return void
     */
    public function consumeNext(string $topicName, int $timeout, Closure $handler): void 
    {
        $consumer = $this->consumer();

        // subscribe to topic if not already subscribed
        if ($topicName !== $this->subscription) {
            $consumer->subscribe([$topicName]);
            $this->subscription = $topicName;
        }

        $message = $consumer->consume($timeout);
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                $handler($message->payload);
                // Commit offsets asynchronously
                $consumer->commitAsync($message);
                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                // ?
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                // ?
                break;
            default:
                throw new \Exception($message->errstr(), $message->err);
                break;
        }
    }
}
