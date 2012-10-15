<?php
namespace Curl;

class RequestPool extends Base implements \Iterator
{
    protected
        $mh
      , $timeout = 10
      , $pool = array()  //Request $req -> $req;
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
        $this->pool[spl_object_hash($req)] = $req;

        curl_multi_add_handle($this->mh, $req->handle);
    }

    function detach(Request $req) {
        unset($this->pool[spl_object_hash($req)]);

        curl_multi_remove_handle($this->mh, $req->handle);
    }

    function sendStart() {
        $mh = $this->mh;

        $stat = curl_multi_exec($mh, $running);
        if (! $running || $stat !== CURLM_OK) {
            throw new \RuntimeException('request cannot start');
        }

        return $this;
    }

    function waitResponse() {
        $mh = $this->mh;

        do switch (curl_multi_select($mh, $this->timeout)) {
            case -1:
            case 0:
                throw new \RuntimeException('timeout.');

            default:
                $stat = curl_multi_exec($mh, $running);

                do if ($raised = curl_multi_info_read($mh, $remains)) {
                    $info = curl_getinfo($raised['handle']);
                    $body = curl_multi_getcontent($raised['handle']);

                    $response = new Response($body, $info);
                    $request = $this->_searchRequestByHandle($raised['handle']);

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

    //全部消す。
    function detachAll() {
        foreach ($this->pool as $request) {
            curl_multi_remove_handle($this->mh, $request->handle);
        }

        $this->pool = array();
    }

    function stop() {
        return $this->detachAll();
    }

    protected function _searchRequestByHandle($ch) {
        foreach ($this->pool as $req) {
            if ($req->handle === $ch) {
                return $req;
            }
        }

        throw new \RuntimeException('The handle is not found.');
    }

    //for iterator
    function rewind() {
        reset($this->pool);
        $this->_valid = true;
    }

    function current() {
        return current($this->pool);
    }

    function key() {
        $req = current($this->pool);
        return $req->getResponse()->getUrl();
    }

    function next() {
        $next = next($this->pool);
        if ($next === false) {
            $this->_valid = false;
        }
    }

    function valid() {
        return $this->_valid;
    }

}
