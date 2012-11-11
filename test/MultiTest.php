<?php
class MultiTest extends PHPUnit_Framework_TestCase
{
    const ORIGIN = 'http://localhost:1337';

    function testXml() {
        $m = new Curl\Multi;

        $req = new Curl\Request(self::ORIGIN . '/simple.xml');
        $req->setProcessor(function($res){
            return simplexml_load_string($res->getBody());
        });
        $m->attach($req);
        $m->detach($req);
        $m->attach($req);

        $m->send();

        $this->assertInstanceOf('SimpleXMLElement', $req->getResponse());
    }

    function testParallel() {
        $m = new Curl\Multi;

        $m->attach(new Curl\Request(self::ORIGIN.'/slow1'));
        $m->attach(new Curl\Request(self::ORIGIN.'/slow1'));
        $m->attach(new Curl\Request(self::ORIGIN.'/slow1'));
        $m->attach(new Curl\Request(self::ORIGIN.'/slow1'));

        $start = microtime(true);
        $m->send();
        $this->assertLessThan(4, microtime(true) - $start);

        foreach ($m as $url => $req) {
            $res = $req->getResponse();
            $this->assertEquals('slow1', $res->getBody());
        }
    }

    /**
     * @expectedException RuntimeException
     */
    function testTimeout() {
        $m = new Curl\Multi(
            new Curl\Request(self::ORIGIN . '/slow1'),
            new Curl\Request(self::ORIGIN . '/slow1')
        );
        $m->setTimeout(1);

        $m->send();
    }
}
