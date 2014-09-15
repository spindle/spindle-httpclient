<?php
/**
 * spindle/httpclient
 * @license CC0-1.0 (Public Domain) https://creativecommons.org/publicdomain/zero/1.0/
 * @see https://github.com/spindle/spindle-httpclient
 */
namespace Spindle\HttpClient;

class Multi implements \IteratorAggregate, \Countable
{
    protected
        $mh
      , $timeout = 10
      , $pool = array()
    ;

    function __construct() {
        $this->mh = curl_multi_init();
        foreach (func_get_args() as $req) {
            $this->attach($req);
        }
    }

    function __destruct()
    {
        $this->detachAll();
        curl_multi_close($this->mh);
    }

    function setTimeout($num)
    {
        $this->timeout = $num;
        return $this;
    }

    function attach(Request $req)
    {
        $handle = $req->getHandle();
        $this->pool[(int)$handle] = $req;

        curl_multi_add_handle($this->mh, $handle);
    }

    function detach(Request $req)
    {
        $handle = $req->getHandle();
        unset($this->pool[(int)$handle]);

        curl_multi_remove_handle($this->mh, $handle);
    }

    function start()
    {
        $mh = $this->mh;

        do switch (curl_multi_select($mh, 0)) {
            case -1:
                usleep(10);
                do $stat = curl_multi_exec($mh, $running);
                while ($stat === \CURLM_CALL_MULTI_PERFORM);
                continue 2;
            default:
                break 2;
        } while ($running);
    }

    /**
     * イベントが発生するのを待って、何かレスポンスが得られればその配列を返す。
     * これを実行すると、不要になったrequestオブジェクトはdetachします
     *
     * @return Request[]
     */
    function getFinishedResponses()
    {
        $mh = $this->mh;
        $requests = array();

        switch (curl_multi_select($mh, $this->timeout)) {
            case 0:
                throw new \RuntimeException('timeout?');

            case -1: //全リクエストが完了しているケース
            default:
                do $stat = curl_multi_exec($mh, $running);
                while ($stat === \CURLM_CALL_MULTI_PERFORM);

                do if ($raised = curl_multi_info_read($mh, $remains)) {
                    $info = curl_getinfo($raised['handle']);
                    $body = curl_multi_getcontent($raised['handle']);

                    $response = new Response($body, $info);
                    $request = $this->pool[(int)$raised['handle']];

                    $request->setResponse($response);
                    $this->detach($request);

                    if (CURLE_OK !== $raised['result']) {
                        $error = new CurlException(curl_error($raised['handle']), $raised['result']);
                        $request->setError($error);
                    }

                    $requests[] = $request;

                } while ($remains);
        }

        return $requests;
    }

    function waitResponse()
    {
        $mh = $this->mh;

        do switch (curl_multi_select($mh, $this->timeout)) {
            case 0:
                throw new \RuntimeException('timeout.');

            case -1: //全リクエストが完了しているケース
            default:
                do $stat = curl_multi_exec($mh, $running);
                while ($stat === \CURLM_CALL_MULTI_PERFORM);

                do if ($raised = curl_multi_info_read($mh, $remains)) {
                    $info = curl_getinfo($raised['handle']);
                    $body = curl_multi_getcontent($raised['handle']);

                    $response = new Response($body, $info);
                    $request = $this->pool[(int)$raised['handle']];

                    $request->setResponse($response);

                } while ($remains);
        } while ($running);
    }

    function send()
    {
        $this->start();
        $this->waitResponse();
    }

    function detachAll()
    {
        foreach ($this->pool as $request) {
            curl_multi_remove_handle($this->mh, $request->getHandle());
        }

        $this->pool = array();
    }

    /**
     * @override IteratorAggregate::getIterator
     */
    function getIterator()
    {
        return new \ArrayIterator($this->pool);
    }

    /**
     * @override Countable::count
     */
    function count()
    {
        return count($this->pool);
    }
}
