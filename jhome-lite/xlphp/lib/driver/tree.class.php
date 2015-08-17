<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * 通用的树型类，可以生成任何树型结构
 */
class Mytree {

    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public $arr = array();
    public $showAll = false; //是否显示联级菜单

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    public $icon = array('│', '├', '└');
    public $nbsp = "&nbsp;";

    /**
     * @access private
     */
    public $ret = '';

    /**
     * 构造函数，初始化类
     * @param array 2维数组
     */
    public function __construct($arr = array()) {
        $this->arr = $arr;
        $this->ret = '';
        return is_array($arr);
    }

    /**
     * 得到父级数组
     * @param int
     * @return array
     */
    public function getParent($myid) {
        $newarr = array();
        if (!isset($this->arr[$myid]))
            return false;
        $pid = $this->arr[$myid]['fid'];
        //$pid = $this->arr[$pid]['fid'];
        if (is_array($this->arr) && $pid) {
            foreach ($this->arr as $catid => $a) {
                if ($a['catid'] == $pid)
                    $newarr[$catid] = $a;
            }
            if ($this->showAll)
                $newarr = array_merge($newarr, $this->getParent($pid));
        }
        return $newarr;
    }

    /**
     * 得到子级数组
     * @param int
     * @return array
     */
    public function getChild($myid) {
        $a = $newarr = $px = $newarr_tmp = array();
        if (is_array($this->arr)) {
            foreach ($this->arr as $catid => $a) {
                if ($a['fid'] == $myid) {
                    $newarr[$catid] = $a;
                    $px[$catid] = $a['px'];
                }
            }
            array_multisort($px, SORT_DESC, SORT_NUMERIC, $newarr);
            foreach ($newarr as $a) {
                $newarr_tmp[$a['catid']] = $a;
            }
            $newarr = $newarr_tmp;
        }
        return $newarr ? $newarr : false;
    }

    /*
      返回指定id的属性值
     */

    public function getVal($myid, $t = 't_title') {
        if (!isset($this->arr[$myid][$t]))
            return false;
        return $this->arr[$myid][$t];
    }

    /* 取出指定ID的全部子菜单 */

    public function getChildId($myid, $self = true) {
        $idList = '';
        if (!isset($this->arr[$myid]))
            return false;
        $idList = $this->getArray($myid);
        if ($idList)
            $idList = array_keys($idList);
        if ($self) {
            $idList[] = $myid;
        }
        return $idList;
    }

    /*
      取出指定类别的菜单项
     */

    public function setType($type, $t = 'menu_id') {
        $arr_tmp = '';
        foreach ($this->arr as $key => $val) {
            if ($val[$t] == $type)
                $arr_tmp[$key] = $val;
        }
        $this->arr = $arr_tmp;
        return true;
    }

    /**
     * 得到当前位置数组
     * @param int
     * @return array
     */
    public function getPos($myid, &$newarr = '') {
        $a = array();
        if (!isset($this->arr[$myid]))
            return false;
        $newarr[] = $this->arr[$myid];
        $pid = $this->arr[$myid]['fid'];
        if (isset($this->arr[$pid])) {
            $this->getPos($pid, $newarr);
        }
        if (is_array($newarr)) {
            krsort($newarr);
            foreach ($newarr as $v) {
                $a[$v['catid']] = $v;
            }
        }
        return $a;
    }

