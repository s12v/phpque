[![Build Status](https://travis-ci.org/s12v/phpque.svg?branch=master)](https://travis-ci.org/s12v/phpque)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/s12v/phpque/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/s12v/phpque/?branch=master)

# phpque

Lightweight and fast [Disque](https://github.com/antirez/disque) PHP client without external dependencies.
Supports PHP 5.3, 5.4, 5.5, 5.6, and HHVM.

## Installation

```
composer require s12v/phpque --no-dev
```

## Usage

```php
<?php

use S12v\Phpque\Client;

require '../vendor/autoload.php';

$client = new Client(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712']);
$client->addJob('test_queue', 'some data', 1000, array('TTL' => 10000));
$job = $client->getJob(array('test_queue'), array('TIMEOUT' => 100));
$client->ackJob($job);
```

