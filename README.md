cURL Wrapper for pecl-curl
==========================

curl_*関数をモダンなPHPらしく書けるようにした薄いラッパークラスです。

```php
$request = new Curl\Request('http://example.com/api');
$request->setOptions(array(
  'post' => true,
  'postFields' => http_build_query(array(
    'param' => 'value',
  )),
));

$response = $request->send();

$header = $response->getHeaderString();
$body = $response->getBody();
```

```php
<?php
//もともと
$ch = curl_init('http://example.com/api');
curl_setopt_array($ch, array(
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HEADER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query(array(
    'param' => 'value',
  )),
));

$response = curl_exec($ch);
```
