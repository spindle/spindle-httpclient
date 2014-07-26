<?php
/**
 * spindle/httpclient
 * @license CC0-1.0 (Public Domain) https://creativecommons.org/publicdomain/zero/1.0/
 * @see https://github.com/spindle/spindle-httpclient
 */
namespace Spindle\HttpClient;

class Response {
    protected
        $body = ''
      , $header = ''
      , $info
      , $headerCache
      ;

    function __construct($body, array $info) {
        if (is_string($body)) {
            $this->header = substr($body, 0, $info['header_size']);
            $this->body = substr($body, $info['header_size']);
        }
        $this->info = $info;
    }

    function __toString() {
        return $this->getBody();
    }

    function getHeaderString() {
        return $this->header;
    }

    function getHeader($label = null) {
        if (! $this->headerCache) {
            $headerString = rtrim($this->header);
            $headerString = str_replace(array("\r\n", "\r"), "\n", $headerString);
            $headerArr = explode("\n", $headerString);
            array_shift($headerArr);

            $result = array();
            foreach ($headerArr as $h) {
                $pos = strpos($h, ':');
                $key = substr($h, 0, $pos);
                $value = substr($h, $pos+1);
                $result[trim($key)] = trim($value);
            }

            $this->headerCache = $result;
        }

        if ($label) {
            return isset($this->headerCache[$label]) ? $this->headerCache[$label] : null;
        } else {
            return $this->headerCache;
        }
    }

    function getUrl() {
        return $this->info['url'];
    }

    function getStatusCode() {
        return $this->info['http_code'];
    }

    function getContentType() {
        return $this->info['content_type'];
    }

    function getContentLength() {
        return $this->info['download_content_length'];
    }

    function getInfo($label = null) {
        if ($label && isset($this->info[$label])) {
            return $this->info[$label];
        }
        return $this->info;
    }

    function getBody() {
        return $this->body;
    }
}