    /**
     * 得到树型结构
     * @param int ID，表示获得这个ID下的所有子级
     * @param string 生成树型结构的基本代码，例如："<option value=\$catid \$selected>\$spacer\$t_title</option>"
     * @param int 被选中的ID，比如在做树型下拉框的时候需要用到
     * @return string
     */
    public function getTree($myid, $str = "<option value=\$catid \$selected>\$spacer\$t_title</option>", $sid = 0, $adds = '', $str_group = '') {
        $number = 1;
        $child = $this->getChild($myid);
        if (is_array($child)) {
            $total = count($child);
            foreach ($child as $catid => $value) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds . $j : '';
                $selected = $sid && ($catid == $sid) ? 'selected' : '';
                @extract($value);
                $disabled = $fid ? '' : 'disabled';
                $fid == 0 && $str_group ? eval("\$nstr = \"$str_group\";") : eval("\$nstr = \"$str\";");
                $this->ret .= $nstr;
                $nbsp = $this->nbsp;
                $this->getTree($catid, $str, $sid, $adds . $k . $nbsp, $str_group);
                $number++;
            }
        }
        return $this->ret;
    }

    /**
     * 同上一方法类似,但允许多选
     */
    public function getTreeMulti($myid, $str, $sid = 0, $adds = '') {
        $number = 1;
        $child = $this->getChild($myid);
        if (is_array($child)) {
            $total = count($child);
            foreach ($child as $catid => $a) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds . $j : '';

                $selected = $this->have($sid, $catid) ? 'selected' : '';
                @extract($a);
                eval("\$nstr = \"$str\";");
                $this->ret .= $nstr;
                $this->getTreeMulti($catid, $str, $sid, $adds . $k . '&nbsp;');
                $number++;
            }
        }
        return $this->ret;
    }

    /**
     * @param integer $myid 要查询的ID
     * @param string $str   第一种HTML代码方式
     * @param string $str2  第二种HTML代码方式
     * @param integer $sid  默认选中
     * @param integer $adds 前缀
     */
    public function getTreeCategory($myid, $str, $str2, $sid = 0, $adds = '') {
        $number = 1;
        $child = $this->getChild($myid);
        if (is_array($child)) {
            $total = count($child);
            foreach ($child as $catid => $a) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds . $j : '';

                $selected = $this->have($sid, $catid) ? 'selected' : '';
                @extract($a);
                if (empty($html_disabled)) {
                    eval("\$nstr = \"$str\";");
                } else {
                    eval("\$nstr = \"$str2\";");
                }
                $this->ret .= $nstr;
                $this->getTreeCategory($catid, $str, $str2, $sid, $adds . $k . '&nbsp;');
                $number++;
            }
        }
        return $this->ret;
    }

    /**
     * 同上一类方法，jquery treeview 风格，可伸缩样式（需要treeview插件支持）
     * @param $myid 表示获得这个ID下的所有子级
     * @param $effected_id 需要生成treeview目录数的id
     * @param $str 末级样式
     * @param $str2 目录级别样式
     * @param $showlevel 直接显示层级数，其余为异步显示，0为全部限制
     * @param $style 目录样式 默认 filetree 可增加其他样式如'filetree treeview-famfamfam'
     * @param $currentlevel 计算当前层级，递归使用 适用改函数时不需要用该参数
     * @param $recursion 递归使用 外部调用时为FALSE
     */
    function getTreeView($myid, $effected_id = 'example', $str = "<span class='file'>\$t_title</span>", $str2 = "<span class='folder'>\$t_title</span>", $showlevel = 0, $style = 'filetree ', $currentlevel = 1, $recursion = FALSE) {
        $child = $this->getChild($myid);
        if (!defined('EFFECTED_INIT')) {
            $effected = ' id="' . $effected_id . '"';
            define('EFFECTED_INIT', 1);
        } else {
            $effected = '';
        }
        $placeholder = '<ul><li><span class="placeholder"></span></li></ul>';
        if (!$recursion)
            $this->str .='<ul' . $effected . '  class="' . $style . '">';
        foreach ($child as $catid => $a) {
            @extract($a);
            if ($showlevel > 0 && $showlevel == $currentlevel && $this->getChild($catid))
                $folder = 'hasChildren'; //如设置显示层级模式@2011.07.01
            $floder_status = isset($folder) ? ' class="' . $folder . '"' : '';
            $this->str .= $recursion ? '<ul><li' . $floder_status . ' id=\'' . $catid . '\'>' : '<li' . $floder_status . ' id=\'' . $catid . '\'>';
            $recursion = FALSE;
            if ($this->getChild($catid)) {
                eval("\$nstr = \"$str2\";");
                $this->str .= $nstr;
                if ($showlevel == 0 || ($showlevel > 0 && $showlevel > $currentlevel)) {
                    $this->getTreeView($catid, $effected_id, $str, $str2, $showlevel, $style, $currentlevel + 1, TRUE);
                } elseif ($showlevel > 0 && $showlevel == $currentlevel) {
                    $this->str .= $placeholder;
                }
            } else {
                eval("\$nstr = \"$str\";");
                $this->str .= $nstr;
            }
            $this->str .=$recursion ? '</li></ul>' : '</li>';
        }
        if (!$recursion)
            $this->str .='</ul>';
        return $this->str;
    }

    /**
     * 获取子栏目json
     * Enter description here ...
     * @param unknown_type $myid
     */
    public function creatSubJson($myid = 0, $str = '') {
        $sub_cats = $this->getChild($myid);
        $n = 0;
        if (is_array($sub_cats))
            foreach ($sub_cats as $c) {
                if ($this->getChild($c['catid'])) {
                    $data[$n]['liclass'] = 'hasChildren';
                    $data[$n]['children'] = array(array('text' => '&nbsp;', 'classes' => 'placeholder'));
                    $data[$n]['classes'] = 'folder';
                    $data[$n]['text'] = $c['t_title'];
                } else {
                    if ($str) {
                        eval("\$data[$n]['text'] = \"$str\";");
                    } else {
                        $data[$n]['text'] = $c['t_title'];
                    }
                }
                $n++;
            }
        return json_encode($data);
    }

    private function have($list, $item) {
        return(strpos(',,' . $list . ',', ',' . $item . ','));
    }

    /**
      +------------------------------------------------
     * 格式化数组
      +------------------------------------------------
     * @author yangyunzhou@foxmail.com
      +------------------------------------------------
     */
    function getArray($myid = 0, $sid = 0, $adds = '') {
        $number = 1;
        $child = $this->getChild($myid);
        if (is_array($child)) {
            $total_child = count($child);
            foreach ($child as $catid => $a) {
                $j = $k = '';
                if ($number == $total_child) {
                    $j = $this->icon[2];
                } else {
                    $j = $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds . $j : $j;
                @extract($a);
                $a['t_title'] = $spacer . ' ' . $a['t_title'];
                $this->ret[$a['catid']] = $a;
                $fd = $adds . $k . '&nbsp;&nbsp;';
                $this->getArray($catid, $sid, $fd);
                $number++;
            }
        }

        return $this->ret;
    }

}
