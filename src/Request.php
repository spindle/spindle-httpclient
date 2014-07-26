<?php
/**
 * spindle/httpclient
 * @license CC0-1.0 (Public Domain) https://creativecommons.org/publicdomain/zero/1.0/
 * @see https://github.com/spindle/spindle-httpclient
 */
namespace Spindle\HttpClient;

class Request
{
    private $responseClass = '\Spindle\HttpClient\Response';

    protected $options = array(
        'returnTransfer' => true,
        'header' => true,
    );

    protected $response;

    function __construct($url = null, array $options = array()) {
        if (is_string($url)) {
            $this->handle = curl_init($url);
            $options['url'] = $url;
        } else {
            $this->handle = curl_init();
        }

        $this->options += $options;
        $this->setOptions($this->options);
    }

    function __destruct() {
        curl_close($this->handle);
    }

    function __clone() {
        $this->handle = curl_copy_handle($this->handle);
    }

    function setOptions(array $options) {
        $this->options = $options + $this->options;
        curl_setopt_array($this->handle, $this->_toCurlSetopt($options));
        return $this;
    }

    function setOption($label, $val) {
        $this->options[$label] = $val;
        curl_setopt($this->handle, $this->_toCurlOption($label), $val);
        return $this;
    }

    function getOption($label) {
        return $this->options[$label];
    }

    function getOptions() {
        return $this->options;
    }

    function getHandle()
    {
        return $this->handle;
    }

    function send() {
        $body = curl_exec($this->handle);
        $info = curl_getinfo($this->handle);
        return $this->response = new $this->responseClass($body, $info);
    }

    function setResponse($res) {
        $this->response = $res;
    }

    function getResponse() {
        return $this->response;
    }

    protected function _toCurlSetopt(array $optionList) {
        $fixedOptionList = array();
        foreach ($optionList as $opt => $value) {
            $label = $this->_toCurlOption($opt);
            $fixedOptionList[$label] = $value;
        }
        return $fixedOptionList;
    }

    protected function _toCurlOption($label) {
        if (is_int($label)) {
            return $label;
        }

        if (is_string($label)) {
            $const = 'CURLOPT_' . strtoupper($label);
            if (defined($const)) {
                $curlopt = constant($const);
            } else {
                throw new \InvalidArgumentException("$label does not exist in CURLOPT_* constants.");
            }
            return $curlopt;
        }

        throw new \InvalidArgumentException('label is invalid');
    }
}
