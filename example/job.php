<?php

namespace S12V\Example;

use S12v\Phpque\Client;

require '../vendor/autoload.php';

$client = new Client('tcp://127.0.0.1:7711', 1);
$client->addJob('test_queue', 'some data', 1000, array('TTL' => 10000));
$job = $client->getJob(array('test_queue'), array('TIMEOUT' => 100));
$client->ackJob($job);

print_r($job);
