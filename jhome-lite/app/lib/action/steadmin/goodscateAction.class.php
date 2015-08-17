<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of goodscateAction
 * 商品类目管理
 * @author skyinter
 */
class goodscateAction extends commonAction {

    protected $rs = array();
    private $rootCateId = 2;

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
        parent::_authUser(array(1));
    }

    /**
     * 商品分类列表
     */
    public function index() {
        $page = $this->_getid('p', 1);
        $pid = $this->_getid('pid', 0);
        $keyword = $this->_get('keyword', '');
        $pageShow = 10000;
        $where = array('is_del' => 0, 'city_id' => steadmin::$adminInfo['city_id']);
        if ($keyword) {
            $where['LIKE'] = array('name' => $keyword);
        }
        if ($pid) {
            $where['pid'] = $pid;
        }
        V('tree/catetree');
        $rs = M('ste_goods_cate')->where($where)->order('sort DESC')->findAll();
        $total = M('ste_goods_cate')->getTotal();
        $group = M('ste_goods_cate')->select('id');
        $this->assign('list', TreeTool::tree($rs, $pid));
        $this->assign('total', $total);
        $this->assign('page', $page);
        $this->assign('keyword', $keyword);
        $this->assign('group', $group);
        $this->assign('pageShow', $pageShow);
        $this->display();
    }

    public function add() {
        $bootCate = array(array('id' => 2, 'name' => 'app在售类目'));
        $cate = M('ste_goods_cate')->field('id,name')->where(
                        array('is_del' => 0, 'city_id' => steadmin::$adminInfo['city_id'], 'pid' => $this->rootCateId))->order('`id`')->findAll(false);
        if ($cate) {
            $cate = array_merge($bootCate, $cate);
        } else {
            $cate = $bootCate;
        }
        $this->assign('cate', $cate);
        $this->display();
    }

    public function post() {
        $data = array(
            'name' => $this->_post('name'),
            'pid' => $this->_postid('pid', 0),
            'sort' => $this->_postid('sort', 0),
            'is_show' => $this->_postid('is_show', 0),
        );
        if (!$data['name']) {
            $this->JsonReturn('类别名称不能为空');
        }
        if (!$data['pid']) {
            $this->JsonReturn('请选择父类');
        }
        $data['city_id'] = steadmin::$adminInfo['city_id'];
        $result = M('ste_goods_cate')->insert($data);
        if ($result) {
            $this->reset();
        }
        $this->setGoodsCateCache();
        //===记录操作日志====
        parent::saveSySLog(1, $data, 0, array(), '商品类目-添加');
        //===记录操作日志====
        $this->JsonReturn('添加分类成功', null, 1);
    }

    /*
     * 编辑分类
     */

    public function edit() {
        $id = $this->_getid('id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_goods_cate')->where(array('id' => $id, 'city_id' => steadmin::$adminInfo['city_id']))->find();
        if (!$rs) {
            showError('类别不存在');
        }
        $bootCate = array(array('id' => 2, 'name' => 'app在售类目'));
        $cate = M('ste_goods_cate')->where(array('is_del' => 0, 'city_id' =>
                    steadmin::$adminInfo['city_id']))->order('`id`')->findAll(false);
        $mainCate = M('ste_goods_cate')->field('id,name')->where(array(
                            'is_del' => 0, 'city_id' => steadmin::$adminInfo['city_id'], 'pid' => $this->rootCateId))
                        ->order('`id`')->findAll(false);
        if ($mainCate) {
            $mainCate = array_merge($bootCate, $mainCate);
        } else {
            $mainCate = $bootCate;
        }
        V('tree/catetree');
        $ctree = TreeTool::tree($cate, $rs['id'], 'pid', 'id');
        //找到当前项目的父类
        $parentId = 0;
        if ($ctree) {
            foreach ($ctree as $val) {
                if ($val['pid'] == $this->rootCateId) {
                    $parentId = $val['id'];
                    break;
                }
            }
        }
        if ($rs['depth'] == $this->rootCateId + 1) {
            $parentId = $this->rootCateId;
        }
        $this->assign(array('rs' => $rs, 'cate' => $mainCate,
            'parentId' => $parentId,));
        $this->display();
    }

    /*
     * 保存
     */

    public function save() {
        $id = $this->_postid('id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_goods_cate')->where(array('id' => $id, 'city_id' => steadmin::$adminInfo['city_id']))->find();
        if (!$rs) {
            showError('类别不存在');
        }
        $objData = array(
            'name' => $this->_post('name'),
            'sort' => $this->_postid('sort', 0),
            'pid' => $this->_post('pid'),
            'is_show' => $this->_postid('is_show', 0),
        );
        if (!$objData['name']) {
            $this->JsonReturn('类别名称不能为空');
        }
        if (!$objData['pid']) {
            $this->JsonReturn('请选择父类');
        }
        if ($objData['pid'] == $id) {
            $this->JsonReturn('父类不能为自己');
        }
        M('ste_goods_cate')->update($objData, array('id' => $id));
        $this->reset();
        $this->setGoodsCateCache();
        //===记录操作日志====
        parent::saveSySLog(2, $objData, $id, array('id' => $id), '商品类目-编辑');
        //===记录操作日志====
        $this->JsonReturn('ok', null, 1);
    }

    /*
     * 删除
     */

    function delete() {
        $id = $this->_postid('id');
        if (!$id) {
            showError('参数丢失');
        }
        M('ste_goods_cate')->update(array('is_del' => 1, 'is_show' => 0), array('id' => $id, 'city_id' => steadmin::$adminInfo['city_id']));
        $this->reset();
        $this->setGoodsCateCache();
        //===记录操作日志====
        parent::saveSySLog(3, array('is_del' => 1, 'is_show' => 0), $id, array('id' => $id), '商品类目-删除');
        //===记录操作日志====
        $this->JsonReturn('ok', null, 1);
    }

    /*
     * 获取商品类别
     */

    public function cate() {
        $cateId = $this->_getid('cid', 0); //类别
        $myCateid = $this->_getid('cateid', 0); //类别
        if (!$cateId) {
            $this->JsonReturn('请先选择类别');
        }
        if ($myCateid) {
            $myCateid = M('ste_goods_cate')->where(array('id' => $myCateid))->getField('pid');
        }
        $cateList = M('ste_goods_cate')->where(array('city_id' =>
                    steadmin::$adminInfo['city_id'], 'is_del' => 0))->order('sort DESC')->select('id');
        if (!isset($cateList[$cateId])) {
            $this->JsonReturn('ok', '<option value="' . $cateId . '">一级类目</option>', 1);
        }
        $cates = D('tree')->getSubs($cateList, $cateId, false);
        if (!$cates['list']) {
            $this->JsonReturn('ok', '<option value="' . $cateList[$cateId]['id'] . '">' . $cateList[$cateId]['name'] . '</option>', 1);
        }
        V('tree/catetree');
        $cateStr = '<option value="' . $cateList[$cateId]['id'] . '">一级类目</option>' .
                D('tree')->genSelectOption(TreeTool::tree($cateList, $cateId), $myCateid, 4);
        $this->JsonReturn('ok', $cateStr, 1);
    }

    //重新处理类目表关系
    private function reset() {
        $this->rs = M('ste_goods_cate')->field('id,name,pid')->where(array('is_del' => 0))->select('id');
//        echo '共更新：' . M('ste_goods_cate')->getTotal() . ' 条数据<br/>';
        if ($this->rs) {
            foreach ($this->rs as $val) {
                $path = $this->getParent($val['id']);
                $pathStr = implode(',', $path);
                $pathCount = count($path) - 1;
                $update = array(
                    'path' => $pathStr,
                    'depth' => $pathCount
                );
                $status = M('ste_goods_cate')->update($update, array('id' => $val['id']));
//                echo '更新：' . $val['name'] . ' -path:' . $pathStr . '，depth:' . $pathCount . ' 状态:' . ($status ? 'OK' : '无改变') . ' <br/>';
            }
        }
        return true;
    }

    //递归获取父类
    private function getParent($id) {
        $ParentList = array(0);
        if (isset($this->rs[$id])) {
            if ($this->rs[$id]['pid']) {
                $ParentList = $this->getParent($this->rs[$id]['pid']);
            }
        }
        $ParentList[] = $id;
        return $ParentList;
    }

    /*
     * 生成菜单缓存
     */

    private function setGoodsCateCache() {
        $cate = M('ste_goods_cate')->field('id,name,sort,pid,path,depth')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'is_show' => 1, 'is_del' => 0))->order('sort DESC')->select('id');
        F('steward/goodscate_city_' . steadmin::$adminInfo['city_id'], $cate);
        return true;
    }

    /*
     * 获取对应类目信息
     */

    public function getgoodscatename() {
        $isAjax = $this->_getid('is_ajax', 0);
        $id = $this->_getid('id', 0);
        $rs = F('steward/goodscate_city_' . steadmin::$adminInfo['city_id']);
        if ($isAjax) {
            isset($rs[$id]) ? $this->JsonReturn('ok', $rs[$id], 1) : $this->JsonReturn('类目不存在');
        }
        return isset($rs[$id]) ? $rs[$id] : '类目不存在';
    }

}
