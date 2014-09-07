<?php
namespace Spindle\HttpClient\Tests;

use Spindle\HttpClient;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    const ORIGIN = 'http://localhost:1337';

    function testSimpleRequest() {
        $req = new HttpClient\Request(self::ORIGIN . '/?wait=0');
        $res = $req->send();
        self::assertInstanceOf('\Spindle\HttpClient\Response', $res);
        self::assertSame('0', $res->getBody());

        $req = new HttpClient\Request;
        $req->setOption('url', self::ORIGIN . '/?wait=0');
        $res = $req->send();
        self::assertInstanceOf('\Spindle\HttpClient\Response', $res);
        self::assertSame('0', $res->getBody());

        $req = new HttpClient\Request;
        $req->setOption(CURLOPT_URL, self::ORIGIN . '/?wait=0');
        $res = $req->send();
        self::assertInstanceOf('\Spindle\HttpClient\Response', $res);
        self::assertSame('0', $res->getBody());
    }

    function testCloneRequest() {
        $req = new HttpClient\Request(self::ORIGIN . '/?wait=0');
        $req2 = clone $req;
        self::assertSame(self::ORIGIN . '/?wait=0', $req2->getOption('url'));
        self::assertSame(array(
            'returnTransfer' => true,
            'header' => true,
            'url' => self::ORIGIN . '/?wait=0',
        ), $req2->getOptions());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetOptErrorString() {
        $req = new HttpClient\Request;
        $req->setOption('hogehoge', 'fugafuga');
    }

    /**
     * @expectedException Spindle\HttpClient\CurlException
     */
    function testInvalidUrl() {
        $req = new HttpClient\Request('uso800.example.com');
        $res = $req->send();
    }

    function testException() {
        $e = new HttpClient\CurlException('error!', 0);
        $e->setRequest($req1 = new HttpClient\Request);
        $req2 = $e->getRequest();
        self::assertSame($req1, $req2);
        self::assertNull($e->getResponse());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetOptErrorFloat() {
        $req = new HttpClient\Request;
        $req->setOption(0.15, 'fugafuga');
    }
}
