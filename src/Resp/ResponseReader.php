<?php

namespace S12v\Phpque\Resp;

class ResponseReader {

    const CRLF = "\r\n";

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
        return rtrim(fgets($stream), static::CRLF);
    }

    /**
     * @param resource $stream
     * @return string
     * @throws ResponseException
     */
    protected function getSimpleString($stream)
    {
        return rtrim(fgets($stream), static::CRLF);
    }

    /**
     * @param resource $stream
     * @return int
     */
    protected function getInteger($stream)
    {
        return (int)fgets($stream, 64);
    }

    /**
     * @param resource $stream
     * @return null|string
     * @throws ResponseException
     */
    protected function getBulkString($stream)
    {
        $length = (int)fgets($stream, 64);
        if ($length > 0) {
            $string = fread($stream, $length);
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
        $count = (int)fgets($stream, 64);
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
