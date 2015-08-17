<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of model
 * 模型继承基类
 * @author xlp
 */
class model {

    public $opts = array();
    public $dbTable = null; //定义数据表
    private static $_instance = null;
    private static $db = null;
    private $dataBase = null;

    /*
     * 静态方法, 单例统一访问入口
     * @return  object  返回对象的唯一实例
     */

    static function getInstance($config = 'database') {
        if (!isset(self::$_instance[$config]) || is_null(self::$_instance[$config])) {
            self::$_instance[$config] = new self($config);
        }
        return self::$_instance[$config];
    }

    /*
     * 构造方法
     * @return  object  返回对象的唯一实例
     */

    function __construct($config = 'database') {
        if (!isset(self::$db[$config]) || is_null(self::$db[$config])) {
            $conf = C($config);
            if (!class_exists('Mydb')) {
                load($conf['dbdrive']);
            }
            self::$db[$config] = new Mydb($conf);
            isset($conf['debug']) && self::$db[$config]->showerr = $conf['debug'];
        }
        $this->dataBase = &self::$db[$config];
    }

    /*
     * 设置调试模式，开启以后会打印出此方法之前所有的待执行的SQL语句
     * @param $bool bool 是否开启
     * @return 无
     */

    function setDebug($bool = true) {
        $this->dataBase->debug = $bool;
    }

    /*
     * 设置显示数据库错误信息
     * 默认情况下所有执行失败的sql语句是不会显示详细错误的，在调试程序是开启此方法便于排错
     * @param $bool bool 是否开启
     * @return 无
     */

    function setShowError($bool = true) {
        $this->dataBase->showerr = $bool;
    }

    /*
     * 获取表前缀
     * @param 无
     * @return 添加前缀的数据表名称
     */

    function getTablepre() {
        return $this->dataBase->databasePre();
    }

    /*
     * 直接执行SQL语句，支持原生SQL语句
     * @param $sql string 待执行的SQL语句
     * @param $returnOne bool 是否是返回单条语句
     * 本方法获取结果集时是返回多维数组的，部分情况只查询单条记录时，设置$returnOne=true，就会直接返回二维数组，以减少数组的层次
     * @return array
     */

    function query($sql = '', $returnOne = false) {
        if (strExists($sql, '__TABLE__')) {
            $sql = str_replace('__TABLE__', $this->dataBase->realTable($this->_getdbtable()), $sql);
        }
        if (strExists($sql, '__PRE__')) {
            $sql = str_replace('__PRE__', $this->getTablepre(), $sql);
        }
        return $this->dataBase->show($this->dataBase->query($sql), $returnOne);
    }

    /*
     * 插入数据
     * @param $arr array 待插入的数组
     * @return int 新增记录的主键值
     */

    function insert($arr = array()) {
        if (isHave($this->opts['data'])) {
            if (is_array($this->opts['data']) && is_array($arr)) {
                $arr = array_merge($this->opts['data'], $arr);
            }
            unset($this->opts['data']);
        }
        if (!$arr) {
            showError('默认阻止没有数据条件的插入语句');
        }
        return $this->dataBase->insert($this->_getdbtable(), $arr);
    }

    /*
     * 批量插入数据
     * @param $arr array 待插入的数组
     * @return int 新增记录的数量
     */

    function insertAll($arr = array()) {
        if (isHave($this->opts['data'])) {
            if (is_array($this->opts['data']) && is_array($arr)) {
                $arr = array_merge($this->opts['data'], $arr);
            }
            unset($this->opts['data']);
        }
        if (!$arr) {
            showError('默认阻止没有数据条件的批量插入语句');
        }
        return $this->dataBase->insertAll($this->_getdbtable(), $arr);
    }

    /*
     * 更新数据
     * @param $arr array 待更新的数组
     * @param $where array or string 更新条件，支持数组和字符串，但不能为空
     * @return int 更新影响记录的数量
     */

    function update($arr, $where = array()) {
        if (isHave($this->opts['data'])) {
            if (is_array($this->opts['data']) && is_array($arr)) {
                $arr = array_merge($this->opts['data'], $arr);
            }
            unset($this->opts['data']);
        }
        if (isHave($this->opts['where'])) {
            if (is_array($this->opts['where']) && is_array($where)) {
                $where = array_merge($this->opts['where'], $where);
            }
            unset($this->opts['where']);
        }
        if (!$where) {
            showError('默认阻止没有where条件的更新语句');
        }
        return $this->dataBase->update($this->_getdbtable(), $arr, $where);
    }

    /*
     * 删除数据简写模式
     * @param $where array or string 更新条件，支持数组和字符串，但不能为空
     * @return int 影响记录的数量
     */

    function delete($where = array()) {
        if (isHave($this->opts['where'])) {
            if (is_array($this->opts['where']) && is_array($where)) {
                $where = array_merge($this->opts['where'], $where);
            }
            unset($this->opts['where']);
        }
        if (!$where) {
            showError('默认阻止没有where条件的删除语句');
        }
        return $this->dataBase->delete($this->_getdbtable(), $where);
    }

