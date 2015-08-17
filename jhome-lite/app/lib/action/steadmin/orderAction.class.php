<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of orderAction
 * 订单管理
 * @author xlp
 */
class orderAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
        $this->steSetting = C('steward/setting');
        $this->setOrderStatus = array(3 => '已审核', 4 => '正在配货', 5 => '配货完成', 6 => '配送中', 7 => '已送达', 8 => '送货失败');
        $this->steSetting['order_status'][13] = '<span class="red">异常订单</span>';
        $this->steSetting['order_type'][0] = '<span class="red">当日预约</span>';
        $this->steSetting['order_type'][1] = '<span class="green">预约订单</span>';
        $this->serviceData = parent::getServiceCache();
    }

    /**
     * 列表
     */
    public function index() {
        parent::_authUser(array(1, 2, 5, 6, 7, 8, 9));
        $title = $this->_get('q'); //关键字搜索
        $st = $this->_get('st', 'order_sn'); //关键字搜索
        $deal = $this->_get('deal', ''); //分组查看
        $orderType = $this->_getid('order_type', 0); //类别
        $serviceId = $this->_getid('service_id', 0); //服务社
        $villageId = $this->_getid('village_id', 0); //小区
        $orderSource = $this->_getid('order_source', 0); //客户端
        $uid = $this->_getid('uid', 0); //商圈
        $startTime = $this->_get('stime', ''); //开始时间
        $endTime = $this->_get('etime', ''); //结束时间
        $status = $this->_getid('status', 0); //状态
        $p = $this->_getid('p', 1);
        $searchType = array('order_sn' => 'order_sn', 'phone' => 'phone', 'username' => 'username');
        $dealAction = array(
            'service' => array('status' => array(1, 3, 4, 5, 13), 'arrive_date[<=]' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))), //客服快捷查看订单
            'finance' => array('status' => array(9, 10))//财务快捷查看订单
        );
        //报表页面每页显示数量为100
        if ($deal == 'report') {
            jumpTo(U('report/today'));
        }
        $where = array();
        if ($deal && isset($dealAction[$deal])) {
            $where = $dealAction[$deal];
        }
        if (isset($_GET['q']) && !$_GET['q']) {
            unset($_GET['q']);
        }
        if ($deal == 'service') {
            $pageShow = 80;
        } else {
            $pageShow = 20;
        }
        if ($title && $st == 'user') {
            $user = D('member')->field('uid')->where(array('nickname' => $title))->findAll(false);
            if ($user) {
                $uid = array();
                foreach ($user as $v) {
                    $uid[] = $v['uid'];
                }
                $title = '';
            }
        } elseif ($title && $st == 'uid') {
            $uid = $title;
            $title = '';
        }
        if ($title && isset($searchType[$st])) {
            $where['LIKE'] = array($searchType[$st] => parent::safeSearch($title));
        }
        if ($orderType) {
            $where['order_type'] = $orderType - 1;
        }
        if ($serviceId) {
            $where['service_id'] = $serviceId;
        }
        if ($villageId) {
            $where['village_id'] = $villageId;
        }
        if ($orderSource) {
            $where['order_source'] = $orderSource - 1;
        }
        if ($uid) {
            $where['uid'] = $uid;
        }
        if ($startTime && $endTime) {
            $where['arrive_date[>=]'] = inTime($startTime);
            $where['arrive_date[<=]'] = inTime($endTime) + 60 * 60 * 24 - 1;
        }
        if ($status) {
            $where['status'] = ($status - 1);
        }
        if (parent::_checkIsPresident()) {//社长
            $where['service_id'] = steadmin::$adminInfo['service_id'];
        } elseif (!parent::_checkIsAdmin()) {
            showError('抱歉，暂时无法查看所属订单');
        }
        $rs = M('ste_order')->where($where)->page($p, $pageShow)->order('order_id DESC')->findAll();
        $total = M('ste_order')->getTotal();
