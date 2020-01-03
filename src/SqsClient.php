<?php

namespace snapsuzun\sqs;


use Aws\Sqs\SqsClient as BaseSqsClient;

/**
 * Class SqsClient
 * @package snapsuzun\sqs
 */
class SqsClient extends BaseSqsClient
{
    /**
     * @var null|string
     */
    public $accountId = null;

    /**
     * @var array
     */
    protected $_knownQueueUrls = [];

    /**
     * @var null|array
     */
    protected $_listQueues = null;

    /**
     * AmazonSQS constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->load($config);
        parent::__construct($config);
    }

    /**
     * @param array|null $params
     */
    public function load(array $params)
    {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @param string $queueName
     * @return null|string
     */
    public function getSqsQueueUrl(string $queueName)
    {
        if (empty($this->_knownQueueUrls[$queueName])) {
            $args = [
                'QueueName' => $queueName
            ];
            if ($this->accountId) {
                $args['QueueOwnerAWSAccountId'] = $this->accountId;
            }
            $result = $this->getQueueUrl($args);
            $this->_knownQueueUrls[$queueName] = $result->get('QueueUrl');
        }
        return $this->_knownQueueUrls[$queueName];
    }

    /**
     * @param string $queueName
     * @return bool|null
     */
    public function existsQueue(string $queueName)
    {
        return isset($this->getQueueUrlList()[$queueName]);
    }

    /**
     * @return array|mixed|null
     */
    public function getQueueUrlList()
    {
        if (is_null($this->_listQueues)) {
            $this->_listQueues = $this->listQueues()->get('QueueUrls');
            $buffer = [];
            foreach (array_keys($this->_listQueues) as $key) {
                $split = explode('/', $this->_listQueues[$key]);
                $buffer[array_pop($split)] = $this->_listQueues[$key];
            }
            $this->_listQueues = $buffer;
        }
        return $this->_listQueues;
    }

    /**
     * @param array $args
     * @return \Aws\Result
     */
    public function createQueue(array $args = [])
    {
        $this->_listQueues = null;
        return parent::createQueue($args);
    }

    /**
     * @param array $args
     * @return \Aws\Result
     */
    public function deleteQueue(array $args = [])
    {
        $this->_listQueues = null;
        return parent::deleteQueue($args);
    }
}