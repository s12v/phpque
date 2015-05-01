<?php

use S12v\Phpque\Client;

class IntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = new Client(
            array(
                'tcp://127.0.0.1:7711',
                'tcp://127.0.0.1:7712', // this server does not exist
                ),
            1
        );
    }

    /**
     * @expectedException           S12v\Phpque\Connection\DsnException
     * @expectedExceptionMessage    Invalid url "aaa"
     */
    public function testConnectToInvalidDsn()
    {
        $client = new Client('aaa', 1);
        $client->ping();
    }

    /**
     * @expectedException           S12v\Phpque\Connection\ConnectionException
     * @expectedExceptionMessage    No servers available
     */
    public function testConnectToInvalidServer()
    {
        $client = new Client('tcp://127.0.0.1:9999', 1);
        $client->ping();
    }

    public function testNodeId()
    {
        $this->assertTrue(strlen($this->client->getNodeId()) > 0, "Connected");
    }

    public function testPing()
    {
        $response = $this->client->ping();

        $this->assertEquals('PONG', $response);
    }

    public function testNoJob()
    {
        $job = $this->client->getJob("test_queue", array('TIMEOUT' => 10));

        $this->assertNull($job, "Null if no job is found");
    }

    public function testJob()
    {
        $body = array(0 => 'somedata');
        $this->client->addJob("test_queue", serialize($body), 1000);
        $job = $this->client->getJob("test_queue");
        $this->client->ackJob($job);

        $this->assertEquals($body, unserialize($job->getBody()));
    }

    /**
     * @expectedException           S12v\Phpque\Resp\ResponseException
     * @expectedExceptionMessage    BADID Invalid Job ID format.
     */
    public function testInvalidJobIdFormat()
    {
        $this->client->ackJobById("abcdef");
    }

    public function testAckWithInvalidJobId()
    {
        $response = $this->client->ackJobById("DI5123453c3ec3a94698b32c7c19f7332b0ec7ce2405a0SQ");
        $this->assertEquals(0, $response, "Should be 0, I don't know why");
    }

    public function testFastAckWithInvalidJobId()
    {
        $response = $this->client->fastAckById("DI5123453c3ec3a94698b32c7c19f7332b0ec7ce2405a0SQ");
        $this->assertEquals(1, $response, "Should be 1, I don't know why");
    }

    public function testFastAck()
    {
        $this->client->addJob("test_queue", "body", 1000);
        $job1 = $this->client->getJob("test_queue");
        $this->client->fastAckById($job1->getId());

        $job2 = $this->client->getJob("test_queue", array('TIMEOUT' => 10));
        $this->assertNull($job2, "No more jobs");
    }
}
