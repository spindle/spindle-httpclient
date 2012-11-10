<?php
namespace Curl;

class Request extends Base
{
    //inherited ... $handle, $processor

    public $responseClass = '\Curl\Response';

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

    function send() {
        $body = curl_exec($this->handle);
        $info = curl_getinfo($this->handle);
        $response = new $this->responseClass($body, $info);

        if (isset($this->processor)) {
            $response = call_user_func($this->processor, $response);
        }

        $this->response = $response;

        return $response;
    }

    function setProcessor($callback) {
        if (! is_callable($callback)) {
            throw new \InvalidArgumentException('is not callable');
        }

        $this->processor = $callback;
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
