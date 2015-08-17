<?php

/*
 * Mysql 数据库操作类
 */
if (!defined('IN_XLP')) {
    exit('Access Denied');
}

class Mydb {

    public $debug = false; //调试信息
    public $showerr = false; //显示错误信息
    private $dbconn;
    public $total = 0;
    static $querynum = 0;
    public $config;
    public $getAllSql = array();

    function __construct($server) {
        $this->config = $server['mysql'];
        $this->connect();
    }

    function connect() {
        if ($this->config['pconnect']) {
            $this->dbconn = @mysql_pconnect($this->config['host'] . ':' . $this->config['port'], $this->config['user'], $this->config['password']);
        } else {
            $this->dbconn = @mysql_connect($this->config['host'] . ':' . $this->config['port'], $this->config['user'], $this->config['password']);
        }
        if (!$this->dbconn) {
            $this->halt('数据库链接失败', mysql_error());
        }
        if ($this->config['dbcharset']) {
            $this->Exec("SET NAMES '" . $this->config['dbcharset'] . "'");
        }
        mysql_select_db($this->config['dbname'], $this->dbconn);
    }

//加上前缀的数据表
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

    function show($result, $returnOne = false) {
        $rs = array();
        if (!is_bool($result)) {
            while ($row = $this->fetch_array($result)) {
                $rs[] = $row;
            }
            return $returnOne && isset($rs[0]) ? $rs[0] : $rs;
        } else {
            return $result;
        }
    }

    /**
     * 统计表中记录总数
     */
    function rowtotal($table, $field = '*', $condition = '') {
        $rs = $this->fetch_assoc($this->query('SELECT COUNT(' . $field . ') AS total FROM ' . $this->realTable($table) . $this->formatCondition($condition)));
        return isset($rs['total']) ? $rs['total'] : 0;
    }

    /**
     * 取得单一记录
     */
    function fetch($table, $field = '*', $condition = '', $sort = '') {
        $sort && $sort = ' ORDER BY ' . $sort;
        return $this->fetch_assoc($this->query('SELECT ' . $field . ' FROM ' . $this->realTable($table) . $this->formatCondition($condition) . $sort . ' LIMIT 1'));
    }

    /**
     * 取得所有记录
     */
    function fetchall($table, $field = '*', $group = '', $condition = '', $sort = '', $limit = '', $getTotal = true) {
        $group && $group = ' GROUP BY ' . $group . ' ';
        $sort && $sort = ' ORDER BY ' . $sort;
        $limit && $limit = ' LIMIT ' . $limit;
        $result = $this->query('SELECT ' . $field . ' FROM ' . $this->realTable($table) . $this->formatCondition($condition) . $group . $sort . $limit);
        $rs = array();
        while ($row = $this->fetch_array($result)) {
            $rs[] = $row;
        }
        if ($rs && $getTotal) {
            $this->total = $this->rowtotal($table, '*', $condition);
        }
        return $rs;
    }

    function query($sql, $type = '') {
        $this->getAllSql[] = $sql;
        if ($this->debug) {
            $this->halt($this->getAllSql);
        }
        ++self::$querynum;
        $func = ($type == 'UNBUFFERED' && function_exists('mysql_unbuffered_query')) ? 'mysql_unbuffered_query' : 'mysql_query';
        if (!($query = $func($sql, $this->dbconn)) && $type != 'SILENT') {
            if ($this->showerr) {
                $this->halt('MySQL Query Error:' . $sql);
            } else {
                $this->halt('MySQL Query Error');
            }
        }
        return $query;
    }

    /**
     * 插入、修改、删除等操作
     */
    function Exec($sql) {
        return $this->query($sql, 'UNBUFFERED');
    }

    function clearTable($table) {
        return $this->query('TRUNCATE TABLE ' . $this->realTable($table));
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
            $safeStr = is_int($strArray) ? $strArray : "'" . mysql_real_escape_string($strArray, $this->dbconn) . "'";
        }
        return $safeStr;
    }

