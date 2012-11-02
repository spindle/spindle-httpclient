<?php
class RequestTest extends PHPUnit_Framework_TestCase
{
    const ORIGIN = 'http://localhost:1337';

    function testSimpleRequest() {
        $req = new Curl\Request(self::ORIGIN . '/simple');
        $res = $req->send();
        $this->assertInstanceOf('\Curl\Response', $res);
        $this->assertEquals('simple', $res->getBody());
    }
}
