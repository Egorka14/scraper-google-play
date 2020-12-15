<?php


class PredisCustom {

    function set($key, $params = []) {


        try {
            $redis = new Predis\Client();

            if ($params['method'] === 'array') {
                $redis->sadd($key, $params['value']);
            } else {
                $redis->set($key, $params['value']);
            }

            // This connection is for a remote server
            /*
                $redis = new PredisClient(array(
                    "scheme" => "tcp",
                    "host" => "153.202.124.2",
                    "port" => 6379
                ));
            */

//            $redis->lrange($key, 0, -1); //output all values
            return true;

        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function get($key, $params = []) {
        try {
            $redis = new Predis\Client();

            if ($params['method'] === 'array') {

                switch ($params['function']) {
                    case 'rpop':
                        $result = $redis->rpop($key);
                        break;
                    case 'sismember':
                        $result = $redis->sismember($key, $params['value']);
                        break;
                    case 'smembers':
                        $result = $redis->smembers($key);
                        break;
                    default:
                        $result = $redis->lrange($key, $params['start'], $params['finish']);
                }
            } else {
                $result = $redis->get($key);
            }

            return $result;
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function createCounter($key) {
        $redis = new Predis\Client();
        return $redis->set($key, 0);
    }

    function inc($key) {
        $redis = new Predis\Client();
        return $redis->incr($key);
    }

    function dec($key) {
        $redis = new Predis\Client();
        return $redis->decr($key);
    }
    function ex($key) {
        $redis = new Predis\Client();
        return $redis->exists($key);
    }
    function delAll() {
        $redis = new Predis\Client();
        return $redis->del($redis->keys('*'));
    }

    function setExp($key, $time) {
        $redis = new Predis\Client();
        return $redis->expire($key, $time);
    }
    function  getExp($key, $time) {
        $redis = new Predis\Client();
        return $redis->ttl($key);
    }
}

