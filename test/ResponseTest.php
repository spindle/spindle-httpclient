<?php
/**
 * test for Curl\Response
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{
    function testConstruct()
    {
        $sampleResponse =<<<'_HTTP_'
HTTP/1.1 200 OK
Content-Type: text/plain

simple
_HTTP_;
        $sampleInfo = array(
            'url' => 'http://localhost:1337/simple',
            'content_type' => 'text/plain',
            'http_code' => 200,
            'header_size' => 42,
            'download_content_length' => 6,
        );
        $res = new Curl\Response($sampleResponse, $sampleInfo);

        $this->assertInstanceOf('\Curl\Response', $res);
        $this->assertEquals('simple', (string)$res);
        $this->assertEquals('simple', $res->getBody());
        $header = array(
            'HTTP/1.1 200 OK',
            'Content-Type: text/plain',
            '', '',
        );

        $this->assertEquals(implode("\n", $header), $res->getHeaderString());

        return $res;
    }

    /**
     * @depends testConstruct
     */
    function testHeaderParser(Curl\Response $res)
    {
        $this->assertEquals('text/plain', $res->getHeader('Content-Type'));
        $this->assertSame(array('Content-Type' => 'text/plain'), $res->getHeader());

        //2回目はキャッシュから返すので2度テストする
        $this->assertEquals('text/plain', $res->getHeader('Content-Type'));

        return $res;
    }

    /**
     * @depends testHeaderParser
     */
    function testInfo(Curl\Response $res)
    {
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('text/plain', $res->getContentType());
        $this->assertEquals('http://localhost:1337/simple', $res->getUrl());
        $this->assertEquals(6, $res->getContentLength());
        $this->assertEquals(200, $res->getInfo('http_code'));
        $this->assertSame(array(
            'url' => 'http://localhost:1337/simple',
            'content_type' => 'text/plain',
            'http_code' => 200,
            'header_size' => 42,
            'download_content_length' => 6,
        ), $res->getInfo());
    }
}
