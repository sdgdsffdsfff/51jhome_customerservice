<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of mongoApi
 *
 * @author xlp
 */
class mongoApi {

    private static $_instance = null;
    static private $mongo; //mongo对象  
    private $db; //db mongodb对象数据库  
    private $collection = null; //集合，相当于数据表

    /*
     * 静态方法, 单例统一访问入口
     * @return  object  返回对象的唯一实例
     */

    static function getInstance($config = 'db/mongo') {
        $confStr = strtr($config, '/', '_');
        if (!isset(self::$_instance[$confStr]) || is_null(self::$_instance[$confStr])) {
            self::$_instance[$confStr] = new self($config);
        }
        return self::$_instance[$confStr];
    }

    /**
     * 初始化Mongo 
     * $config = array( 
     * 'host' => ‘127.0.0.1' 服务器地址 
     * ‘port’   => '27017' 端口地址 
     * ‘option’ => array('connect' => true) 参数 
     * 'dbname'=> 'test' 数据库名称 
     * ‘username’=> 'zhuli' 数据库用户名 
     * ‘password’=> '123456' 数据库密码 
     * )
     */
    function __construct($config = 'db/mongo') {
        $confStr = strtr($config, '/', '_');
        if (!isset(self::$mongo[$confStr]) || is_null(self::$mongo[$confStr])) {
            $conf = C($config, 'mongo');
            $auth = '';
            if ($conf['user'] && $conf['password']) {
                $auth = $conf['user'] . ':' . $conf['password'] . '@';
            }
            try {
                if (phpversion('Mongo') >= 1.3) {
                    $mongo = new MongoClient('mongodb://' . $auth . $conf['host'] . ':' . $conf['port'], $conf['option']);
                } else {
                    $mongo = new Mongo('mongodb://' . $auth . $config['host'] . ':' . $config['port'], $conf['option']);
                }
                $db = $mongo->selectDB($conf['dbname']);
            } catch (MongoException $e) {
                showError('mongo连接失败 ' . $e->getMessage());
            }
            self::$mongo[$confStr] = $db;
        }
        $this->db = &self::$mongo[$confStr];
        return $this->db;
    }

    /*
     * 获取_id
     * @param string $id id字符串
     */

    public function getId($id) {
        return new MongoId($id);
    }

    /**
     * 选择一个集合，相当于选择一个数据表 
     * @param string $table 集合名称 
     */
    public function setTable($table = '') {
        $collection = isset($this->opts['table']) ? $this->opts['table'] : $table;
        if (!$collection) {
            showError('请先选择数据表');
        }
        return $this->collection = $this->db->selectCollection($collection);
    }

    /**
     * 新增数据 
     * @param array $data 需要新增的数据 例如：array('title' => '1000', 'username' => 'xcxx') 
     * @param array $option 参数 
     */
    public function insert($data, $option = array()) {
        $this->setTable();
        return $this->collection->insert($data, $option);
    }

    /**
     * 批量新增数据 
     * @param array $data 需要新增的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx')) 
     * @param array $option 参数 
     */
    public function insertAll($data, $option = array()) {
        $this->setTable();
        return $this->collection->batchInsert($data, $option);
    }

    /**
     * 保存数据，如果已经存在在库中，则更新，不存在，则新增 
     * @param array $data 需要新增的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx')) 
     * @param array $option 参数 
     */
    public function save($data, $option = array()) {
        $this->setTable();
        return $this->collection->save($data, $option);
    }

    /**
     * 根据条件移除 
     * @param array $where  条件 例如：array(('title' => '1000')) 
     * @param array $option 参数 
     */
    public function delete($where, $option = array()) {
        if (isHave($this->opts['where'])) {
            $where = array_merge($this->opts['where'], $where);
            unset($this->opts['where']);
        }
        if (!$where) {
            showError('默认阻止没有where条件的删除语句');
        }
        $this->setTable();
        return $this->collection->remove($where, $option);
    }

    /**
     * 根据条件更新数据 
     * @param array $data   需要更新的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx')) 
     * @param array $where  条件 例如：array(('title' => '1000')) 
     * @param array $option 参数 
     */
    public function update($data, $where = array(), $option = array('multiple' => true)) {
        if (isHave($this->opts['where'])) {
            $where = array_merge($this->opts['where'], $where);
            unset($this->opts['where']);
        }
        if (!$where) {
            showError('默认阻止没有where条件的删除语句');
        }
        $this->setTable();
        return $this->collection->update($where, $data, $option);
    }

