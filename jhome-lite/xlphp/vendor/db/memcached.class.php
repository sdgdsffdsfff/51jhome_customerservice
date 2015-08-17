<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of memcachedApi
 *
 * @author xlp
 */
class memcachedApi {

    static private $memcache = null;

    /**
     * Memcache缓存-设置链接服务器 
     * 支持多MEMCACHE服务器 
     * 配置文件中配置Memcache缓存服务器： 
     * array('127.0.0.1', '11211');   
     * @param  array $config 服务器数组-array(array('127.0.0.1', '11211')) 
     */
    static public function init($config = 'db/memcache') {
        if (!self::$memcache || is_null(self::$memcache)) {
            $conf = C($config, 'memcache');
            if (!is_array($conf) || empty($conf)) {
                showError('memcache server is null!');
            }
            self::$memcache = new Memcache;
            foreach ($conf as $val) {
                self::$memcache->addServer($val['host'], $val['port']);
            }
        }
        return self::$memcache;
    }

    /**
     * Memcache缓存-设置缓存 
     * 设置缓存key，value和缓存时间 
     * @param  string $key   KEY值 
     * @param  string $value 值 
     * @param  string $time  缓存时间 
     */
    static public function set($key, $value, $time = 0) {
        return self::$memcache->set($key, $value, false, $time);
    }

    /**
     * Memcache缓存-获取缓存 
     * 通过KEY获取缓存数据 
     * @param  string $key   KEY值 
     */
    static public function get($key) {
        return self::$memcache->get($key);
    }

    /**
     * Memcache缓存-清除一个缓存 
     * 从memcache中删除一条缓存 
     * @param  string $key   KEY值 
     */
    static public function clear($key) {
        return self::$memcache->delete($key);
    }

    /**
     * Memcache缓存-清空所有缓存 
     * 不建议使用该功能 
     * @return 
     */
    static public function clearAll() {
        return self::$memcache->flush();
    }

    /**
     * 字段自增-用于记数 
     * @param string $key  KEY值 
     * @param int    $step 新增的step值 
     */
    static public function setInc($key, $step = 1) {
        return self::$memcache->increment($key, (int) $step);
    }

    /**
     * 字段自减-用于记数 
     * @param string $key  KEY值 
     * @param int    $step 新增的step值 
     */
    static public function setDec($key, $step = 1) {
        return self::$memcache->decrement($key, (int) $step);
    }

    /**
     * 关闭Memcache链接 
     */
    static public function close() {
        return self::$memcache->close();
    }

    /**
     * 替换数据 
     * @param string $key 期望被替换的数据 
     * @param string $value 替换后的值 
     * @param int    $time  时间值 
     * @param bool   $flag  是否进行压缩 
     */
    static public function replace($key, $value, $time = 0, $flag = false) {
        return self::$memcache->replace($key, $value, $flag, $time);
    }

    /**
     * 获取Memcache的版本号 
     */
    static public function getVersion() {
        return self::$memcache->getVersion();
    }

    /**
     * 获取Memcache的状态数据 
     */
    static public function getStats() {
        return self::$memcache->getStats();
    }

}
