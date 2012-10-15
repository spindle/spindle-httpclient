<?php
namespace Curl;

class Response {
    protected
        $_body = ''
      , $_header = ''
      , $_info
      , $_headerCache
      ;

    function __construct($body, array $info) {
        if (is_string($body)) {
            $this->_header = substr($body, 0, $info['header_size']);
            $this->_body = substr($body, $info['header_size']);
        }
        $this->_info = $info;
    }

    function __toString() {
        return $this->getBody();
    }

    function getHeaderString() {
        return $this->_header;
    }

    function getHeader($label = null) {
        if (! $this->_headerCache) {
            $headerString = trim($this->_header);
            $headerString = str_replace(array("\r\n", "\r"), "\n", $headerString);
            $headerArr = explode("\n", $headerString);
            $stat = array_shift($headerArr);

            $result = array();
            foreach ($headerArr as $h) {
                $pos = strpos($h, ':');
                $key = substr($h, 0, $pos);
                $value = substr($h, $pos+1);
                $result[trim($key)] = trim($value);
            }

            $this->_headerCache = $result;
        }

        if ($label) {
            return isset($this->_headerCache[$label]) ? $this->_headerCache[$label] : null;
        } else {
            return $this->_headerCache;
        }
    }

    function getUrl() {
        return $this->_info['url'];
    }

    function getCode() {
        return $this->_info['http_code'];
    }

    function getContentType() {
        return $this->_info['content_type'];
    }

    function getContentLength() {
        return $this->_info['download_content_length'];
    }

    function getBody() {
        return $this->_body;
    }
}
