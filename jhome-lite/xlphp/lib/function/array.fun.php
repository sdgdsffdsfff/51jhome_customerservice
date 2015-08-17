<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
  +----------------------------------------------------------
 * 把返回的数据集转换成Tree
  +----------------------------------------------------------
 * @access public
  +----------------------------------------------------------
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
  +----------------------------------------------------------
 * @return array
  +----------------------------------------------------------
 */
function treeByList($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = & $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] = & $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = & $refer[$parentId];
                    $parent[$child][] = & $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
  +----------------------------------------------------------
 * 对查询结果集进行排序
  +----------------------------------------------------------
 * @access public
  +----------------------------------------------------------
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
  +----------------------------------------------------------
 * @return array
  +----------------------------------------------------------
 */
function sortByList($list, $field, $sortby = 'asc') {
    if (is_array($list)) {
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/**
  +----------------------------------------------------------
 * 在数据列表中搜索
  +----------------------------------------------------------
 * @access public
  +----------------------------------------------------------
 * @param array $list 数据列表
 * @param mixed $condition 查询条件
 * 支持 array('name'=>$value) 或者 name=$value
  +----------------------------------------------------------
 * @return array
  +----------------------------------------------------------
 */
function searchByList($list, $condition) {
    if (is_string($condition))
        parse_str($condition, $condition);
    // 返回的结果集合
    $resultSet = array();
    foreach ($list as $key => $data) {
        $find = false;
        foreach ($condition as $field => $value) {
            if (isset($data[$field])) {
                if (0 === strpos($value, '/')) {
                    $find = preg_match($value, $data[$field]);
                } elseif ($data[$field] == $value) {
                    $find = true;
                }
            }
        }
        if ($find)
            $resultSet[] = &$list[$key];
    }
    return $resultSet;
}

/** @method:xml to array  *
 *  @param : simplexml $xml
 *  @author:taylor
 * */
function xmlToArray($xml, &$tmparr = array()) {
    $object = simplexml_load_file($xml);
    if (is_object($object)) {
        $tmparr = get_object_vars($object);
    }
    foreach ($tmparr as $k => $v) {
        if (is_array($v)) {
            $tmparr[$k] = xmlToArray($v, $tmparr[$k]);
        } elseif (is_object($v)) {
            $tmparr[$k] = xmlToArray($v, $tmparr[$k]);
        } else {
            $tmparr[$k] = $v;
        }
    }
    return $tmparr;
}

/** 将数组生成XML文档
 *  @param $data array 输入的数组
 *  @param $encoding string 编码格式
 *  @param $root string 根节点名称
 *  @return string
 **/
function xmlByArray($data, $encoding = 'utf-8', $root = "xlphp") {
    $xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
    $xml.= '<' . $root . '>';
    $xml.= dataToXml($data);
    $xml.= '</' . $root . '>';
    return $xml;
}

function dataToXml($data) {
    if (is_object($data)) {
        $data = get_object_vars($data);
    }
    $xml = '';
    foreach ($data as $key => $val) {
        is_numeric($key) && $key = "item id=\"$key\"";
        $xml.="<$key>";
        $xml.= ( is_array($val) || is_object($val)) ? dataToXml($val) : $val;
        list($key, ) = explode(' ', $key);
        $xml.="</$key>";
    }
    return $xml;
}
