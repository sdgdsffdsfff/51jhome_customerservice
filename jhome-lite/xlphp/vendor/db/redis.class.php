<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of redis
 *
 * @author xlp
 */
class redisApi {

    static private $redis; //redis对象
    static private $db;

    /**
     * 初始化Redis 
     * $config = array( 
     *  'host' => '127.0.0.1' 服务器 
     *  'port'   => '6379' 端口号 
     * ) 
     * @param array $config 
     */
    static public function init($config = 'db/redis') {
        $confStr = strtr($config, '/', '_');
        if (!isset(self::$redis[$confStr])) {
            $conf = C($config, 'redis');
            $redis = new Redis();
            $redis->connect($conf['host'], $conf['port']);
            if ($conf['password']) {
                $redis->auth($conf['password']);
            }
            self::$redis[$confStr] = $redis;
        }
        self::$db = &self::$redis[$confStr];
        return self::$db;
    }

    /**
     * 设置值 
     * @param string $key KEY名称 
     * @param string|array $value 获取得到的数据 
     * @param int $timeOut 时间 
     */
    static public function set($key, $value, $timeOut = 0) {
        $retRes = self::$db->set($key, json_encode($value, true));
        if ($timeOut > 0) {
            self::$db->expire($key, $timeOut);
        }
        return $retRes;
    }

    /**
     * 通过KEY获取数据 
     * @param string $key KEY名称 
     */
    static public function get($key) {
        return json_decode(self::$db->get($key), true);
    }

    /**
     * 删除一条数据 
     * @param string $key KEY名称 
     */
    static public function delete($key) {
        return self::$db->delete($key);
    }

    /**
     * 清空数据 
     */
    static public function flushAll() {
        return self::$db->flushAll();
    }

    /**
     * 数据入队列 
     * @param string $key KEY名称 
     * @param string|array $value 获取得到的数据 
     * @param bool $right 是否从右边开始入 
     */
    static public function push($key, $value, $right = true) {
        return $right ? self::$db->rPush($key, json_encode($value)) : self::$db->lPush($key, json_encode($value));
    }

    /**
     * 数据出队列 
     * @param string $key KEY名称 
     * @param bool $left 是否从左边开始出数据 
     */
    static public function pop($key, $left = true) {
        $val = $left ? self::$db->lPop($key) : self::$db->rPop($key);
        return json_decode($val);
    }

    /**
     * 数据自增 
     * @param string $key KEY名称 
     */
    static public function increment($key) {
        return self::$db->incr($key);
    }

    /**
     * 数据自减 
     * @param string $key KEY名称 
     */
    static public function decrement($key) {
        return self::$db->decr($key);
    }

    /**
     * key是否存在，存在返回ture 
     * @param string $key KEY名称 
     */
    static public function isExists($key) {
        return self::$db->exists($key);
    }

    /*
     * 构建一个集合(无序集合)
     * @param string $key 集合Y名称
     * @param string|array $value  值
     */

    static public function sadd($key, $value) {
        return self::$db->sadd($key, $value);
    }

    /*
     * 构建一个集合(有序集合)
     * @param string $key 集合名称
     * @param string|array $value  值
     */

    static public function zadd($key, $value) {
        return self::$db->zadd($key, $value);
    }

    /**
     * 取集合对应元素
     * @param string $setName 集合名字
     */
    static public function smembers($setName) {
        return self::$db->smembers($setName);
    }

    /**
     * 获取所有列表数据（从头到尾取）
     * @param sting $key KEY名称
     * @param int $head  开始
     * @param int $tail     结束
     */
    static public function lranges($key, $head, $tail) {
        return self::$db->lrange($key, $head, $tail);
    }

    /**
     * 设置HASH类型
     * @param string $tableName  表名字key
     * @param string $field            字段名字
     * @param sting $value          值
     */
    static public function hset($tableName, $field, $value) {
        return self::$db->hset($tableName, $field, json_encode($value, true));
    }

    /**
     * 读取HASH类型
     * @param string $tableName  表名字key
     * @param string $field            字段名字
     */
    static public function hget($tableName, $field) {
        return json_decode(self::$db->hget($tableName, $field), true);
    }

    /**
     * 以列表形式返回哈希表的域和域的值
     * @param string $tableName  表名字key
     */
    static public function hgetAll($tableName) {
        return self::$db->hgetall($tableName);
    }

    /**
     * 以列表形式返回哈希表的域
     * @param string $tableName  表名字key
     */
    static public function hkeyAll($tableName) {
        return self::$db->hkeys($tableName);
    }

    /**
     * 删除哈希表key中的一个或多个指定域，不存在的域将被忽略
     * @param string $tableName  表名字key
     * @param string $field            字段名字
     */
    static public function hdelete($tableName, $field) {
        return self::$db->hdel($tableName, $field);
    }

    /**
     * 返回哈希表key中域的数量
     * @param string $tableName  表名字key
     */
    static public function hlen($tableName) {
        return self::$db->hlen($tableName);
    }

    /**
     * 查看哈希表key中，给定域field是否存在
     * @param string $tableName  表名字key
     * @param string $field            字段名字
     */
    static public function hIsExists($tableName, $field) {
        return self::$db->hexists($tableName, $field);
    }

    /**
     * 设置多个值
     * @param array $keyArray KEY名称
     * @param string|array $value 获取得到的数据
     * @param int $timeOut 时间
     */
    static public function sets($keyArray, $timeout) {
        if (is_array($keyArray)) {
            $retRes = self::$db->mset($keyArray);
            if ($timeout > 0) {
                $keys = array_keys($keyArray);
                foreach ($keys as $value) {
                    self::$db->expire($value, $timeout);
                }
            }
            return $retRes;
        } else {
            showError("Call  " . __FUNCTION__ . " method  parameter  Error !");
        }
    }

    /**
     * 同时获取多个值
     * @param ayyay $keyArray 获key数值
     */
    static public function gets($keyArray) {
        if (is_array($keyArray)) {
            return self::$db->mget($keyArray);
        } else {
            showError("Call  " . __FUNCTION__ . " method  parameter  Error !");
        }
    }

    /**
     * 同时删除多个key数据
     * @param array $keyArray KEY集合
     */
    static public function dels($keyArray) {
        if (is_array($keyArray)) {
            return self::$db->del($keyArray);
        } else {
            showError("Call  " . __FUNCTION__ . " method  parameter  Error !");
        }
    }

    /**
     * 获取所有key名，不是值
     */
    static public function keyAll($prefix = '') {
        return self::$db->keys($prefix . '*');
    }

    /**
     * 返回redis对象信息
     */
    static public function info() {
        return self::$db->info();
    }

    /**
     * 返回redis对象 
     * redis有非常多的操作方法，我们只封装了一部分 
     * 拿着这个对象就可以直接调用redis自身方法 
     */
    static public function redis() {
        return self::$db;
    }

}
