<?php
namespace Spindle\HttpClient\Tests;

use Spindle\HttpClient;

/**
 * test for Curl\Response
 * @group response
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    function testConstruct()
    {
        $sampleResponse =<<<_HTTP_
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
\r
0
_HTTP_;
        $sampleInfo = array(
            'url' => 'http://localhost:1337/?wait=0',
            'content_type' => 'text/plain',
            'http_code' => 200,
            'header_size' => 45,
            'download_content_length' => 6,
        );
        $res = new HttpClient\Response($sampleResponse, $sampleInfo);

        self::assertInstanceOf('\Spindle\HttpClient\Response', $res);
        self::assertSame('0', (string)$res);
        self::assertSame('0', $res->getBody());
        $header = array(
            'HTTP/1.1 200 OK',
            'Content-Type: text/plain',
            '', '',
        );

        self::assertSame(implode("\r\n", $header), $res->getHeaderString());

        return $res;
    }

    /**
     * @depends testConstruct
     */
    function testHeaderParser(HttpClient\Response $res)
    {
        self::assertSame('text/plain', $res->getHeader('Content-Type'));
        self::assertSame(array('Content-Type' => 'text/plain'), $res->getHeader());

        //2回目はキャッシュから返すので2度テストする
        self::assertSame('text/plain', $res->getHeader('Content-Type'));

        return $res;
    }

    /**
     * @depends testHeaderParser
     */
    function testInfo(HttpClient\Response $res)
    {
        self::assertSame(200, $res->getStatusCode());
        self::assertSame('text/plain', $res->getContentType());
        self::assertSame('http://localhost:1337/?wait=0', $res->getUrl());
        self::assertSame(6, $res->getContentLength());
        self::assertSame(200, $res->getInfo('http_code'));
        self::assertSame(array(
            'url' => 'http://localhost:1337/?wait=0',
            'content_type' => 'text/plain',
            'http_code' => 200,
            'header_size' => 45,
            'download_content_length' => 6,
        ), $res->getInfo());
    }
}
