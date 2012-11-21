<?php
namespace Curl;

class Multi extends Base implements \IteratorAggregate
{
    protected
        $mh
      , $timeout = 10
      , $pool = array()
      , $_valid //for iterator
    ;

    function __construct() {
        $this->mh = curl_multi_init();
        foreach (func_get_args() as $req) {
            $this->attach($req);
        }
    }

    function __destruct() {
        $this->detachAll();
        curl_multi_close($this->mh);
    }

    function setTimeout($num) {
        $this->timeout = $num;
        return $this;
    }

    function attach(Request $req) {
        $this->pool[(int)$req->handle] = $req;

        curl_multi_add_handle($this->mh, $req->handle);
    }

    function detach(Request $req) {
        unset($this->pool[(int)$req->handle]);

        curl_multi_remove_handle($this->mh, $req->handle);
    }

    function sendStart() {
        $mh = $this->mh;

        //for libcurl < 7.20
        do {
            $stat = curl_multi_exec($mh, $running);
        } while ($stat === CURLM_CALL_MULTI_PERFORM);

        if (! $running || $stat !== CURLM_OK) {
            throw new \RuntimeException('request cannot start');
        }

        return $this;
    }

    function waitResponse() {
        $mh = $this->mh;

        do switch (curl_multi_select($mh, $this->timeout)) {
            case -1:
                throw new \RuntimeException('select failed.');
            case 0:
                throw new \RuntimeException('timeout.');

            default:
                //for libcurl < 7.20
                do {
                    $stat = curl_multi_exec($mh, $running);
                } while ($stat === CURLM_CALL_MULTI_PERFORM);

                do if ($raised = curl_multi_info_read($mh, $remains)) {
                    $info = curl_getinfo($raised['handle']);
                    $body = curl_multi_getcontent($raised['handle']);

                    $response = new Response($body, $info);
                    $request = $this->pool[(int)$raised['handle']];

                    if (isset($request->processor)) {
                        $response = call_user_func($request->processor, $response);
                    }

                    $request->setResponse($response);

                } while ($remains);
        } while ($running);

        return $this;
    }

    function send() {
        $this->sendStart();
        $this->waitResponse();
    }

    function detachAll() {
        foreach ($this->pool as $request) {
            curl_multi_remove_handle($this->mh, $request->handle);
        }

        $this->pool = array();
    }

    function stop() {
        return $this->detachAll();
    }

    //for IteratorAggregate
    function getIterator() {
        return new \ArrayIterator($this->pool);
    }
}
