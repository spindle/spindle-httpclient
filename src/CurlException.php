<?php
/**
 * spindle/httpclient
 * @license CC0-1.0 (Public Domain) https://creativecommons.org/publicdomain/zero/1.0/
 * @see https://github.com/spindle/spindle-httpclient
 */
namespace Spindle\HttpClient;

class CurlException extends \RuntimeException
{
    private $req;

    function setRequest(Request $req)
    {
        $this->req = $req;
    }

    function getRequest()
    {
        return $this->req;
    }

    function getResponse()
    {
        return $this->req->getResponse();
    }
}
