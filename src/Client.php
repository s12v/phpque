<?php

namespace Phpque;

use Phpque\Connection\ConnectionException;
use Phpque\Connection\Connector;
use Phpque\Connection\DsnException;
use Phpque\Connection\TransportException;
use Phpque\Dto\Job;
use Phpque\Exception\InvalidArgumentException;
use Phpque\Exception\RuntimeException;
use Phpque\Mapper\JobMapper;
use Phpque\Resp\ResponseException;
use Phpque\Resp\ResponseReader;
use Phpque\Resp\Serializer;

class Client implements ClientInterface
{

    /**
     * @var array
     */
    protected $dsns;

    /**
     * @var resource|bool
     */
    protected $stream;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var bool
     */
    protected $isConnected;

    /**
     * @var string
     */
    protected $nodeId;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var ResponseReader
     */
    protected $responseReader;

    /**
     * @var JobMapper
     */
    protected $jobMapper;

    /**
     * @param string|array $dsns Data source name(s)
     * @param float $timeout In seconds
     */
    public function __construct($dsns, $timeout = null)
    {
        $this->dsns = (array)$dsns;
        $this->connector = new Connector($timeout);
        $this->serializer = new Serializer();
        $this->responseReader = new ResponseReader();
        $this->jobMapper = new JobMapper();
    }

    /**
     * @throws ConnectionException
     */
    protected function connect()
    {
        $dsns = $this->dsns;
        do {
            $this->isConnected = false;
            if (empty($dsns)) {
                throw new ConnectionException("No servers available");
            }

            $index = array_rand($dsns);
            try {
                if ($this->connectToNode($dsns[$index])) {
                    break;
                }
            } catch (RuntimeException $e) {
                trigger_error($e->getMessage());
            }
            unset($dsns[$index]);
        } while (true);
    }

    /**
     * @param $dsn
     * @return bool
     * @throws DsnException
     * @throws ResponseException
     * @throws TransportException
     */
    protected function connectToNode($dsn)
    {
        $this->nodeId = null;
        $this->stream = $this->connector->connect($dsn);
        if ($this->stream) {
            $this->isConnected = true;
            $response = $this->hello();
            if (!empty($response[1])) {
                $this->nodeId = $response[1];
            } else {
                throw new ConnectionException("Invalid HELLO response");
            }
        }

        return (bool)$this->nodeId;
    }

    /**
     * @param $command
     * @param array $arguments
     * @return mixed
     * @throws ConnectionException
     * @throws ResponseException
     * @throws TransportException
     */
    protected function send($command, $arguments = array())
    {
        if (!$this->isConnected) {
            $this->connect();
        }
        $request = $this->serializer->serialize($command, $arguments);
        $result = fwrite($this->stream, $request);
        if ($result === false) {
            throw new TransportException("Unable to send command to node $this->nodeId");
        }

        $response = $this->responseReader->getResponse($this->stream);

        return $response;
    }

    /**
     * @param array $hash
     * @return string[]
     */
    protected function flatten(array $hash)
    {
        $result = array();
        foreach ($hash as $key => $value) {
            if (!is_int($key)) {
                $result[] = (string)$key;
            }
            $result[] = (string)$value;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeId()
    {
        if (!$this->isConnected) {
            $this->connect();
        }

        return $this->nodeId;
    }

    /**
     * {@inheritdoc}
     */
    public function addJob($queueName, $job, $msTimeout, array $options = array())
    {
        $arguments = array($queueName, $job, (string)$msTimeout);
        if (!empty($options)) {
            $arguments = array_merge($arguments, $this->flatten($options));
        }

        return $this->send('ADDJOB', $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getJob($queueNames, array $options = array())
    {
        if (isset($options['COUNT']) && $options['COUNT'] > 1) {
            throw new InvalidArgumentException("COUNT is greater then 1, use getJobs() instead");
        }

        $jobs = $this->getJobs($queueNames, $options);
        return isset($jobs[0]) ? $jobs[0] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobs($queueNames, array $options = array())
    {
        $queueNames = (array)$queueNames;
        $arguments = array();
        if (!empty($options)) {
            $arguments = array_merge($arguments, $this->flatten($options));
        }
        $arguments[] = 'FROM';
        $arguments = array_merge($arguments, $queueNames);

        $responses = $this->send('GETJOB', $arguments);
        return $this->jobMapper->convertResponsesToJobs($responses);
    }

    /**
     * {@inheritdoc}
     */
    public function ackJobById($jobId)
    {
        return $this->ackJobsByIds(array($jobId));
    }

    /**
     * {@inheritdoc}
     */
    public function ackJobsByIds(array $jobIds)
    {
        return $this->send('ACKJOB', $jobIds);
    }

    /**
     * {@inheritdoc}
     */
    public function ackJob(Job $job)
    {
        return $this->ackJobById($job->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function ackJobs(array $jobs)
    {
        $jobIds = array_map(function(Job $job) {
            return $job->getId();
        }, $jobs);

        return $this->ackJobsByIds($jobIds);
    }

    /**
     * {@inheritdoc}
     */
    public function fastAckById($jobId)
    {
        return $this->fastAckByIds(array($jobId));
    }

    /**
     * {@inheritdoc}
     */
    public function fastAckByIds(array $jobIds)
    {
        return $this->send('FASTACK', $jobIds);
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return $this->send('INFO');
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        return $this->send('PING');
    }

    /**
     * {@inheritdoc}
     */
    public function hello()
    {
        return $this->send('HELLO');
    }

    /**
     * {@inheritdoc}
     */
    public function qLen($queueName)
    {
        return $this->send('QLEN', array($queueName));
    }

    /**
     * {@inheritdoc}
     */
    public function show($jobId)
    {
        return $this->send('SHOW');
    }

    /**
     * {@inheritdoc}
     */
    public function scan($name, $cursor, array $options = array())
    {
        $arguments = array($name, (string)$cursor);
        if (!empty($options)) {
            $arguments = array_merge($arguments, $this->flatten($options));
        }

        return $this->send('SCAN', $arguments);
    }
}
