<?php

namespace S12v\Phpque\Resp;

class Serializer {

    const CRLF = "\r\n";

    /**
     * @param string $command
     * @param array $arguments
     * @return string
     */
    public function serialize($command, $arguments = array())
    {
        if (empty($arguments)) {
            return $this->getSimpleString($command);
        } else {
            array_unshift($arguments, $command);
            return $this->getArray($arguments);
        }
    }

    /**
     * @param array $elements
     * @return string
     */
    protected function getArray(array $elements)
    {
        $command = '*' . count($elements) . self::CRLF;
        foreach ($elements as $elem) {
            if (is_int($elem)) {
                $command .= $this->getInteger($elem);
            } elseif (is_string($elem)) {
                $command .= $this->getBulkString($elem);
            } elseif (is_null($elem)) {
                $command .= $this->getNull();
            } elseif (is_array($elem)) {
                $command .= $this->getArray($elem);
            }
        }

        return $command;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function getSimpleString($value)
    {
        return $value . self::CRLF;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function getBulkString($value)
    {
        return "$" . strlen($value) . self::CRLF . $value . self::CRLF;
    }

    /**
     * @param int $value
     * @return string
     */
    protected function getInteger($value)
    {
        return ":" . $value . self::CRLF;
    }

    /**
     * @return string
     */
    protected function getNull()
    {
        return "$-1" . self::CRLF;
    }
}

