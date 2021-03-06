<?php

namespace Phpque\Mapper;

use Phpque\Dto\Job;

class JobMapper {

    const FIELD_QUEUE_NAME = 0;
    const FIELD_ID = 1;
    const FIELD_BODY = 2;

    /**
     * @param array|null $response
     *
     * @return null
     */
    public function convertResponseToJob($response)
    {
        if (!is_array($response)) {
            return null;
        }

        $job = new Job();
        $job->setQueueName($response[static::FIELD_QUEUE_NAME]);
        $job->setId($response[static::FIELD_ID]);
        $job->setBody($response[static::FIELD_BODY]);

        return $job;
    }

    /**
     * @param array|null $responses
     *
     * @return Job[]
     */
    public function convertResponsesToJobs($responses)
    {
        if (!is_array($responses)) {
            return array();
        }

        $jobs = array();
        foreach ($responses as $response) {
            if ($response) {
                $jobs[] = $this->convertResponseToJob($response);
            }
        }

        return $jobs;
    }
}
