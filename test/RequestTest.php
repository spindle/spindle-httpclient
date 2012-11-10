<?php
class RequestTest extends PHPUnit_Framework_TestCase
{
    const ORIGIN = 'http://localhost:1337';

    function testSimpleRequest() {
        $req = new Curl\Request(self::ORIGIN . '/simple');
        $res = $req->send();
        $this->assertInstanceOf('\Curl\Response', $res);
        $this->assertEquals('simple', $res->getBody());

        $req = new Curl\Request();
        $req->setOption('url', self::ORIGIN . '/simple');
        $res = $req->send();
        $this->assertInstanceOf('\Curl\Response', $res);
        $this->assertEquals('simple', $res->getBody());

        $req = new Curl\Request();
        $req->setOption(CURLOPT_URL, self::ORIGIN . '/simple');
        $res = $req->send();
        $this->assertInstanceOf('\Curl\Response', $res);
        $this->assertEquals('simple', $res->getBody());
    }

    function testCloneRequest() {
        $req = new Curl\Request(self::ORIGIN . '/simple');
        $req2 = clone $req;
        $this->assertEquals(self::ORIGIN . '/simple', $req2->getOption('url'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testProcessor() {
        $req = new Curl\Request(self::ORIGIN . '/simple');
        $req->setProcessor('hoge');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetOptErrorString() {
        $req = new Curl\Request;
        $req->setOption('hogehoge', 'fugafuga');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetOptErrorFloat() {
        $req = new Curl\Request;
        $req->setOption(0.15, 'fugafuga');
    }

    function testXmlRequest() {
        $req = new Curl\Request(self::ORIGIN . '/simple.xml');
        $res = $req->send();
        $this->assertEquals('application/xml', $res->getHeader('Content-Type'));

        $req->setProcessor(static function($res){
            return simplexml_load_string($res->getBody());
        });
        $req->send();

        $res = $req->getResponse();
        $this->assertInstanceOf('SimpleXMLElement', $res);
    }
}
