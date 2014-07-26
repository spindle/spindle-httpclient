spindle\httpclient
==========================

[![Build Status](https://travis-ci.org/spindle/spindle-httpclient.svg?branch=master)](https://travis-ci.org/spindle/spindle-httpclient)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spindle/spindle-httpclient/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/spindle/spindle-httpclient/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/spindle/spindle-httpclient/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/spindle/spindle-httpclient/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/spindle/httpclient/v/stable.svg)](https://packagist.org/packages/spindle/httpclient)
[![Total Downloads](https://poser.pugx.org/spindle/httpclient/downloads.svg)](https://packagist.org/packages/spindle/httpclient)
[![Latest Unstable Version](https://poser.pugx.org/spindle/httpclient/v/unstable.svg)](https://packagist.org/packages/spindle/httpclient) 
[![License](https://poser.pugx.org/spindle/httpclient/license.svg)](https://packagist.org/packages/spindle/httpclient)

curl\_\*関数をモダンなPHPらしく書けるようにした薄いラッパークラスです。
curl\_multi\_\*に対応しており、並列リクエストが可能です。

```php
$request = new Spindle\HttpClient\Request('http://example.com/api', array(
  'post' => true,
  'postFields' => http_build_query(array(
    'param' => 'value',
  )),
));

$response = $request->send();

$statusCode = $response->getStatusCode();
$header     = $response->getHeaderString();
$body       = $response->getBody();
$body       = (string)$response;
```

```php
<?php
//libcurl original
$ch = curl_init('http://example.com/api');
curl_setopt_array($ch, array(
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HEADER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query(array(
    'param' => 'value',
  )),
));

$response_fulltext = curl_exec($ch);
curl_close($ch);
```

## Spindle\HttpClient\Request
curl\_init()のWrapperです。

### \_\_construct([ $url, [ array $options ] ])
### \_\_clone()
Spindle\HttpClient\Requestはclone可能です。cloneした場合、オプションなどがすべてコピーされます。

```php
$req1 = new Spindle\HttpClient\Request('http://example.com/');
$req2 = clone $req1;
```

### void setOption($label, $value)
`curl_setopt()`のラッパーです。
デフォルトで`CURLOPT_RETURNTFANSFER`と`CURLOPT_HEADER`はtrueに設定されているため、改めてセットする必要はありません。
`CURLOPT_`定数は、文字列でも書くことができます。

```php
$req = new Spindle\HttpClient\Request;

//equals
$req->setOption(CURLOPT_POST, true);
$req->setOption('post', true);

//equals
$req->setOption(CURLOPT_POSTFIELDS, 'a=b');
$req->setOption('postFields', 'a=b');
```

文字列がラベルに指定された場合、全て大文字にして、`CURLOPT_`をくっつけてから該当する定数を探します。

### void setOptions(array $options)
`curl_setopt_array()`のラッパーです。setOption()と同じく、文字列ラベルが使えます。

```php
$req = new Spindle\HttpClient\Request();
$req->setOptions(array(
  'post' => true,
  'postFields' => 'a=b',
));
```

### Spindle\HttpClient\Response send()
リクエストを送信し、レスポンスが返るまで待ちます。

### Spindle\HttpClient\Response getResponse()
最後に取得したレスポンスを返します。


## Spindle\HttpClient\Response
レスポンスのWrapperです。

### int getStatusCode()
HTTPのステータスコードを返します。

### string getUrl()
リクエストに使われたURLを返します。

### string getContentType()
レスポンスのContent-Typeを返します。

### string getContentLength()
レスポンスのContent-Lengthを返します。

### mixed getInfo(string $label)
`curl_getinfo()`のラッパーです。

### string getHeaderString()
レスポンスヘッダーの文字列を返します。

### mixed getHeader(string $headerName = null)
$headerNameに対応するレスポンスヘッダーの中身を返します。
$headerNameを省略すると、レスポンスヘッダーを連想配列形式で返します。

### string getBody()
レスポンスボディの文字列を返します。


## Spindle\HttpClient\Multi
`curl_multi_*`のWrapperです。並列リクエストを行うことができます。

```php
use Spindle\HttpClient;

$pool = new HttpClient\Multi(
    new HttpClient\Request('http://example.com/api'),
    new HttpClient\Request('http://example.com/api2')
);
$pool->setTimeout(10);

$pool->send(); //wait for all response

foreach ($pool as $url => $req) {
    $res = $req->getResponse();
    echo $url, PHP_EOL;
    echo $res->getStatusCode(), PHP_EOL
    echo $res->getBody(), PHP_EOL;
}
```

```php
use Spindle\HttpClient;

$pool = new HttpClient\Multi;
$req1 = new HttpClient\Request('http://example.com/api1');
$req2 = new HttpClient\Request('http://example.com/api2');

$pool->attach($req1);
$pool->attach($req2);

$pool->detach($req1);

$pool->send();
```

`send()`は全てのリクエストを送り、全てのレスポンスが戻ってくるのを待ちますが、これを`sendStart()`と`waitResponse()`の二つに分けて書くと、待っている間に他のコードを実行できます。

```php
use Spindle\HttpClient;

$pool = new HttpClient\Multi(
    new HttpClient\Request('http://example.com/api'),
    new HttpClient\Request('http://example.com/api2')
);

$pool->sendStart();

for ($i=0; $i<10000; $i++) {
    very_very_heavy_function();
}

$pool->waitResponse();

foreach ($pool as $url => $req) {
    $res = $req->getResponse();
    echo $url, PHP_EOL;
    echo $res->getStatusCode(), PHP_EOL
    echo $res->getBody(), PHP_EOL;
}
```

License
------------

spindle/httpclientの著作権は放棄するものとします。
利用に際して制限はありませんし、作者への連絡や著作権表示なども必要ありません。
スニペット的にコードをコピーして使っても問題ありません。

[ライセンスの原文](LICENSE)

CC0-1.0 (No Rights Reserved)
- https://creativecommons.org/publicdomain/zero/1.0/
- http://sciencecommons.jp/cc0/about (Japanese)