    /**
     * 根据条件查找一条数据 
     * @param array $where  条件 例如：array(('title' => '1000')) 
     * @param array $fields 参数 
     */
    public function find($where = array(), $fields = array()) {
        if (isHave($this->opts['where'])) {
            $where = array_merge($this->opts['where'], $where);
            unset($this->opts['where']);
        }
        if (isHave($this->opts['field'])) {
            $fields = array_merge($this->opts['field'], $fields);
            unset($this->opts['field']);
        }
        if (!$where) {
            showError('默认阻止没有where条件的删除语句');
        }
        $this->setTable();
        return $this->collection->findOne($where, $fields);
    }

    /**
     * 根据条件查找多条数据 
     * @param array $where 查询条件 
     * @param array $sort  排序条件 array('age' => -1, 'username' => 1) 
     * @param int   $limit 页面 
     * @param int   $skip 查询到的数据条数 
     * @param array $fields 返回的字段 
     */
    public function findAll($where = array(), $sort = array(), $limit = 0, $skip = 0, $fields = array()) {
        $fieldsVal = isset($this->opts['field']) ? $this->opts['field'] : $fields;
        $whereVal = isset($this->opts['where']) ? $this->opts['where'] : $where;
        $sortVal = isset($this->opts['order']) ? $this->opts['order'] : $sort;
        $limitVal = isset($this->opts['limit']) ? $this->opts['limit'] : $limit;
        $skipVal = isset($this->opts['skip']) ? $this->opts['skip'] : $skip;
        $this->_clear();
        $this->setTable();
        $cursor = $this->collection->find($whereVal, $fieldsVal);
        if ($sortVal) {
            $cursor->sort($sortVal);
        }
        if ($skipVal) {
            $cursor->skip($skipVal);
        }
        if ($limitVal) {
            $cursor->limit($limitVal);
        }
        return iterator_to_array($cursor);
    }

    /**
     * 数据统计 
     */
    public function count($fields = array(), $where = array()) {
        if (isHave($this->opts['where'])) {
            $where = array_merge($this->opts['where'], $where);
            unset($this->opts['where']);
        }
        if (isHave($this->opts['field'])) {
            $fields = array_merge($this->opts['field'], $fields);
            unset($this->opts['field']);
        }
        $this->setTable();
        return $this->collection->find($where, $fields)->count();
    }

    /*
     * 链式操作，指定字段值增加
     * @param $field string 用于增加计数的表字段
     * @param $num int 增加数
     * @return int 影响的行数
     */

    function setInc($field, $num = 1) {
        if (isHave($this->opts['where'])) {
            $where = array_merge($this->opts['where'], $where);
            unset($this->opts['where']);
        } else {
            $where = array();
        }
        return $this->update(array('$set' => array($field => $num)), $where);
    }

    /*
     * 链式操作，指定字段减少
     * @param $field string 用于减少计数的表字段
     * @param $num int 减少数
     * @return int 影响的行数
     */

    function setDec($field, $num = 1) {
        if (isHave($this->opts['where'])) {
            $where = array_merge($this->opts['where'], $where);
            unset($this->opts['where']);
        } else {
            $where = array();
        }
        return $this->update(array('$inc' => array($field => $num)), $where);
    }

    /**
     * 错误信息 
     */
    public function error() {
        return $this->db->lastError();
    }

    /**
     * 获取集合对象 
     */
    public function getCollection() {
        return $this->collection;
    }

    /**
     * 获取DB对象 
     */
    public function getDb() {
        return $this->db;
    }

    private function _clear() {
        $arr = array('field', 'where', 'order', 'limit', 'skip');
        foreach ($arr as $val) {
            if (isset($this->opts[$val])) {
                unset($this->opts[$val]);
            }
        }
    }

    //自动加载函数, 实现特殊操作
    function __call($func, $args) {
        if (in_array($func, array('field', 'where', 'order', 'table', 'limit', 'skip'))) {
            $this->opts[$func] = array_shift($args);
            return $this;
        }
        //如果函数不存在, 则抛出异常
        showError('Call to undefined method mongoDb::' . $func . '()');
    }

}
