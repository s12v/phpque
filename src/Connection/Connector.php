<?php

namespace S12v\Phpque\Connection;

class Connector {

    /**
     * @var float
     */
    protected $timeout;

    /**
     * @param float $timeout In seconds
     */
    public function __construct($timeout)
    {
        $this->timeout = $timeout ?: (float)ini_get("default_socket_timeout");
    }

    /**
     * @param string $dsn
     * @return resource|bool
     * @throws DsnException
     */
    public function connect($dsn)
    {
        $node = parse_url($dsn);
        if (!isset($node['host']) || !isset($node['port'])) {
            throw new DsnException("Invalid url \"$dsn\"");
        }

        return @fsockopen($node['host'], $node['port'], $errno, $errstr, $this->timeout);
    }

}