    /*
     * 返回最后插入的记录的ID
     * @param 无
     * @return int
     */

    function getInsertid() {
        return $this->dataBase->insertId();
    }

    /*
     * 返回所有执行的SQL语句
     * @param 无
     * @return array
     */

    function getAllsql() {
        return $this->dataBase->getAllSql;
    }

    /*
     * 处理安全字符串
     * @param $arr string 待处理的字符串
     * @return string
     */

    function getSafeStr($arr) {
        return trim($this->dataBase->safestr($arr), "'\\");
    }

    /*
     * 处理复杂where条件
     * @param $arr array 待处理的where数组
     * @return string
     */

    function getWhere($arr) {
        return $this->dataBase->formatCondition($arr);
    }

    /*
     * 返回数据库查询次数
     * @param 无
     * @return int
     */

    function getQuerynum() {
        return $this->dataBase->querynum;
    }

    /*
     * 返回表所有字段
     * @param 无
     * @return array
     */

    function getFields() {
        return $this->dataBase->getFields($this->_getdbtable());
    }

    /*
     * 返回记录集总数
     * @param 无
     * @return int
     */

    function getTotal() {
        return $this->dataBase->total;
    }

    /*
     * 返回数据库驱动
     * @param 无
     * @return string
     */

    function getDbtype() {
        return $this->dataBase->databaseType();
    }

    /*
     * 链式操作，返回多条记录
     * @param $getTotal bool 是否统计结果集，如果为false，则getTotal()只会返回0
     * @return array
     */

    function findAll($getTotal = true) {
        $field = isset($this->opts['field']) ? $this->opts['field'] : '*';
        $group = isset($this->opts['group']) ? $this->opts['group'] : '';
        $condition = isset($this->opts['where']) ? $this->opts['where'] : '';
        $sort = isset($this->opts['order']) ? $this->opts['order'] : '';
        $limit = isset($this->opts['limit']) ? $this->opts['limit'] : '';
        $this->_clear();
        return $this->fetchall($field, $group, $condition, $sort, $limit, $getTotal);
    }

    /*
     * 链式操作，返回单条记录
     * @param 无
     * @return array
     */

    function find() {
        $field = isset($this->opts['field']) ? $this->opts['field'] : '*';
        $condition = isset($this->opts['where']) ? $this->opts['where'] : '';
        $sort = isset($this->opts['order']) ? $this->opts['order'] : '';
        $this->_clear();
        return $this->fetch($field, $condition, $sort);
    }

    /*
     * 链式操作，返回多条记录,数组键名为主键
     * @param $key 表的主键
     * @param $getTotal bool 是否统计结果集，如果为false，则getTotal()只会返回0
     * @return array
     */

    function select($key = 'id', $getTotal = true) {
        $field = isset($this->opts['field']) ? $this->opts['field'] : '*';
        $group = isset($this->opts['group']) ? $this->opts['group'] : '';
        $condition = isset($this->opts['where']) ? $this->opts['where'] : '';
        $sort = isset($this->opts['order']) ? $this->opts['order'] : '';
        $limit = isset($this->opts['limit']) ? $this->opts['limit'] : '';
        $this->_clear();
        $list = $this->fetchall($field, $group, $condition, $sort, $limit, $getTotal);
        $rs = array();
        if ($list) {
            if (!isset($list[0][$key])) {
                showError('不存在字段:' . $key);
            }
            foreach ($list as $val) {
                $rs[$val[$key]] = $val;
            }
        }
        return $rs;
    }

    /*
     * 链式操作，返回单条记录单字段
     * @param $field string 表的字段
     * @return string
     */

    function getField($field = '') {
        if (!$field) {
            showError('getField需要输入字段');
        }
        $condition = isset($this->opts['where']) ? $this->opts['where'] : '';
        $sort = isset($this->opts['order']) ? $this->opts['order'] : '';
        $this->_clear();
        $rs = $this->fetch($field, $condition, $sort);
        return $rs && isHave($rs[$field]) ? $rs[$field] : null;
    }

    /*
     * 链式操作，返回符合条件的记录集总数
     * @param $field string 用于统计的表字段
     * @return int
     */

    function count($field = '*') {
        $field = isset($this->opts['field']) ? $this->opts['field'] : $field;
        $condition = isset($this->opts['where']) ? $this->opts['where'] : '';
        $this->_clear();
        return $this->rowCount($field, $condition);
    }

    /*
     * 链式操作，指定字段值增加
     * @param $field string 用于增加计数的表字段
     * @param $num int 增加数
     * @return int 影响的行数
     */

    function setInc($field, $num = 1) {
        return $this->_setfieldValue($field, $num, '+');
    }

    /*
     * 链式操作，指定字段减少
     * @param $field string 用于减少计数的表字段
     * @param $num int 减少数
     * @return int 影响的行数
     */

