<?php

namespace S12v\Phpque\Dto;

class Job {
    /**
     * @var string
     */
    protected $queueName;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $payload;

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * @param string $queueName
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param string $payload
     */
    public function setBody($payload)
    {
        $this->payload = $payload;
    }
}
