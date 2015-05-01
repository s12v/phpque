<?php

namespace S12v\Phpque;

use S12v\Phpque\Dto\Job;
use S12v\Phpque\Exception\RuntimeException;

interface ClientInterface {

    /**
     * @return string
     */
    public function getNodeId();

    /**
     * @param string    $queueName
     * @param string    $job
     * @param int       $msTimeout
     * @param array     $options
     *                  Hash map of optional arguments: { "OPTION1" => "value", "OPTION" }
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function addJob($queueName, $job, $msTimeout, array $options = array());

    /**
     * @param string|string[]   $queueNames
     *                          Array or string for single queue
     * @param array             $options
     *                          Hash map of optional arguments: { "OPTION1" => "value", "OPTION" }
     *
     * @return Job|null Null if there's no job available
     *
     * @throws RuntimeException
     */
    public function getJob($queueNames, array $options = array());

    /**
     * @param string|string[]   $queueNames
     *                          Array or string for single queue
     * @param array             $options
     *                          Hash map of optional arguments: { "OPTION1" => "value", "OPTION" }
     *
     * @return Job[] Empty array if there are no jobs available
     *
     * @throws RuntimeException
     */
    public function getJobs($queueNames, array $options = array());

    /**
     * @param string $jobId
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function ackJobById($jobId);

    /**
     * @param string[] $jobIds

     * @return mixed
     *
     * @throws RuntimeException
     */
    public function ackJobsByIds(array $jobIds);

    /**
     * @param Job $job
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function ackJob(Job $job);

    /**
     * @param Job[] $jobs
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function ackJobs(array $jobs);

    /**
     * @param string $jobId
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function fastAckById($jobId);

    /**
     * @param string[] $jobIds
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function fastAckByIds(array $jobIds);

    /**
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function info();

    /**
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function hello();

    /**
     * @param string $queueName
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function qLen($queueName);

    /**
     * @param string $jobId
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function show($jobId);

    /**
     * @param string    $name
     * @param int       $cursor
     * @param array     $options
     *                  Hash map of optional arguments: { "OPTION1" => "value", "OPTION" }
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function scan($name, $cursor, array $options = array());

}
