<?php

use Phpque\Resp\Serializer;

class SerializerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Serializer
     */
    private $service;

    public function setUp()
    {
        $this->service = new Serializer();
    }

    public static function dataTestSerialize()
    {
        return array(
            'simple' => array(
                'command' => 'HELLO',
                'arguments' => array(),
                'expected' => "HELLO\r\n",
            ),
            'array' => array(
                'command' => "LLEN",
                'arguments' => array("mylist"),
                'expected' => "*2\r\n$4\r\nLLEN\r\n$6\r\nmylist\r\n",
            ),
            'mixed' => array(
                'command' => "foo",
                'arguments' => array(100, null, "bar"),
                'expected' => "*4\r\n$3\r\nfoo\r\n:100\r\n$-1\r\n$3\r\nbar\r\n",
            ),
            'empty array inside array' => array(
                'command' => "foo",
                'arguments' => array(array(), "bar"),
                'expected' => "*3\r\n$3\r\nfoo\r\n*0\r\n$3\r\nbar\r\n",
            )
        );
    }

    /**
     * @param string $command
     * @param array $arguments
     * @param string $expected
     * @dataProvider dataTestSerialize
     */
    public function testSerialize($command, array $arguments, $expected)
    {
        $this->assertEquals($expected, $this->service->serialize($command, $arguments));
    }
}