    function setDec($field, $num = 1) {
        return $this->_setfieldValue($field, $num, '-');
    }

    /*
     * 开启事务
     * @param 无
     * @return 
     */

    function begin() {
        return $this->dataBase->begin();
    }

    /*
     * 使用事务提交数据
     * @param $sqlArray array 用于事物执行的SQL数组
     * @return 
     */

    function transaction($sqlArray) {
        return $this->dataBase->transaction($sqlArray);
    }

    /*
     * 提交事务
     * @param 无
     * @return 
     */

    function commit() {
        return $this->dataBase->commit();
    }

    /*
     * 回滚事务
     * @param 无
     * @return 
     */

    function rollback() {
        return $this->dataBase->rollback();
    }

    /*
     * 根据表名获取字段及默认值，该方法会缓存表的字段名
     * 如果数据库字段有变化，需要手动删除缓存文件，缓存位于runtime/data/_Fields/下
     * @param $noFields array 需要排除的字段数组
     * @return array 字段数组
     */

    function getTableFields($noFields = array()) {
        //缓存字段名
        $table = $this->_getdbtable();
        $rs = F('_Fields/' . $this->dataBase->getDataBaseName() . '/_' . $table);
        if (!$rs) {
            $list = $this->dataBase->getFields($table);
            if ($list) {
                $rs = array();
                foreach ($list as $val) {
                    if (($val['Extra'] == 'auto_increment' || $val['Key'] == 'PRI')) {
                        $rs['key'] = $val['Field'];
                    } else {
                        $rs['fields'][$val['Field']] = array('name' => $val['Comment'], 'value' => $val['Default'], 'null' => $val['Null'] == 'NO' ? 0 : 1, 'type' => strExists($val['Type'], 'int') ? 'int' : 'char');
                    }
                }
                F('_Fields/' . $this->dataBase->getDataBaseName() . '/_' . $table, $rs);
            }
        }
        //删除去除字段
        if ($noFields) {
            foreach ($noFields as $val) {
                if (isset($rs['fields'][$val])) {
                    unset($rs['fields'][$val]);
                }
            }
        }
        return $rs;
    }

//-------------------------------------------------------------------------------
    //查询单条记录
    private function fetch($field = '*', $condition = '', $sort = '') {
        return $this->dataBase->fetch($this->_getdbtable(), $field, $condition, $sort);
    }

//查询多条记录
    private function fetchall($field = '*', $group = '', $condition = '', $sort = '', $limit = '', $getTotal = true) {
        $this->dataBase->total = 0;
        return $this->dataBase->fetchall($this->_getdbtable(), $field, $group, $condition, $sort, $limit, $getTotal);
    }

//返回符合条件的记录总数
    private function rowCount($field, $condition) {
        return $this->dataBase->rowtotal($this->_getdbtable(), $field, $condition);
    }

    //自动加载函数, 实现特殊操作
    function __call($func, $args) {
        if (in_array($func, array('field', 'join', 'where', 'order', 'group', 'having', 'on', 'table', 'data'))) {
            $this->opts[$func] = array_shift($args);
            return $this;
        } elseif ($func == 'limit') {
            $this->opts[$func] = implode(',', $args);
            return $this;
        } elseif ($func == 'page') {
            if (isHave($args)) {
                $p = $args;
            } else {
                $p = array(1, 20);
            }
            if (!isset($p[0]) || !checkNum($p[0]) || $p[0] < 1) {
                $p[0] = 1;
            }
            if (!isset($p[1]) || !checkNum($p[1])) {
                $p[1] = 20;
            }
            $this->opts['limit'] = (($p[0] - 1) * $p[1]) . ',' . $p[1];
            return $this;
        }
        //如果函数不存在, 则抛出异常
        showError('Call to undefined method Db::' . $func . '()');
    }

    private function _clear() {
        $arr = array('field', 'join', 'where', 'order', 'group', 'having', 'on', 'limit', 'page', 'data');
        foreach ($arr as $val) {
            if (isset($this->opts[$val])) {
                unset($this->opts[$val]);
            }
        }
    }

    private function _getdbtable() {
        if (isHave($this->opts['table'])) {
            $this->dbTable = $this->opts['table'];
            unset($this->opts['table']);
        }
        if (empty($this->dbTable)) {
            showError('缺少必要的参数：table');
        }
        return $this->dbTable;
    }

    private function _setfieldValue($field, $num, $act = '+') {
        if (empty($field) || is_array($field)) {
            showError('参数不正确：field');
        }
        $condition = isset($this->opts['where']) ? $this->opts['where'] : '';
        if (!$condition) {
            showError('默认阻止没有where条件的更新语句');
        }
        $this->_clear();
        return $this->dataBase->update($this->_getdbtable(), $field . '=' . $field . $act . intval($num), $condition);
    }

//-------------------------------------------------------------------------------
}
