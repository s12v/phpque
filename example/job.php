<?php

use S12v\Phpque\Client;
use S12v\Phpque\Connection\ConnectionException;

require '../vendor/autoload.php';

try {
    // Connect to a server pool
    $client = new Client(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712']);

    // ... or to a single server
    $client = new Client('tcp://127.0.0.1:7711');
} catch (ConnectionException $e) {
    // Handle connection errors
    throw $e;
}

// Add a job with payload "some data" and timeout 1 sec
$client->addJob('test_queue', 'some data', 1000);

// Get a job from the queue
$job = $client->getJob(array('test_queue'));

// Acknowledge the job
$client->ackJob($job);
