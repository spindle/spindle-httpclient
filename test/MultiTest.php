<?php
class MultiTest extends PHPUnit_Framework_TestCase
{
    const ORIGIN = 'http://localhost:1337';

    function testMulti() {
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
}
