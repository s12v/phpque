<?php

namespace S12v\Phpque;

use S12v\Phpque\Connection\ConnectionException;
use S12v\Phpque\Connection\Connector;
use S12v\Phpque\Connection\DsnException;
use S12v\Phpque\Connection\TransportException;
use S12v\Phpque\Dto\Job;
use S12v\Phpque\Exception\InvalidArgumentException;
use S12v\Phpque\Mapper\JobMapper;
use S12v\Phpque\Resp\ResponseException;
use S12v\Phpque\Resp\ResponseReader;
use S12v\Phpque\Resp\Serializer;

class Client implements ClientInterface
{

    /**
     * @var array
     */
    protected $dsns;

    /**
     * @var resource
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
     * @param array $dsns Data source names
     * @param int $timeout
     */
    public function __construct(array $dsns, $timeout = null)
    {
        $this->dsns = $dsns;
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
                $this->stream = $this->connector->connect($dsns[$index]);
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
     * @return string
     */
    public function getNodeId()
    {
        if (!$this->isConnected) {
            $this->connect();
        }

        return $this->nodeId;
    }

    public function addJob($queueName, $job, $msTimeout, array $options = array())
    {
        $arguments = array($queueName, $job, (string)$msTimeout);
        if ($options) {
            $arguments = array_merge($arguments, $this->flatten($options));
        }

        return $this->send('ADDJOB', $arguments);
    }

    public function getJob(array $queueNames, array $options = array())
    {
        if (isset($options['COUNT']) && $options['COUNT'] > 1) {
            throw new InvalidArgumentException("COUNT is greater then 1, use getJobs() instead");
        }

        $jobs = $this->getJobs($queueNames, $options);
        return isset($jobs[0]) ? $jobs[0] : null;
    }

    public function getJobs(array $queueNames, array $options = array())
    {
        $arguments = array();
        if ($options) {
            $arguments = array_merge($arguments, $this->flatten($options));
        }
        $arguments[] = 'FROM';
        $arguments = array_merge($arguments, $queueNames);

        $responses = $this->send('GETJOB', $arguments);
        return $this->jobMapper->convertResponsesToJobs($responses);
    }

    public function ackJobById($jobId)
    {
        return $this->ackJobsByIds(array($jobId));
    }

    public function ackJobsByIds(array $jobIds)
    {
        return $this->send('ACKJOB', $jobIds);
    }

    public function ackJob(Job $job)
    {
        return $this->ackJobById($job->getId());
    }

    public function ackJobs(array $jobs)
    {
        $jobIds = array_map(function(Job $job) {
            return $job->getId();
        }, $jobs);

        return $this->ackJobsByIds($jobIds);
    }

    /**
     * @param string $jobId
     * @return mixed
     * @throws RuntimeException
     */
    public function fastAckById($jobId)
    {
        $this->fastAckByIds(array($jobId));
    }

    /**
     * @param array $jobIds
     * @return mixed
     * @throws RuntimeException
     */
    public function fastAckByIds(array $jobIds)
    {
        return $this->send('FASTACK', $jobIds);
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function info()
    {
        return $this->send('INFO');
    }

    /**
     * @return array
     * @throws RuntimeException
     */
    public function hello()
    {
        return $this->send('HELLO');
    }

    /**
     * @param $queueName
     * @return int
     * @throws RuntimeException
     */
    public function qLen($queueName)
    {
        return $this->send('QLEN', array($queueName));
    }

    /**
     * @param $job
     * @return mixed
     * @throws RuntimeException
     */
    public function show($job)
    {
        return $this->send('SHOW');
    }

    /**
     * @param string $name
     * @param int $cursor
     * @param array $options
     * @return mixed
     * @throws RuntimeException
     */
    public function scan($name, $cursor, array $options = array())
    {
        $arguments = array($name, (string)$cursor);
        if ($options) {
            $arguments = array_merge($arguments, $this->flatten($options));
        }

        return $this->send('SCAN', $arguments);
    }
}
