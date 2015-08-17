<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of shopAction
 * 店铺管理
 * @author skyinter
 */
class shopAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
        $this->steSetting = C('steward/setting');
        $this->serviceData = parent::getServiceCache();
        $this->adminData = C('steward/admin');
        $this->steSetting['status'][1] = '<span class="red">是</span>';
    }

    /**
     * 列表
     */
    public function index() {
        parent::_authUser(array(1, 2, 5, 7, 8, 9));
        $title = $this->_get('q'); //关键字搜索
        $areaId = $this->_getid('area_id', 0); //城区
        $serviceId = $this->_getid('service_id', 0); //商圈
        $shopType = $this->_getid('shop_type', 0); //店铺类型
        $tips = $this->_getid('tips', 0); //参与活动
        $status = $this->_getid('status', 2); //状态
        $p = $this->_getid('p', 1);
        $where = array('AND' => array('city_id' => steadmin::$adminInfo['city_id']));
        if ($title) {
            $where['LIKE'] = array('shop_name' => parent::safeSearch($title));
        }
        if ($tips) {
            $where['LIKE'] = array('tips_list' => ',' . ($tips - 1) . ',');
        }
        if ($areaId) {
            $where['AND']['area_id'] = $areaId;
        }
        if ($serviceId) {
            $where['AND']['service_id'] = $serviceId;
        }
        if ($shopType) {
            $where['AND']['shop_type'] = $shopType - 1;
        }
        if ($status) {
            $where['AND']['status'] = ($status - 1);
        }
        //DOTO:后期可能会让另外的帐号也可以来管理这个店（比喻品牌创建了多个分店，那么品牌可以管理所有的分店，分店也可以管理自己的店），那么需要额外指定管理权限
        if (!parent::_checkIsAdmin()) {
            if (steadmin::$adminInfo['groupid'] == 8) {//店长
                $where['AND']['shop_id'] = steadmin::$adminInfo['shop_id'];
            } elseif (parent::_checkIsPresident()) {
                $where['AND']['service_id'] = steadmin::$adminInfo['service_id'];
            } else {
                $where['AND']['user_id'] = steadmin::$adminInfo['user_id'];
            }
        }
        $rs = M('ste_shop')->where($where)->page($p)->order('shop_id DESC')->findAll();
        $total = M('ste_shop')->getTotal();
//        z(M('ste_shop')->getAllSql());
//        z($rs);
        if ($rs) {
            foreach ($rs as $k => $v) {
                $rs[$k]['userName'] = parent::_getAdminName($v['user_id']);
                $rs[$k]['tipsList'] = implode('，', parent::_getTipsGroupList($this->steSetting['shop_tips'], $v['tips_list']));
                $rs[$k]['areaName'] = parent::getAreaName($v['area_id']);
                $rs[$k]['serviceName'] = isset($this->serviceData[$v['service_id']]) ? $this->serviceData[$v['service_id']]['stitle'] : '';
                $rs[$k]['stime'] = date('H:i', strtotime($v['stime']));
                $rs[$k]['etime'] = date('H:i', strtotime($v['etime']));
            }
        }
        $this->assign(array(
            'adminData' => $this->adminData,
            'service' => $this->serviceData,
            'area' => M('area')->field('aid,name')->where(
                    array('city_id' => steadmin::$adminInfo['city_id'], 'area_level' => 2))->findAll(false),
            'rs' => $rs, 'total' => $total, 'p' => $p,
            'area_id' => $areaId, 'service_id' => $serviceId,
            'shop_type' => $shopType,
            'title' => $title,
            'tips_id' => $tips,
            'status' => $status,
            'setting' => $this->steSetting));
        $this->display();
    }

    /**
     * 添加
     */
    public function add() {
        parent::_authUser(array(1, 5, 7));
        if (!parent::_checkIsAdmin()) {
            $maxShop = C('steward/admin', 'add_shop');
            if (!$maxShop[steadmin::$adminInfo['groupid']]) {
                showError('抱歉，用户组不能创建店铺');
            }
            if (M('ste_shop')->where(array('user_id' => steadmin::$adminInfo['user_id']))->count() >= $maxShop[steadmin::$adminInfo['groupid']]) {
                showError('抱歉，你最多只能创建' . $maxShop[steadmin::$adminInfo['groupid']] . '个店铺');
            }
        }
        $area = M('area')->field('aid,name')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'area_level' => 2))->findAll(false);
        $this->assign(array(
            'adminData' => $this->adminData,
            'service' => $this->serviceData,
            'setting' => $this->steSetting, 'area' => $area,
        ));
        $this->display();
    }

    /*
     * 保存
     */

    public function post() {
        parent::_authUser(array(1, 5, 7));
        if (!parent::_checkIsAdmin()) {
            $maxShop = C('steward/admin', 'add_shop');
            if (!$maxShop[steadmin::$adminInfo['groupid']]) {
                showError('抱歉，用户组不能创建店铺');
            }
            if (M('ste_shop')->where(array('user_id' => steadmin::$adminInfo['user_id']))->count() >= $maxShop[steadmin::$adminInfo['groupid']]) {
                showError('抱歉，你最多只能创建' . $maxShop[steadmin::$adminInfo['groupid']] . '个店铺');
            }
        }
        $objData = array();
        //获取字段内容
        $fieldList = M('ste_shop')->getTableFields();
        foreach ($fieldList['fields'] as $key => $val) {
            $objData[$key] = $val['type'] == 'int' ? $this->_postid($key, $val['value']) : $this->_post($key, $val['value']);
        }
        $objData['city_id'] = steadmin::$adminInfo['city_id'];
        $objData['user_id'] = steadmin::$adminInfo['user_id'];
        $objData['tips_list'] = $this->_post('tips_list');
        if ($objData['tips_list']) {
            $objData['tips_list'] = ',' . implode(',', $objData['tips_list']) . ',';
        }
        if ($objData['deployment_users']) {
            $objData['deployment_users'] = implode(',', $objData['deployment_users']);
        }
        //内容规则检查
        T('content/validate');
        $validate = array(
            array('shop_name', 'range_length', '店铺名称不符合要求', 2, 80),
            array('pic_url', 'required', '店铺小图不能为空'),
            array('score_total', 'double', '综合评分不符合要求'),
            array('score_flavour', 'double', '口味评分不符合要求'),
            array('score_service', 'double', '服务评分不符合要求'),
            array('ontime_point', 'int', '准点率必须为整数'),
        );
        if (!validate::check($validate, $objData)) {
            $this->JsonReturn(validate::getError());
        }
        //判断重复
        if (M('ste_shop')->where(array('shop_name' => $objData['shop_name'],
                    'shop_alt_name' => $objData['shop_alt_name'], 'city_id' => $objData['city_id'], 'status' => 1))->find()) {
            $this->JsonReturn('同名店铺已存在，请检查');
        }
//        z($objData);
        $id = M('ste_shop')->insert($objData);
        D('api')->refreshConfig();
        //===记录操作日志====
        parent::saveSySLog(1, $objData, 0, array(), '店铺管理-添加');
        //===记录操作日志====
        $this->JsonReturn('ok', $id, 1);
    }

    /**
     * 编辑
     */
    public function edit() {
        parent::_authUser(array(1, 5, 7, 9));
        $id = $this->_getid('id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_shop')->where(array('shop_id' => $id))->find();
        if (!$rs) {
            showError('店铺不存在');
        }
        if (!parent::_checkUser($rs['user_id'], $id) &&
                (parent::_checkIsPresident() && $rs['service_id'] != steadmin::$adminInfo['service_id'])) {
            showError('编辑权限不足');
        }
        $rs['tips_list'] = explode(',', trim($rs['tips_list'], ','));
        $rs['deployment_users'] = explode(',', trim($rs['deployment_users'], ','));
        $rs['hot_goods'] = $rs['hot_goods'] ? explode(',', $rs['hot_goods']) : array();
        $area = M('area')->field('aid,name')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'area_level' => 2))->findAll(false);
        $this->assign(array(
            'adminData' => $this->adminData,
            'rs' => $rs,
            'service' => $this->serviceData,
            'setting' => $this->steSetting, 'area' => $area,
            'goods' => $this->_getHotGoods($id, 20)
        ));