//        z(M('ste_shop')->getAllSql());
//        z($rs);
        if ($rs) {
            foreach ($rs as $k => $v) {
                $userName = getUser($v['uid']);
                $rs[$k]['userName'] = $userName ? $userName : $v['username'];
                $rs[$k]['villageName'] = parent::getVillageName($v['village_id']);
                $rs[$k]['serviceName'] = isset($this->serviceData[$v['service_id']]) ? $this->serviceData[$v['service_id']]['stitle'] : '';
            }
        }
        $lastOrderId = M('ste_order')->order('order_id DESC')->getField('order_id');
        //
        $this->steSetting['order_status'][1] = '<span class="red">已支付</span>';
        $this->assign(array(
            'pageShow' => $pageShow,
            'rs' => $rs, 'total' => $total, 'p' => $p,
            'lastOrderId' => $lastOrderId,
            'service_id' => $serviceId,
            'order_type' => $orderType,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'title' => $title,
            'status' => $status,
            'orderSource' => $orderSource,
            'service' => $this->serviceData,
            'setting' => $this->steSetting,
            'setOrderStaus' => $this->setOrderStatus
        ));
        $this->display();
    }

    /*
     * ajax检测有无新订单
     */

    public function checkorderlist() {
        $orderId = $this->_getid('order_id', 0);
        if (!$orderId) {
            $this->JsonReturn('参数丢失');
        }
        $where = array('order_id[>]' => $orderId, 'status' => array(1, 13), 'arrive_date[<=]' => mktime(0, 0, 0, date('m'), date('d'), date('Y')));
        if (parent::_checkIsPresident()) {
            $where['service_id'] = steadmin::$adminInfo['service_id'];
        }
        $count = M('ste_order')->where($where)->count();
//        z(M('ste_order')->getAllSql());
        $this->JsonReturn('ok', $count, 1);
    }

    /**
     * 详细
     */
    public function detail() {
        $id = $this->_getid('id', 0);
        $orderSn = $this->_get('order_sn', '');
        if (!$id && !$orderSn) {
            showError('参数丢失');
        }
        if ($orderSn) {
            $where = array('order_sn' => $orderSn);
        } else {
            $where = array('order_id' => $id);
        }
        $rs = M('ste_order')->where($where)->find();
        if (!$rs) {
            showError('订单不存在');
        }
        if (parent::_checkIsPresident()) {//社长
            if ($rs['service_id'] != steadmin::$adminInfo['service_id']) {
                showError('没有操作权限!');
            }
        } elseif (!parent::_checkIsAdmin()) {
            showError('没有操作权限');
        }
        $rs['serviceName'] = isset($this->serviceData[$rs['service_id']]) ? $this->serviceData[$rs['service_id']]['stitle'] : '';
        $rs['userName'] = getUser($rs['uid']);
        $rs['villageName'] = parent::getVillageName($rs['village_id']);
        $rs['service_user'] = parent::_getAdminName($rs['service_uid'], 'real_name');
        $rs['deployment_user'] = parent::_getAdminName($rs['deployment_uid'], 'real_name');
        $rs['worker_user'] = parent::_getAdminName($rs['worker_uid'], 'real_name');
        $orderRefund = array();
        if ($rs['status'] == 10) {//有退款的
            $orderRefund = M('ste_order_refund')->where(array('order_id' => $rs['order_id']))->find();
            $orderRefund['actualUserNmae'] = parent::_getAdminName($orderRefund['user_id']);
        }
        $comment = array();
        if ($rs['status'] == 11) {//已评价
            $comment = M('ste_order_comment')->where(array('order_id' => $rs['order_id']))->find();
            $comment['complainList'] = $comment['complain'] ? explode(',', $comment['complain']) : array();
        }
        //订单商品数据
        $goods = M('ste_order_goods')->where(array('order_id' => $rs['order_id']))->findAll(false);
        foreach ($goods as $k => $v) {
            $goods[$k]['shopName'] = parent::_getShopName($v['shop_id']);
        }
//        z($rs);
        $this->assign(array('rs' => $rs, 'orderRefund' => $orderRefund, 'isEdit' => in_array($rs['status'], array(2, 7, 9, 10, 11)) ? 0 : 1,
            'goods' => $goods, 'setting' => $this->steSetting, 'comment' => $comment));
        $this->display();
    }

    /*
     * 处理状态
     */

    public function deal() {
        $id = $this->_postid('id', 0);
        $act = $this->_postid('act', 0);
        if (!$id) {
            showError('参数丢失');
        }
        if (!isset($this->setOrderStatus[$act])) {
            $this->JsonReturn('参数错误');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('订单不存在');
        }
        if (parent::_checkIsPresident()) {//社长
            if ($rs['service_id'] != steadmin::$adminInfo['service_id']) {
                showError('没有操作权限!');
            }
        } elseif (!parent::_checkIsAdmin()) {
            showError('没有操作权限');
        }
        M('ste_order')->update(array('status' => $act), array('order_id' => $id));
        //===记录操作日志====
        parent::saveSySLog(4, array('status' => $act), $id, array('order_id' => $id), '订单管理-处理状态');
        //===记录操作日志====
        $this->JsonReturn('ok', $this->setOrderStatus[$act], 1);
    }

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
