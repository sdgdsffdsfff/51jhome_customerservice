<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of cacheApi
 *
 * @author xlp
 */
class cacheApi {

    static public $type = 'memcache'; //使用方式:memcache、database
    static public $db = null;

    function __construct($type = '') {
        self::connect($type);
    }

    static function connect($type = '') {
        if ($type) {
            self::$type = $type;
        }
        if (self::$type == 'database') {
            self::$db = 'data_cache'; //数据表名
            self::flush(); //一定几率的清理数据库
        } elseif (self::$type == 'memcache') {
            self::$db = new Memcache();
            self::$db->connect('localhost', 11211) or showError('Could not connect');
        } else {
            showError('缓存数据调用设置有误');
        }
    }

    //保存记录
    static function set($key, $value, $time = 0) {
        if (!self::$db) {
            self::connect();
        }
        if ($time < 0 || $time > 60 * 60 * 24 * 30) {
            $time = 60 * 10;
        }
        //参数修正
        $time = TIME + intval($time);
        if (self::$type == 'memcache') {
            self::$db->set($key, $value, false, $time);
        } else {
            if (is_null($value)) {
                return self::delete($key);
            }
            $value = json_encode($value);
            if (M(self::$db)->field('cache_key')->where(array('cache_key' => $key))->find()) {
                M(self::$db)->update(array('cache_value' => $value, 'cache_time' => $time), array('cache_key' => $key));
            } else {
                M(self::$db)->insert(array('cache_key' => $key, 'cache_value' => $value, 'cache_time' => $time));
            }
        }
        return true;
    }

    //获取记录
    static function get($key) {
        if (!self::$db) {
            self::connect();
        }
        if (self::$type == 'memcache') {
            return self::$db->get($key);
        } else {
            $rs = M(self::$db)->where(array('cache_key' => $key))->find();
            if ($rs) {
                if ($rs['cache_time'] >= TIME && $rs['cache_value']) {
                    return json_decode($rs['cache_value'], true);
                } else {
                    self::delete($key);
                }
            }
        }
        return NULL;
    }

    static function delete($key) {
        if (!self::$db) {
            self::connect();
        }
        if (self::$type == 'memcache') {
            return self::$db->delete($key);
        } else {
            M(self::$db)->delete(array('cache_key' => $key));
        }
        return true;
    }

    //清理过期数据的概率
    static function flush($rand = false) {
        if (!self::$db) {
            self::connect();
        }
        if (self::$type == 'memcache') {
            return self::$db->flush();
        } else {
            if (!$rand || ($rand && getRand(array(0.999, 0.001), 3))) {
                M(self::$db)->delete('cache_time < ' . TIME);
            }
        }
        return true;
    }

}