//        z($this->tVar);
        $this->display();
    }

    /*
     * 详细
     */

    public function detail() {
        parent::_authUser(array(1, 5, 7, 9));
        $id = $this->_getid('id', 0);
        $isAjax = $this->_getid('is_ajax', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_shop')->where(array('shop_id' => $id))->find();
        if (!$rs) {
            showError('店铺不存在');
        }
        if (!parent::_checkUser($rs['user_id'], $id) &&
                (parent::_checkIsPresident() && $rs['service_id'] != steadmin::$adminInfo['service_id'])) {
            showError('查看权限不足');
        }
        $rs['tips_list'] = explode(',', trim($rs['tips_list'], ','));
        $rs['deployment_users'] = explode(',', trim($rs['deployment_users'], ','));
        $rs['hot_goods'] = $rs['hot_goods'] ? explode(',', $rs['hot_goods']) : array();
        $list = M('area')->field('aid,name')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'area_level' => 2))->findAll(false);
        foreach ($list as $key => $val) {
            $area[$val['aid']] = $val['name'];
        }
        foreach ($this->serviceData as $key => $val) {
            $service[$val['sid']] = $val['stitle'];
        }
        $arr = array();
        foreach ($rs['hot_goods'] as $key => $val) {
            $arr[] = $this->getGoodsName($val);
        }
        $rs['hot_goods'] = $arr;
        if ($isAjax) {
            $this->JsonReturn('ok', $rs, 1);
        }
        $this->assign(array(
            'adminData' => $this->adminData,
            'rs' => $rs,
            'service' => $service,
            'setting' => $this->steSetting,
            'area' => $area,
        ));
        //   z($this->tVar);
        $this->display();
    }

    /*
     * 更新
     */

    public function save() {
        parent::_authUser(array(1, 5, 7, 9));
        $id = $this->_postid('id', 0);
        if (!$id) {
            $this->JsonReturn('参数丢失');
        }
        $rs = M('ste_shop')->where(array('shop_id' => $id))->find();
        if (!$rs) {
            showError('店铺不存在');
        }
        if (!parent::_checkUser($rs['user_id'], $id) &&
                (parent::_checkIsPresident() && $rs['service_id'] != steadmin::$adminInfo['service_id'])) {
            showError('编辑权限不足');
        }
        $objData = array();
        $noField = array('user_id', 'city_id', 'goods_sales');
        //获取字段内容
        $fieldList = M('ste_shop')->getTableFields($noField);
        foreach ($fieldList['fields'] as $key => $val) {
            $objData[$key] = $val['type'] == 'int' ? $this->_postid($key, $val['value']) : $this->_post($key, $val['value']);
        }
        $objData['tips_list'] = $this->_post('tips_list');
        $objData['hot_goods'] = $this->_post('hot_goods');
        if ($objData['tips_list']) {
            $objData['tips_list'] = ',' . implode(',', $objData['tips_list']) . ',';
        }
        if ($objData['hot_goods']) {
            $objData['hot_goods'] = implode(',', $objData['hot_goods']);
        }
        if ($objData['deployment_users']) {
            $objData['deployment_users'] = implode(',', $objData['deployment_users']);
        }
        //内容规则检查
        T('content/validate');
        $validate = array(
            array('shop_name', 'range_length', '店铺名称不符合要求', 2, 80),
            array('pic_url', 'required', '店铺小图不能为空'),
            array('score_total', 'double', '综合评分不符合要求'),
            array('score_flavour', 'double', '口味评分不符合要求'),
            array('score_service', 'double', '服务评分不符合要求'),
            array('ontime_point', 'int', '准点率必须为整数')
        );
        if (!validate::check($validate, $objData)) {
            $this->JsonReturn(validate::getError());
        }
//        z($objData);
        //判断重复
        $checkHas = M('ste_shop')->where(array('shop_name' => $objData['shop_name'],
                    'shop_alt_name' => $objData['shop_alt_name'], 'city_id' => steadmin::$adminInfo['city_id'], 'status' => 1))->getField('shop_id');
        if ($checkHas && $checkHas != $id) {
            $this->JsonReturn('同名店铺已存在，请检查');
        }
//        z($objData);
        M('ste_shop')->update($objData, array('shop_id' => $id));
        //判断店铺的服务中心有没有更改
        if ($rs['service_id'] != $objData['service_id']) {
            M('ste_goods')->update(array('service_id' => $objData['service_id']), array('shop_id' => $id));
        }
        //判断店铺的类型是否有更改
        if ($rs['shop_type'] != $objData['shop_type']) {
            D('api')->refreshConfig();
        }
        
        //===记录操作日志====
        parent::saveSySLog(2, $objData, $id, array('shop_id' => $id), '店铺管理-更新');
        //===记录操作日志====
        $this->JsonReturn('ok', null, 1);
    }

    /*
     * 删除
     */

    public function delete() {
        parent::_authUser(array(1));
        $id = $this->_postid('id');
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_shop')->where(array('shop_id' => $id))->find();
        if (!$rs) {
            showError('店铺不存在');
        }
        if (!parent::_checkUser($rs['user_id'], $id)) {
            showError('编辑权限不足');
        }
        M('ste_shop')->update(array('status' => 0), array('shop_id' => $id, 'city_id' => steadmin::$adminInfo['city_id']));
        M('ste_goods')->update(array('status' => 0), array('shop_id' => $id));
        //DOTO:清除相关数据
        //===记录操作日志====
        parent::saveSySLog(3, array('status' => 0), $id, array('shop_id' => $id), '店铺管理-删除');
        //===记录操作日志====
        $this->JsonReturn('ok', null, 1);
    }

    /*
     * 处理状态
     */

    public function deal() {
        parent::_authUser(array(1, 5, 7, 9));
        $id = $this->_postid('id', 0);
        $act = $this->_postid('act', 0);
        $action = $this->_post('action', '');
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_shop')->where(array('shop_id' => $id, 'city_id' => steadmin::$adminInfo['city_id']))->find();
        if (!$rs) {
            showError('店铺不存在');
        }
        if (!parent::_checkUser($rs['user_id'], $id) &&
                (parent::_checkIsPresident() && $rs['service_id'] != steadmin::$adminInfo['service_id'])) {
            showError('编辑权限不足');
        }
        if ($action) {
            if (in_array($action, array('up', 'down'))) {
                if ($action == 'up') {
                    $count = M('ste_goods')->update(array('status' => 1), array('status' => 0, 'shop_id' => $id));
                } else {
                    $count = M('ste_goods')->update(array('status' => 0), array('status' => 1, 'shop_id' => $id));
                }
                //===记录操作日志====
                parent::saveSySLog(4, array('status' => $action, 'table' => 'goods'), $id, array('shop_id' => $id), '店铺管理-更新店铺商品状态');
                //===记录操作日志====
                $this->JsonReturn('ok', $count, 1);
            } else {
                $this->JsonReturn('参数错误');
            }
        } else {
            if (!isset($this->steSetting['shop_status'][$act])) {
                showError('参数错误');
            }
            M('ste_shop')->update(array('status' => $act), array('shop_id' => $id));
            //===记录操作日志====
            parent::saveSySLog(4, array('status' => $act), $id, array('shop_id' => $id), '店铺管理-处理状态');
            //===记录操作日志====
            $this->JsonReturn('ok', $this->steSetting['shop_status'][$act], 1);
        }
    }

    /*
     * 半成品菜报表
     */

    public function report() {
        $startTime = $this->_get('stime', ''); //开始时间
        $endTime = $this->_get('etime', ''); //结束时间
        $serviceId = $this->_getid('service_id', 0); //服务社
        $isOutput = $this->_getid('is_output', 0); //是否导出数据
        $cateId = $this->_getid('cid', 0); //类目
        $cateList = array(275 => '半成品菜', 273 => '预定下午茶');
        if (!isset($cateList[$cateId])) {
            showError('抱歉，该类目不允许查看');
        }
        if (!parent::_checkIsAdmin()) {
            if ($cateId != 275 || steadmin::$adminInfo['user_id'] != 291) {//半成品菜店长
                showError('抱歉，暂无权限');
            }
        }
        //取类目及所有子类目
        $cateAllList = M('ste_goods_cate')->where(array('city_id' =>
                    steadmin::$adminInfo['city_id'], 'is_del' => 0))->order('sort DESC')->select('id');
        $tidList = D('tree')->getSubs($cateAllList, $cateId, true);
        $cateId = $tidList['list'] ? implode(',', $tidList['list']) : $cateId;
        //默认报表时间为今天
        $today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $stime = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
        if (!$startTime) {
            $startTime = date('Y-m-d', $stime);
        }
        if (!$endTime) {
            $endTime = date('Y-m-d', $stime);
        }
        if ($startTime && $endTime) {
            $startTime = inTime($startTime);
            $endTime = inTime($endTime) + 60 * 60 * 24 - 1;
        }
        if ($serviceId) {
            $where = ' AND a.service_id=' . $serviceId;
            $sWhere = ' AND service_id=' . $serviceId;
        } else {
            $where = '';
            $sWhere = '';
        }
        if ($startTime < $today) {
            $statusList = '1,3,4,5,6,7,8,9,10,11';
        } else {
            $statusList = '1,3,4,5,6,7,8,9,10,11'; //搜索状态：已支付、已审核、正在配货
        }
        //根据类目搜索所有的商品gid
        $where = '';
        $rs = M('ste_order')->query('SELECT a.order_id,a.order_sn,a.order_type,a.service_id,a.village_id,a.uid,a.address,a.phone,a.username,'
                . 'a.desc,a.arrive_date,a.arrive_time,a.order_source,a.status,a.order_time,a.goods_counts AS goods_total,b.gid,b.shop_id,b.goods_name,b.goods_counts,b.goods_price'
                . ' FROM __TABLE__ AS a LEFT JOIN __PRE__ste_order_goods AS b ON a.order_id=b.order_id WHERE a.arrive_date>=' . $startTime . ' AND a.arrive_date<=' . $endTime . ' AND a.status IN (' . $statusList . ') AND b.gid IN (
            SELECT gid FROM __PRE__ste_goods WHERE cate_id IN (' . $cateId . ') ' . $sWhere . '
            )' . $where . ' ORDER BY service_id ASC');
//        z(M('ste_order')->getAllSql());
//        z($rs);
        $orderList = array();
        $orderTotal = array();
        $serviceList = array();
        if ($rs) {
            foreach ($rs as $val) {
                //统计各个菜的数量
                $goodsName = md5($val['goods_name']);
                if (!isset($orderTotal[$goodsName])) {
                    $orderTotal[$goodsName] = array(
                        'name' => $val['goods_name'],
                        'counts' => $val['goods_counts'],
                        'item' => array()
                    );
                } else {
                    $orderTotal[$goodsName]['counts']+=$val['goods_counts'];
                }
                if (!isset($orderTotal[$goodsName]['item'][$val['service_id']])) {
                    $orderTotal[$goodsName]['item'][$val['service_id']] = array(
                        'name' => $this->serviceData[$val['service_id']]['stitle'],
                        'counts' => $val['goods_counts']
                    );
                } else {
                    $orderTotal[$goodsName]['item'][$val['service_id']]['counts']+=$val['goods_counts'];
                }
                //按服务社统计菜单
                if (!isset($serviceList[$val['service_id']])) {
                    $serviceList[$val['service_id']] = array(
                        'service_name' => $this->serviceData[$val['service_id']]['stitle'],
                        'counts' => $val['goods_counts'],
                        'item' => array()
                    );
                } else {
                    $serviceList[$val['service_id']]['counts']+=$val['goods_counts'];
                }
                if (!isset($serviceList[$val['service_id']]['item'][$val['gid']])) {
                    $serviceList[$val['service_id']]['item'][$val['gid']] = array(
                        'name' => $val['goods_name'],
                        'counts' => $val['goods_counts']
                    );
                } else {
                    $serviceList[$val['service_id']]['item'][$val['gid']]['counts']+=$val['goods_counts'];
                }
                //按订单号归类商品
                if (!isset($orderList[$val['order_id']])) {
                    $orderList[$val['order_id']] = array(
                        'order_id' => $val['order_id'],
                        'order_sn' => $val['order_sn'],
                        'order_type' => $val['order_type'],
                        'service_id' => $val['service_id'],
                        'service_name' => $this->serviceData[$val['service_id']]['stitle'],
                        'village_id' => $val['village_id'],
                        'village_name' => parent::getVillageName($val['village_id']),
                        'address' => $val['address'],
                        'phone' => $val['phone'],
                        'username' => $val['username'],
                        'desc' => $val['desc'],
                        'arrive_date' => $val['arrive_date'],
                        'arrive_time' => $val['arrive_time'],
                        'order_source' => $val['order_source'],
                        'status' => $val['status'],
                        'order_time' => $val['order_time'],
                        'goods_total' => $val['goods_total'],
                        'select_goods_total' => 0,
                        'list' => array()
                    );
                }
                $orderList[$val['order_id']]['list'][] = array(
                    'goods_name' => $val['goods_name'],
                    'goods_counts' => $val['goods_counts'],
                    'goods_price' => $val['goods_price'],
                );
                $orderList[$val['order_id']]['select_goods_total']+=$val['goods_counts'];
            }
        }
//        z($orderTotal);
//        z($serviceList);
        if ($isOutput) {
            $dataList = array();
            $title = array('订单号', '收货人', '手机', '地址', '配送时间', '商品列表', '留言', '混合订单');
            foreach ($orderList as $val) {
                $list = array();
                foreach ($val['list'] as $v) {
                    $list[] = $v['goods_name'] . ' (x ' . $v['goods_counts'] . ')';
                }
                $dataList[] = array(
                    $val['order_sn'],
                    $val['username'],
                    $val['phone'],
                    $val['address'],
                    (outTime($val['arrive_date'], 2) . ' ' . $val['arrive_time']),
                    implode("\r\n", $list),
                    $val['desc'],
                    ($val['goods_total'] == $val['select_goods_total'] ? '否' : '是')
                );
            }
            if (isset($this->serviceData[$serviceId])) {
                $tname = $this->serviceData[$serviceId]['stitle'];
            } else {
                $tname = '全部';
            }
            $dataList[] = array('', '', '', '', '', '', '', '');
            $dataList[] = array('', '', '', '', '', '', '', '');
            $dataList[] = array('品类', '详细', '数量', '', '', '', '', '');
            //将统计信息附加到报表中
            foreach ($orderTotal as $val) {
                $list = array();
                foreach ($val['item'] as $v) {
                    $list[] = $v['name'] . ' (' . $v['counts'] . '份)';
                }
                $dataList[] = array($val['name'], implode('、', $list), $val['counts'], '', '', '', '', '');
            }
            $dataList[] = array('', '', '', '', '', '', '', '');
            $dataList[] = array('', '', '', '', '', '', '', '');
            $dataList[] = array('服务社', '详细', '数量', '', '', '', '', '');
            //将统计信息附加到报表中
            foreach ($serviceList as $val) {
                $list = array();
                foreach ($val['item'] as $v) {
                    $list[] = $v['name'] . ' (' . $v['counts'] . '份)';
                }
                $dataList[] = array($val['service_name'], implode("\r\n", $list), $val['counts'], '', '', '', '', '');
            }
            load('csv');
            $csv = new csv();
            $csv->write($title, $dataList, $cateList[$cateId] . '订单_' . $tname . date('Y-m-d H/i/s'));
        } else {
            $this->assign(array('rs' => $orderList,
                'orderTotal' => $orderTotal,
                'cid' => $cateId,
                'service_id' => $serviceId,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'serviceList' => $serviceList,
                'service' => $this->serviceData,
                'setting' => $this->steSetting));
            $this->display();
        }
    }

    /*
     * 获取店铺的热门商品
     */

    private function _getHotGoods($shop_id, $limit = 20) {
        return M('ste_goods')->field('gid,goods_name')->where(array('shop_id' => $shop_id, 'status' => 1))->limit($limit)->findAll(false);
    }

    private function getGoodsName($gid) {
        $rs = M('ste_goods')->field('goods_name')->where(array('gid' => $gid))->find();
        return $rs['goods_name'];
    }

    /*
     * 获取区域配货员
     */

//    private function _getDeploymentList() {
//        if (parent::_checkIsAdmin() || steadmin::$adminInfo['groupid'] == 7) {
//            $where = array('city_id' => steadmin::$adminInfo['city_id']);
//        } else {
//            $where = array('service_id' => steadmin::$adminInfo['service_id']);
//        }
//        $where['groupid'] = 3;
//        $where['status'] = 1;
//        return M('ste_user')->field('user_id,username,real_name')->where($where)->select('user_id');
//    }
}

//处理url参数
function getSearchUrl($arr = array()) {
    static $_url = array();
    if (!$_url) {
        $url = getUrlStrList(array(), true);
        unset($url['g'], $url['c'], $url['m']);
        $_url = $url;
    }
    $url = $_url;
    if ($arr) {
        foreach ($arr as $key => $val) {
            if (!is_null($val)) {
                $url[$key] = $val;
            } elseif (isset($url[$key])) {
                unset($url[$key]);
            }
        }
    }
    return $url;
}
