<?php

use S12v\Phpque\Resp\ResponseReader;

class DeserializerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var ResponseReader
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new ResponseReader();
    }

    /**
     * @param string $string
     * @return resource
     */
    protected function mockResource($string)
    {
        $stream = fopen('php://memory','r+');
        fwrite($stream, $string);
        rewind($stream);

        return $stream;
    }

    public static function dataTestGetResponse()
    {
        return array(
            'simple' => array(
                'response' => "+HELLO\r\n",
                'expected' => "HELLO",
            ),
            'integer' => array(
                'response' => ":100\r\n",
                'expected' => 100,
            ),
            'zero' => array(
                'response' => ":0\r\n",
                'expected' => 0,
            ),
            'negative' => array(
                'response' => ":-1\r\n",
                'expected' => -1,
            ),
            'null' => array(
                'response' => "$-1\r\n",
                'expected' => null,
            ),
            'bulk string' => array(
                'response' => "$3\r\nfoo\r\n",
                'expected' => "foo",
            ),
            'empty array' => array(
                'response' => "*0\r\n",
                'expected' => array(),
            ),
            'mixed array' => array(
                'response' => "*5\r\n$3\r\nfoo\r\n:100\r\n$-1\r\n+bar\r\n:-1\r\n",
                'expected' => array('foo', 100, null, 'bar', -1),
            ),
            'array inside an array' => array(
                'response' => "*3\r\n$3\r\nfoo\r\n*0\r\n$3\r\nbar\r\n",
                'expected' => array('foo', array(), "bar"),
            ),
        );
    }

    /**
     * @param string $response
     * @param string $expected
     * @dataProvider dataTestGetResponse
     */
    public function testGetResponse($response, $expected)
    {
        $this->assertEquals($expected, $this->deserializer->getResponse($this->mockResource($response)));
    }
}