//插入数据简写模式
    function insert($table, $arr) {
        if (!is_array($arr)) {
            $this->halt('请输入参数数组');
        }
        $this->Exec('INSERT INTO ' . $this->realTable($table) . ' (`' . implode('`,`', array_keys($arr)) . '`) VALUES (' . implode(',', $this->safestr(array_values($arr))) . ')');
        return $this->insertId();
    }

//批量插入数据
    function insertAll($table, $arr) {
        if (!is_array($arr)) {
            $this->halt('请输入参数数组');
        } else {
            $v = '';
            if (isset($arr[0]) && is_array($arr[0])) {
                foreach ($arr as $value) {
                    $v.="(" . implode(',', $this->safestr($value)) . "),";
                }
            } else {
                $this->halt('数组格式不正确');
            }
        }
        $this->Exec('INSERT INTO ' . $this->realTable($table) . ' ' . '(`' . implode('`,`', array_keys($arr[0])) . '`)' . ' VALUES ' . rtrim($v, ','));
        return $this->affected_rows();
    }

//修改数据简写模式
    function update($table, $arr, $where) {
        $sql = 'UPDATE ' . $this->realTable($table) . ' SET ';
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                $sql .= ' `' . $key . '` = ' . $this->safestr($value) . ' ,';
            }
            $sql = rtrim($sql, ',');
        } else {
            $sql.=$arr;
        }
        $this->Exec($sql . ' ' . $this->formatCondition($where));
        return $this->affected_rows();
    }

//删除数据简写模式
    function delete($table, $where = '') {
        if (empty($where)) {
            $this->halt('条件不能为空');
        }
        $this->Exec('DELETE FROM ' . $this->realTable($table) . ' ' . $this->formatCondition($where));
        return $this->affected_rows();
    }

    /**
     * 使用事务提交数据
     * 注意mysql表为InnoDB引擎才可使用，MyISAM不行
     */
    function transaction($sqlArray) {
        $this->begin();
        if (is_array($sqlArray)) {
            foreach ($sqlArray as $sql) {
                $this->Exec($sql);
            }
        } else {
            $this->halt($sqlArray);
        }
        $this->commit();
        return true;
    }

    /**
     * 开启事务
     */
    function begin() {
        $this->query('SET AUTOCOMMIT=0'); //设置为不自动提交，因为MYSQL默认立即执行
        return $this->query('BEGIN'); //开始事务定义
    }

    /**
     * 提交事务
     */
    function commit() {
        return $this->query('COMMIT'); //执行事务
    }

    /**
     * 回滚事务
     */
    function rollback() {
        return $this->query('ROOLBACK'); //判断当执行失败时回滚
    }

    /**
     * TOOLS—获取表字段
     */
    function getFields($table) {
        $result = $this->query('SHOW FULL FIELDS FROM  ' . $this->realTable($table));
        $fields = array();
        while ($row = $this->fetch_array($result)) {
            $fields[] = $row;
        }
        return $fields;
    }

    /**
     * TOOLS—获取当前数据库类型
     */
    function databaseType() {
        return array('driver' => 'MYSQL', 'type' => 'mysql');
    }

    function fetch_array($query, $result_type = MYSQL_ASSOC) {
        return mysql_fetch_array($query, $result_type);
    }

    function affected_rows() {
        return mysql_affected_rows();
    }

    function fetch_assoc($query) {
        return mysql_fetch_assoc($query);
    }

    function num_rows($query) {
        return mysql_num_rows($query);
    }

    function free_result($query) {
        return mysql_free_result($query);
    }

    function insertId() {
        return mysql_insert_id();
    }

    function geterrdesc() {
        return mysql_error();
    }

    function geterrno() {
        return intval(mysql_errno());
    }

    function close() {
        return mysql_close($this->dbconn);
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
