<?php
namespace Spindle\HttpClient\Tests;

use Spindle\HttpClient;

class MultiTest extends \PHPUnit_Framework_TestCase
{
    const ORIGIN = 'http://localhost:1337';

    function testParallel() {
        $m = new HttpClient\Multi;

        $m->attach(new HttpClient\Request(self::ORIGIN.'/?wait=1'));
        $m->attach(new HttpClient\Request(self::ORIGIN.'/?wait=1'));
        $m->attach(new HttpClient\Request(self::ORIGIN.'/?wait=1'));
        $m->attach($request = new HttpClient\Request(self::ORIGIN.'/?wait=1'));

        $m->detach($request);

        $start = microtime(true);
        $m->send();
        self::assertLessThan(3, microtime(true) - $start);
        self::assertCount(3, $m);

        foreach ($m as $url => $req) {
            $res = $req->getResponse();
            self::assertEquals('1', $res->getBody());
        }
    }

    /**
     * @expectedException RuntimeException
     */
    function testTimeout() {
        $m = new HttpClient\Multi(
            new HttpClient\Request(self::ORIGIN . '/?wait=4'),
            new HttpClient\Request(self::ORIGIN . '/?wait=2')
        );
        $m->setTimeout(1);

        $m->send();
    }
}
