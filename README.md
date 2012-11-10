cURL Wrapper for pecl-curl
==========================

curl_*関数をモダンなPHPらしく書けるようにした薄いラッパークラスです。
curl_multi_*に対応しており、並列リクエストが可能です。

```php
$request = new Curl\Request('http://example.com/api', array(
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

## Curl\Request
curl_init()のWrapperです。

### __construct([ $url, [ array $options ] ])
### __clone()
Curl\Requestはclone可能です。cloneした場合、オプションなどがすべてコピーされます。

```php
$req1 = new Curl\Request('http://example.com/');
$req2 = clone $req1;
```

### void setOption($label, $value)
curl_setopt()のラッパーです。
デフォルトでCURLOPT_RETURNTFANSFERとCURLOPT_HEADERはtrueに設定されているため、改めてセットする必要はありません。
CURLOPT_定数は、文字列でも書くことができます。

```php
$req = new Curl\Request();

//equals
$req->setOption(CURLOPT_POST, true);
$req->setOption('post', true);

//equals
$req->setOption(CURLOPT_POSTFIELDS, 'a=b');
$req->setOption('postFields', 'a=b');
```

文字列がラベルに指定された場合、全て大文字にして、CURLOPT_をくっつけてから該当する定数を探します。

### void setOptions(array $options)
curl_setopt_array()のラッパーです。setOption()と同じく、文字列ラベルが使えます。

```php
$req = new Curl\Request();
$req->setOptions(array(
  'post' => true,
  'postFields' => 'a=b',
));
```

### Curl\Response send()
リクエストを送信し、レスポンスが返るまで待ちます。

### Curl\Response getResponse()
最後に取得したレスポンスを返します。


## Curl\Response
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
curl_getinfo()のラッパーです。

### string getHeaderString()
レスポンスヘッダーの文字列を返します。

### mixed getHeader(string $headerName)
$headerNameに対応するレスポンスヘッダーの中身を返します。
$headerNameを省略すると、レスポンスヘッダーを連想配列形式で返します。

### string getBody()
レスポンスボディの文字列を返します。


## Curl\Multi
curl_multi_*のWrapperです。並列リクエストを行うことができます。

```php
$pool = new Curl\Multi(
    new Curl\Request('http://example.com/api'),
    new Curl\Request('http://example.com/api2')
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
$pool = new Curl\Multi;
$req1 = new Curl\Request('http://example.com/api1');
$req2 = new Curl\Request('http://example.com/api2');

$pool->attach($req1);
$pool->attach($req2);

$pool->detach($req1);

$pool->send();
```
