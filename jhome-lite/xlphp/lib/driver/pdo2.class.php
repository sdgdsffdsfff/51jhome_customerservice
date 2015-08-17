<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * 文件描述  PDO数据库操作类
 */
class Mydb {

    private $config;
    private $db;
    static $querynum = 0; //查询统计
    public $debug = false; //调试信息
    public $showerr = false; //显示错误信息
    public $total = 0;
    public $getAllSql = array();

    function __construct($server = '') {
        if (!is_array($server)) {
            $this->halt('数据库配置文件不正确！');
        }
        //配置数据库，2选一
        $server[$server['dbtype']]['dbtype'] = $server['dbtype'];
        $this->config = $server[$server['dbtype']];
        $this->connect();
    }

    /**
     * 连接数据库
     */
    private function connect() {
        if (isset($this->db)) {
            return true;
        }
        try {
            if ($this->config['dbtype'] == 'mysql') {
                $this->db = new PDO('mysql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['dbname'] . ';charset=' . $this->config['dbcharset'], $this->config['user'], $this->config['password'], array(PDO::ATTR_PERSISTENT => $this->config['pconnect']));
                $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } elseif ($this->config['dbtype'] == 'sqlite') {
                !file_exists($this->config['host']) && $this->halt('没有找到指定的SQLITE数据库');
                $this->db = new PDO('sqlite:' . $this->config['host']);
            }
        } catch (PDOException $e) {
            $this->halt('数据库连接失败', $e->getMessage());
        }
        !isset($this->db) && $this->halt('不支持该数据库类型 ' . $this->config['dbtype']);
        //设置查询字符编码
        $this->db->Exec("SET NAMES '" . $this->config['dbcharset'] . "'");
    }

    /**
     * 将数据表加上前缀
     */
    function realTable($table) {
        return '`' . $this->config['dbprefix'] . $table . '`';
    }

    function databasePre() {
        return $this->config['dbprefix'];
    }

    function getDataBaseName() {
        return $this->config['dbname'];
    }

    /**
     * 将数组拆分为标准语句
     */
    public function formatCondition($where) {
        $whereClause = '';
        if (is_array($where)) {
            $singleCondition = array_diff_key($where, array_flip(array('AND', 'OR', 'HAVING', 'LIKE', 'MATCH')));
            if ($singleCondition != array()) {
                $whereClause = ' WHERE ' . $this->dataImplode($singleCondition, ' AND ');
            }
            if (isset($where['AND'])) {
                $whereClause = ' WHERE ' . $this->dataImplode($where['AND'], ' AND ');
            }
            if (isset($where['OR'])) {
                $whereClause = ' WHERE ' . $this->dataImplode($where['OR'], ' OR ');
            }
            if (isset($where['LIKE'])) {
                $likeQuery = $where['LIKE'];
                if (is_array($likeQuery)) {
                    if (isset($likeQuery['OR']) || isset($likeQuery['AND'])) {
                        $connector = isset($likeQuery['OR']) ? 'OR' : 'AND';
                        $likeQuery = isset($likeQuery['OR']) ? $likeQuery['OR'] : $likeQuery['AND'];
                    } else {
                        $connector = 'AND';
                    }
                    $clauseWrap = array();
                    foreach ($likeQuery as $column => $keyword) {
                        if (is_array($keyword)) {
                            foreach ($keyword as $key) {
                                $clauseWrap[] = $column . ' LIKE ' . $this->safestr('%' . $key . '%');
                            }
                        } else {
                            $clauseWrap[] = $column . ' LIKE ' . $this->safestr('%' . $keyword . '%');
                        }
                    }
                    $whereClause.=($whereClause != '' ? ' AND ' : ' WHERE ') . '(' . implode(' ' . $connector . ' ', $clauseWrap) . ')';
                }
            }
            if (isset($where['MATCH'])) {
                $matchQuery = $where['MATCH'];
                if (is_array($matchQuery) && isset($matchQuery['columns']) && isset($matchQuery['keyword'])) {
                    $whereClause.=($whereClause != '' ? ' AND ' : ' WHERE ') . ' MATCH (' . implode(', ', $matchQuery['columns']) . ') AGAINST (' . $this->safestr($matchQuery['keyword']) . ')';
                }
            }
        } else {
            if ($where != null) {
                $whereClause.=' WHERE ' . $where;
            }
        }
        return $whereClause;
    }

    /**
     * 输出sql语句错误
     */
    private function error($err = '', $type = '') {
        $error = '';
        if ($this->db->errorCode() != '00000') {
            $error = $this->db->errorInfo();
            if ($type == 'transaction') {//事务处理时返回错误信息便于异常处理捕获
                return $error;
            } elseif ($this->showerr) {
                return $this->halt('SQL语句:' . $err . '，错误：' . $error['2']);
            } else {
                return $this->halt('SQL语句执行失败！', $err . '，错误：' . $error['2']);
            }
        }
        return $error;
    }

    /**
     * 基本查询
     */
    function query($sql) {
        $this->getAllSql[] = $sql;
        if ($this->debug) {
            $this->halt($this->getAllSql);
        }
        $result = $this->db->query($sql);
        $this->error($sql);
        ++self::$querynum;
        return $result;
    }

    /**
     * 插入、修改、删除等操作
     */
    function Exec($sql, $val = array()) {
        $this->getAllSql[] = $val ? array($sql, $val) : $sql;
        if ($this->debug) {
            $this->halt($this->getAllSql);
        }
        if ($val) {
            $exec = $this->db->prepare($sql);
            $exec->execute($val);
            $result = $exec->rowCount();
        } else {
            $result = $this->db->exec($sql);
        }
        $this->error($sql);
        ++self::$querynum;
        return $result;
    }

    /**
     * 上次插入值id
     */
    function insertId() {
        return $this->db->lastInsertId();
    }

    function show($result, $returnOne = false) {
        $result = $result->fetchAll(PDO::FETCH_ASSOC);
        return $returnOne && isset($result[0]) ? $result[0] : $result;
    }

    /**
     * 取得所有记录
     */
    function fetchall($table, $field = '*', $group = '', $condition = '', $sort = '', $limit = '') {
        $group && $group = ' GROUP BY ' . $group . ' ';
        $sort && $sort = ' ORDER BY ' . $sort;
        $limit && $limit = ' LIMIT ' . $limit;
        $rs = $this->query('SELECT ' . $field . ' FROM ' . $this->realTable($table) . $this->formatCondition($condition) . $group . $sort . $limit)->fetchAll(PDO::FETCH_ASSOC);
        if ($rs) {
            $this->total = $this->rowtotal($table, '*', $condition);
        }
        if ($this->total) {
            return $rs;
        } else {
            return array();
        }
    }

    /**
     * 取得单一记录
     */
    function fetch($table, $field = '*', $condition = '', $sort = '') {
        $sort && $sort = ' ORDER BY ' . $sort;
        return $this->query('SELECT ' . $field . ' FROM ' . $this->realTable($table) . $this->formatCondition($condition) . $sort . ' LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 统计表中记录总数
     */
    function rowtotal($table, $field = '*', $condition = '') {
        return $this->query('SELECT COUNT(' . $field . ') FROM ' . $this->realTable($table) . $this->formatCondition($condition))->fetchColumn();
    }

    /**
     * sql语句过滤
     */
    function safestr($strArray) {
        $safeStr = '';
        if (is_array($strArray)) {
            foreach ($strArray as $strKey => $strValue) {
                $safeStr[$strKey] = $this->safestr($strValue); //递归处理
            }
        } else {
            $safeStr = is_int($strArray) ? $strArray : $this->db->quote($strArray);
        }
        return $safeStr;
    }

    /**
     * 插入简写模式
     */
    function insert($table, $arr) {
        if (!is_array($arr)) {
            $this->halt('简写模式插入数据时必须为数组形式');
        }
        $objData=array();
        foreach($arr as $key=>$val){
            $objData[':'.$key]=$val;
        }
        $this->Exec('INSERT INTO ' . $this->realTable($table) . ' (`' .
                implode('`,`', array_keys($arr)) . '`) VALUES (' .
                implode(',', array_keys($objData)) . ')', $objData);
        return $this->insertId();
    }

    /**
     * 更新简写模式
     */
    function update($table, $arr, $where) {
        $sql = 'UPDATE ' . $this->realTable($table) . ' SET ';
        $values = array();
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                $sql .= " `{$key}` = :{$key},";
                $values[':'.$key] = $value;
            }
            $sql = rtrim($sql, ',');
        } else {
            $sql.=$arr;
        }
        return $this->Exec($sql . ' ' . $this->formatCondition($where), $values);
    }

    /**
     * 删除简写模式
     */
    function delete($table, $condition) {
        $result = $this->Exec('DELETE FROM ' . $this->realTable($table) . $this->formatCondition($condition));
        if ($this->config['dbtype'] == 'sqlite') {
            $this->query('VACUUM'); //整理文件大小
        }
        return $result;
    }

//批量插入数据
    function insertAll($table, $arr) {
        if (!is_array($arr)) {
            $this->halt('请输入参数数组！');
        } else {
            if (isset($arr[0]) && is_array($arr[0])) {
                $keys = array_keys($arr[0]);
                $stmt = $this->db->prepare('INSERT INTO ' . $this->realTable($table) . ' (`' . implode('`,`', $keys) . '`)' . 'VALUES (' . rtrim(str_repeat('?,', count($keys)), ',') . ')');
                foreach ($arr as $value) {
                    $stmt->execute(array_values($value));
                }
                return count($arr);
            } else {
                $this->halt('数组格式不正确！');
            }
        }
    }

    /**
     * TOOLS—获取有效的PDO驱动器支持属性
     */
    function pdoDrivers() {
        return $this->db->getAvailableDrivers();
    }

    /**
     * TOOLS—获取当前数据库类型
     */
    function databaseType() {
        return array('driver' => 'PDO', 'type' => $this->config['dbtype']);
    }

    /**
     * TOOLS—获取表字段
     */
    function getFields($table) {
        if ($this->config['dbtype'] == 'mysql') {
            $sql = 'SHOW FULL FIELDS FROM ' . $this->realTable($table);
        } else if ($this->config['dbtype'] == 'sqlite') {
            $sql = 'PRAGMA table_info(' . $this->realTable($table) . ')';
        }
        $fields = $this->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $fields;
    }

    /**
     * 使用事务提交数据
     * 注意mysql表为InnoDB引擎才可使用，MyISAM不行
     */
    function transaction($sqlArray) {
        $this->begin();
        if (is_array($sqlArray)) {
            foreach ($sqlArray as $sql) {
                $this->db->exec($sql);
            }
        } else {
            $this->rollback();
            $this->halt($sqlArray);
        }
        return true;
    }

    /**
     * 开启事务
     */
    function begin() {
        return $this->db->beginTransaction();
    }

    /**
     * 提交事务
     */
    function commit() {
        return $this->db->commit();
    }

    /**
     * 回滚事务
     */
    function rollback() {
        return $this->db->rollBack();
    }

    /**
     * 关闭mysql连接
     */
    function close() {
        if ($this->db) {
            $this->db = null;
        }
    }

    public function __destruct() {
        $this->close();
    }

    protected function innerConjunct($data, $conjunctor, $outerConjunctor) {
        $haystack = array();
        foreach ($data as $value) {
            $haystack[] = '(' . $this->dataImplode($value, $conjunctor) . ')';
        }
        return implode($outerConjunctor . ' ', $haystack);
    }

    protected function dataImplode($data, $conjunctor) {
        $wheres = array();
        foreach ($data as $key => $value) {
            if (($key == 'AND' || $key == 'OR') && is_array($value)) {
                $wheres[] = 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) ? '(' . $this->dataImplode($value, ' ' . $key) . ')' : '(' . $this->innerConjunct($value, ' ' . $key, $conjunctor) . ')';
            } else {
                $match = array();
                preg_match('/([\w]+)(\[(\>|\>\=|\<|\<\=|\!|\<\>)\])?/i', $key, $match);
                if (isset($match[3])) {
                    if ($match[3] == '' || $match[3] == '!') {
                        $wheres[] = $match[1] . ' ' . $match[3] . '= ' . $this->safestr($value);
                    } else {
                        if ($match[3] == '<>') {
                            if (is_array($value) && is_numeric($value[0]) && is_numeric($value[1])) {
                                $wheres[] = '(' . $match[1] . ' BETWEEN ' . $value[0] . ' AND ' . $value[1] . ')';
                            }
                        } else {
                            if (is_numeric($value)) {
                                $wheres[] = $match[1] . ' ' . $match[3] . ' ' . $value;
                            }
                        }
                    }
                } else {
                    if (is_int($key)) {
                        $wheres[] = $this->safestr($value);
                    } else {
                        $wheres[] = is_array($value) ? $match[1] . ' IN (' . implode(',', $this->safestr($value)) . ')' : $match[1] . ' = ' . $this->safestr($value);
                    }
                }
            }
        }
        return implode($conjunctor . ' ', $wheres);
    }

    /**
     * 输出自定义提示信息
     */
    private function halt($msg = '', $errMsg = '') {
        if (isHave($this->config['save_errlog'])) {
            saveLog('db/error', var_export($msg, true) . ':' . $errMsg);
        }
        showError($msg);
    }

}
