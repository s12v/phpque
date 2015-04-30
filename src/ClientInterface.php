<?php

namespace S12v\Phpque;

use S12v\Phpque\Dto\Job;

interface ClientInterface {

    /**
     * @param string $queueName
     * @param string $job
     * @param int $msTimeout
     * @param array $options Hash map of optional arguments: { "OPTION1" => "value", "OPTION" }
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function addJob($queueName, $job, $msTimeout, array $options = array());

    /**
     * @param string[] $queueNames
     * @param array $options Hash map of optional arguments: { "OPTION1" => "value", "OPTION" }
     *
     * @return Job|null
     *
     * @throws RuntimeException
     */
    public function getJob(array $queueNames, array $options = array());

    /**
     * @param string[] $queueNames
     * @param array $options Hash map of optional arguments: { "OPTION1" => "value", "OPTION" }
     *
     * @return Job[]
     *
     * @throws RuntimeException
     */
    public function getJobs(array $queueNames, array $options = array());

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
     * @param string $job
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function show($job);

    /**
     * @param string $name
     * @param int $cursor
     * @param array $options Hash map of optional arguments: { "OPTION1" => "value", "OPTION" }
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function scan($name, $cursor, array $options = array());

}
