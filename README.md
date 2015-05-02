[![Build Status](https://travis-ci.org/s12v/phpque.svg?branch=master)](https://travis-ci.org/s12v/phpque)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/s12v/phpque/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/s12v/phpque/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/s12v/phpque/v/stable)](https://packagist.org/packages/s12v/phpque)

# phpque

Lightweight and fast [Disque](https://github.com/antirez/disque) PHP client without external dependencies.
Supports PHP 5.3, 5.4, 5.5, 5.6, and HHVM.

## Installation

```
composer require s12v/phpque
```

## Usage

```php
<?php

use Phpque\Client;
use Phpque\Connection\ConnectionException;

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
```

## API

Refer to the [Disque documentation](https://github.com/antirez/disque#api) and [ClientInterface](https://github.com/s12v/phpque/blob/master/src/ClientInterface.php)

## Development

Run tests:
```
./vendor/bin/phpunit
```
