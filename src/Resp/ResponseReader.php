<?php

namespace S12v\Phpque\Resp;

class ResponseReader {

    const CRLF = "\r\n";
    const SIMPLE_STRING_MAX_LENGTH = 2147483647;

    public function getResponse($stream)
    {
        $prefix = fgetc($stream);

        if ($prefix === '-') {
            throw new ResponseException($this->getError($stream));
        } elseif ($prefix === '+') {
            return $this->getSimpleString($stream);
        } elseif ($prefix === ':') {
            return $this->getInteger($stream);
        } elseif ($prefix === '$') {
            return $this->getBulkString($stream);
        } elseif ($prefix === '*') {
            return $this->getArray($stream);
        } elseif ($prefix === false) {
            return null;
        } else {
            throw new ResponseException("Unknown prefix: $prefix");
        }
    }

    /**
     * @param resource $stream
     * @return string
     */
    protected function getError($stream)
    {
        return stream_get_line($stream, self::SIMPLE_STRING_MAX_LENGTH, self::CRLF);
    }

    /**
     * @param resource $stream
     * @return string
     * @throws ResponseException
     */
    protected function getSimpleString($stream)
    {
        return stream_get_line($stream, self::SIMPLE_STRING_MAX_LENGTH, self::CRLF);
    }

    /**
     * @param resource $stream
     * @return int
     */
    protected function getInteger($stream)
    {
        return (int)stream_get_line($stream, 64, self::CRLF);
    }

    /**
     * @param resource $stream
     * @return null|string
     * @throws ResponseException
     */
    protected function getBulkString($stream)
    {
        $length = (int)stream_get_line($stream, 64, self::CRLF);
        if ($length > 0) {
            $string = stream_get_contents($stream, $length);
            if ($string === false) {
                throw new ResponseException("Invalid bulk string");
            }
            fseek($stream, 2, SEEK_CUR);
            return $string;
        } elseif ($length == 0) {
            fseek($stream, 2, SEEK_CUR);
            return '';
        } else {
            return null;
        }
    }

    /**
     * @param resource $stream
     * @return array
     */
    protected function getArray($stream)
    {
        $count = (int)stream_get_line($stream, 64, self::CRLF);
        if ($count > 0) {
            $results = array();
            for ($i = 0; $i < $count; $i++) {
                $results[] = $this->getResponse($stream);
            }
            return $results;
        } elseif ($count == 0) {
            return array();
        } else {
            return null;
        }
    }
}
