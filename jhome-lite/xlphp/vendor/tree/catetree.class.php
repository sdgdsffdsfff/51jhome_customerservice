<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

class TreeTool {

    static public $treeList = array();

    /**
     * 无限级分类
     * @access public 
     * @param Array $data     //结果集 
     * @param Int $pid             
     * @param Int $count       //第几级分类
     * @return Array $treeList   
     */
    static public function tree(&$data, $pid = 0, $id = 'id', $pname = 'pid', $count = 1) {
        foreach ($data as $value) {
            if ($value[$pname] == $pid) {
                $value['count'] = $count;
                self::$treeList [] = $value;
//                unset($data[$key]);
                self::tree($data, $value[$id], $id, $pname, $count + 1);
            }
        }
        return self::$treeList;
    }

    /**
     * 得到父级数组
     * @param $data array 数据源
     * @param $myid int   当前类别ID
     * @param $id string 数据主键名
     * @param $pname string 父键名称
     * @return array
     */
    static public function getParent(&$data, $myid, $id = 'id', $pname = 'pid') {
        $newarr = array();
        if (!isset($data[$myid])) {
            return $newarr;
        }
        $pid = $data[$myid][$pname];
        if (is_array($data) && $pid) {
            foreach ($data as $catid => $a) {
                if ($a[$id] == $pid) {
                    $newarr[$catid] = $a;
                }
            }
            $newarr = array_merge($newarr, self::getParent($data, $pid, $id, $pname));
        }
        return $newarr;
    }

}
