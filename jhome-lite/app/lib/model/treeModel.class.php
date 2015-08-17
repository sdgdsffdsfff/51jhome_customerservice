<?php

defined('IN_XLP') or exit('Access Denied');

/**
 * 生成树形
 */
class treeModel extends Model {

    private $idList = array();

    function __construct() {
        parent::__construct();
    }

    public function genSelectOption($tree, $selected = '', $down = 1) {
        static $options = '';
        foreach ($tree as $v) {
            $space = str_repeat('&emsp;&emsp;', ($v['depth'] - $down > 0 ? $v['depth'] - $down : 0));

            if ($selected == $v['id']) {
                $options .= "<option value='{$v['id']}' selected>$space{$v['name']}</option>";
            } else {
                $options .= "<option value='{$v['id']}'>$space{$v['name']}</option>";
            }

            if (isset($v['son'])) {
                $this->genSelectOption($v['son'], $selected);
            }
        }
        return $options;
    }

    //数组转树
    public function genTree($items) {
        $tree = array();
        foreach ($items as $v) {
            if (isset($items[$v['pid']])) {
                $items[$v['pid']]['son'][$v['id']] = &$items[$v['id']];
            } else {
                $tree[$v['id']] = &$items[$v['id']];
            }
        }
        return $tree;
    }

    /*
     * 从类目树中获取全部子类
     */

    public function getSubs(&$categorys, $catId = 0, $hasSelf = true, $level = 1) {
        $this->idList = array();
        $data = $this->getData($categorys, $catId, $hasSelf, $level);
        if ($data['items']) {
            $res = array();
            foreach ($data['items'] as $val) {
                $res[$val['id']] = $val;
            }
            $data['items'] = $res;
        }
        return $data;
    }

    private function getData($categorys, $catId = 0, $hasSelf = true, $level = 1) {
        $subs = array();
        if ($categorys) {
            foreach ($categorys as $item) {
                if ($hasSelf && $item['id'] == $catId) {
                    $item['level'] = $level;
                    $subs[] = $item;
                    $this->idList[] = $item['id'];
                }
                if ($item['pid'] == $catId) {
                    $item['level'] = $level + 1;
                    $subs[] = $item;
                    $this->idList[] = $item['id'];
                    $arr = $this->getData($categorys, $item['id'], false, $level + 1);
                    $subs = array_merge($subs, $arr['items']);
                }
            }
        }
        return array('list' => $this->idList, 'items' => $subs);
    }

}
