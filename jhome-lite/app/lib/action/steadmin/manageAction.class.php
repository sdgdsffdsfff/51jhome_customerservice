<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of manageAction
 * 订单管理操作管理
 * @author xlp
 */
class manageAction extends commonAction {

    private $payType = array('wx' => 0, 'alipay' => 1, 'tenpay' => 2, 'credit' => 3, 'debit' => 4); //支付方式

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
        $this->steSetting = C('steward/setting');
        $this->setOrderStatus = array(3 => '已审核', 4 => '正在采购', 5 => '采购完成',
            6 => '配送中', 7 => '已送达', 8 => '送货失败', 0 => '订单备注');
        $this->setOrderDealStatus = array(3 => '已审核', 6 => '配送中');
        $this->serviceData = parent::getServiceCache();
    }

    /*
     * 处理订单
     */

    public function index() {
        $id = $this->_getid('id', 0);
        $act = $this->_getid('act', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
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
        //载入小管家
        $user = M('ste_user')->field('user_id,real_name,phone,work_status')->
                        where(array('status' => 1, 'groupid' => 4, 'service_id' => $rs['service_id']))->findAll(false);
        //载入配货员
        $distr = M('ste_user')->field('user_id,real_name,phone,work_status')->
                        where(array('status' => 1, 'groupid' => 3, 'service_id' => $rs['service_id']))->findAll(false);
        if ($rs['status'] == 1) {
            unset($this->setOrderDealStatus[6]);
        } else {
            unset($this->setOrderDealStatus[3]);
        }
        $this->assign(array('rs' => $rs, 'user' => $user, 'setting' => $this->steSetting, 'act' => $act,
            'action' => $this->setOrderStatus, 'orderDealStatus' => $this->setOrderDealStatus, 'distr' => $distr));
        $this->display('doaction');
    }

    public function savelog() {
        $error = $this->_post('error');
        $info = $this->_post('info');
        saveLog('error/steadmin_order', array('msg' => $error, 'info' => $info));
        $this->JsonReturn('ok', null, 1);
    }

    public function printorder() {
        parent::_authUser(array(1, 2, 9));
        $this->returnJson = false;
        $id = $this->_getid('id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
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
        $this->assign(array('rs' => $rs, 'setting' => $this->steSetting));
        $this->display('print');
    }

    /*
     * 处理异常订单
     */

    public function abnormal() {
        parent::_authUser(array(1, 2, 9));
        $this->returnJson = false;
        $id = $this->_getid('id', 0);
        if (!$id) {
            $this->JsonReturn('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('订单不存在');
        }
        if (parent::_checkIsPresident()) {//社长
            if ($rs['service_id'] != steadmin::$adminInfo['service_id']) {
                $this->JsonReturn('没有操作权限!');
            }
        } elseif (!parent::_checkIsAdmin()) {
            $this->JsonReturn('没有操作权限');
        }
        if ($rs['status'] != 13) {//不是异常订单，直接退出
            $this->JsonReturn('该订单状态正常，无需处理');
        }
        $this->assign(array('rs' => $rs, 'setting' => $this->steSetting, 'timer' => $this->disttime()));
//        z($this->tVar);
        $this->display('abnormal');
    }

    /*
     * 检查订单支付情况（直接查询收银台数据）
     */

    public function check_pay() {
        parent::_authUser(array(1, 2, 9));
        $id = $this->_postid('order_id', 0);
        if (!$id) {
            $this->JsonReturn('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('订单不存在');
        }
        if (parent::_checkIsPresident()) {//社长
            if ($rs['service_id'] != steadmin::$adminInfo['service_id']) {
                $this->JsonReturn('没有操作权限!');
            }
        } elseif (!parent::_checkIsAdmin()) {
            $this->JsonReturn('没有操作权限');
        }
        if (!in_array($rs['status'], array(0, 2, 12))) {//不是未支付或者过期订单，直接退出
            $this->JsonReturn('该订单状态无需处理');
        }
        //查询订单的支付情况(pay_model:固定为1，小管家)
        $payInfo = M('pay')->where(array('out_trade_no' => $rs['order_sn'], 'pay_model' => 1))->find();
        if (!$payInfo) {
            $this->JsonReturn('支付信息不存在');
        }
        //如果确认支付已完成，则按异步通知方式处理订单信息
        if ($payInfo['notify_time'] && $payInfo['pay_time']) {
            if ($rs['status'] == 2) {//如果是用户已取消订单，则只修改订单信息并且提交退款申请
                if (!D('steOrderRefund')->field('order_id')->where(array('order_id' => $rs['order_id']))->find()) {
                    //先取订单商品数据
                    $goods = M('ste_order_goods')->where(array('order_id' => $id))->findAll(false);
                    //处理库存和店铺销量
                    $goodsList = array();
                    foreach ($goods as $val) {
                        $goodsList[] = array(
                            'gid' => $val['gid'],
                            'goods_sn' => $val['goods_sn'],
                            'goods_name' => $val['goods_name'],
                            'goods_spec' => $val['goods_spec'],
                            'goods_counts' => $val['goods_counts'],
                            'goods_price' => $val['goods_price'],
                            'credits' => $val['credits']
                        );
                    }
                    $refund = array(
                        'order_id' => $rs['order_id'],
                        'buyer_id' => $rs['uid'],
                        'refund_info' => json_encode($goodsList),
                        'refund_shipping' => $rs['shipping_fee'],
                        'refund_note' => '',
                        'refund_time' => TIME,
                        'refund_amount' => $payInfo['total_fee']//实际付款金额
                    );
                    D('steOrderRefund')->addOrderRefund($refund);
                    M('ste_order')->update(array('pay_amount' => $payInfo['total_fee'],
                        'pay_type' => $this->payType[$payInfo['pay_type']], 'pay_id' => $payInfo['id'],
                        'pay_time' => $payInfo['pay_time']), array('order_id' => $rs['order_id']));
                }
            } else {
                $status = getHttp(U($payInfo['router'], array('out_sn' => $rs['order_sn'])));
                if ($status != 'success') {
                    $this->JsonReturn('路由通知失败，请检查！');
                }
            }
            //===记录操作日志====
            parent::saveSySLog(4, array('order_id' => $id, 'act' => '检查异常支付'), $id, array(), '订单管理-检查支付');
            //===记录操作日志====
            $this->JsonReturn('ok', array('pay_amount' => $payInfo['total_fee'],
                'pay_type' => $payInfo['pay_type'], 'pay_id' => $payInfo['id'],
                'pay_time' => outTime($payInfo['pay_time'])), 1);
        } else {
            $this->JsonReturn('未检查到支付信息');
        }
    }

    /*
     * 检查订单抵价券情况
     */

    public function check_coupon() {
        parent::_authUser(array(1, 2, 9));
        $orderSn = $this->_post('order_sn', '');
        $uid = $this->_postid('uid', 0);
        if (!$orderSn || !$uid) {
            $this->JsonReturn('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_sn' => $orderSn, 'uid' => $uid))->find();
        if (!$rs) {
            $this->JsonReturn('订单不存在');
        }
        if (parent::_checkIsPresident()) {//社长
            if ($rs['service_id'] != steadmin::$adminInfo['service_id']) {
                $this->JsonReturn('没有操作权限!');
            }
        } elseif (!parent::_checkIsAdmin()) {
            $this->JsonReturn('没有操作权限');
        }
        if (!$rs['coupon_offset']) {//没有使用抵价券，直接退出
            $this->JsonReturn('该订单状态无需处理');
        }
        //查询订单的抵价券信息
        $couponInfo = M('ste_coupon')->where(array('used_uid' => $uid, 'order_sn' => $rs['order_sn']))->find();
        if (!$couponInfo) {
            $this->JsonReturn('抵价券信息不存在');
        }
        if ($couponInfo['use_cate']) {
            $couponInfo['use_cate'] = explode(',', $couponInfo['use_cate']);
            //循环取出所有类目的子类目
            $cate = M('ste_goods_cate')->field('id,name,sort,pid,path,depth')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'is_show' => 1, 'is_del' => 0))->order('sort DESC')->select('id');
            $cateList = array();
            foreach ($couponInfo['use_cate'] as $v) {
                $cates = D('tree')->getSubs($cate, $v, true);
                if (isset($cates['items']) && $cates['items']) {
                    $cateList = array_merge($cateList, $cates['items']);
                }
            }
            $couponInfo['cateNameList'] = array_reduce($cateList, create_function('$v,$w', '$v[]=$w["name"];return $v;'));
        } else {
            $couponInfo['cateNameList'] = array();
        }
        $couponInfo['useShopList'] = array();
        if ($couponInfo['use_shop']) {
            $couponInfo['use_shop'] = explode(',', $couponInfo['use_shop']);
            $shopList = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')->where(array('shop_id' => $couponInfo['use_shop']))->select('shop_id', false);
            foreach ($shopList as $v) {
                $couponInfo['useShopList'][] = $v['shop_name'] . ($v['shop_alt_name'] ? '[' . $v['shop_alt_name'] . ']' : '');
            }
        }
        $list = array(0 => '普通发放', 1 => '指定生成', 2 => '新人券', 3 => '限时券', 4 => '折扣券', 5 => '满减券', 6 => '免邮券');
        $couponInfo['type'] = isset($list[$couponInfo['stype']]) ? $list[$couponInfo['stype']] : '';
        $couponInfo['start_time'] = outTime($couponInfo['start_time']);
        $couponInfo['end_time'] = outTime($couponInfo['end_time']);
        $couponInfo['info_time'] = outTime($couponInfo['info_time']);
        $couponInfo['use_client'] = $this->steSetting['use_client'][$couponInfo['use_client']];
        $this->JsonReturn('ok', $couponInfo, 1);
    }

    /*
     * 保存异常处理结果
     */

    public function save_abnormal() {
        parent::_authUser(array(1, 2, 9));
        $id = $this->_postid('order_id', 0);
        if (!$id) {
            $this->JsonReturn('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('订单不存在');
        }
        if (parent::_checkIsPresident()) {//社长
            if ($rs['service_id'] != steadmin::$adminInfo['service_id']) {
                $this->JsonReturn('没有操作权限!');
            }
        } elseif (!parent::_checkIsAdmin()) {
            $this->JsonReturn('没有操作权限');
        }
        if ($rs['status'] != 13) {//不是异常订单，直接退出
            $this->JsonReturn('该订单状态正常，无需处理');
        }
        $objData = array(
            'arrive_date' => $this->_post('arrive_date'),
            'arrive_time' => $this->_post('arrive_time')
        );
        if (!$objData['arrive_date'] || !$objData['arrive_time']) {
            $this->JsonReturn('配送日期和时间段不能为空');
        }
        //检查内容是否有变化
        $isChange = false;
        if ($objData['arrive_date'] != $rs['arrive_date']) {
            $isChange = true;
        }
        if (!$isChange && $objData['arrive_time'] != $rs['arrive_time']) {
            $isChange = true;
        }
//        if (!$isChange) {
//            $this->JsonReturn('数据未修改', null, 2);
//        }
        $objData['status'] = 1; //修改为正常订单
//        z($objData);
        $res = array();
        //修改订单信息
        $res['log'] = M('ste_order')->update($objData, array('order_id' => $id));
        $res['user'] = $this->_sendWXNotice($rs, 5);
        //===记录操作日志====
        parent::saveSySLog(4, $objData, $id, array('order_id' => $id), '订单管理-保存异常订单');
        //===记录操作日志====
        $this->JsonReturn('ok', $res, 1);
    }

    public function printer() {
        $orderId = $this->_postid('id');
        if (!$orderId) {
            showError('对不起，订单号错误！');
        }
        $rs = D('steorder')->getOrderInfoById($orderId, true);
        if (parent::_checkIsPresident()) {//社长
            if ($rs['service_id'] != steadmin::$adminInfo['service_id']) {
                showError('没有操作权限!');
            }
        } elseif (!parent::_checkIsAdmin()) {
            showError('没有操作权限');
        }
        $orderData = $this->_getOrderData($rs);
//        $this->JsonReturn('ok',array('prints'=>array(11=>array('name'=>'物美','result'=>1),'home'=>array('name'=>'调度中心','result'=>'失败啊啊'))),1);
        $res['prints'] = $this->_sendOrderPrint($orderData);
        $this->JsonReturn('ok', $res, 1);
    }

    /*
     * 修改订单信息
     */

    public function edit() {
        parent::_authUser(array(1, 2, 9));
        $this->returnJson = false;
        $id = $this->_getid('id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
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
        if (in_array($rs['status'], array(2, 7, 9, 10, 11, 12))) {//已取消的订单无需编辑
            showError('该订单无法编辑');
        }
        $this->assign(array('rs' => $rs, 'setting' => $this->steSetting, 'timer' => $this->disttime()));
        $this->display();
    }

    /*
     * 取消订单
     */

    public function cancel() {
        parent::_authUser(array(1, 2));
        $id = $this->_postid('order_id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
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
        //处理订单
        $res = D('steorder')->cancelOrderByOrderId($id, true, steadmin::$adminInfo['user_id']);
        if ($res['status'] == 1) {
            //===记录操作日志====
            parent::saveSySLog(4, array(), $id, array('order_id' => $id), '订单管理-取消订单');
            //===记录操作日志====
            $this->JsonReturn('ok', null, 1);
        } else {
            $this->JsonReturn($res['info']);
        }
    }

    /*
     * 提交订单修改信息
     * 只允许修改收货信息
     */

    public function save() {
        parent::_authUser(array(1, 2, 9));
        $id = $this->_postid('order_id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
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
        $objData = array(
            'address' => $this->_post('address'),
            'phone' => $this->_post('phone'),
            'username' => $this->_post('username'),
            'arrive_date' => $this->_post('arrive_date'),
            'arrive_time' => $this->_post('arrive_time')
        );
        if (!$objData['arrive_date'] || !$objData['arrive_time']) {
            $this->JsonReturn('配送日期和时间段不能为空');
        }
        if (!$objData['address'] || !$objData['phone'] || !$objData['username']) {
            showError('地址、手机号和收货人不能为空');
        }
        //检查内容是否有变化
        $isChange = false;
        $oldData = array();
        if ($objData['arrive_date'] != $rs['arrive_date']) {
            $oldData['arrive_date'] = $rs['arrive_date'];
            $isChange = true;
        } else {
            unset($objData['arrive_date']);
        }
        if (!$isChange && $objData['arrive_time'] != $rs['arrive_time']) {
            $oldData['arrive_time'] = $rs['arrive_time'];
            $isChange = true;
        } else {
            unset($objData['arrive_time']);
        }
        if (!$isChange && $objData['address'] != $rs['address']) {
            $oldData['address'] = $rs['address'];
            $isChange = true;
        } else {
            unset($objData['address']);
        }
        if (!$isChange && $objData['phone'] != $rs['phone']) {
            $oldData['phone'] = $rs['phone'];
            $isChange = true;
        } else {
            unset($objData['phone']);
        }
        if (!$isChange && $objData['username'] != $rs['username']) {
            $oldData['username'] = $rs['username'];
            $isChange = true;
        } else {
            unset($objData['username']);
        }
        if (!$isChange) {
            $this->JsonReturn('数据未修改', null, 2);
        }
//        z($objData);
        //修改订单信息
        if ($objData) {
            $res = array();
            $res['order'] = M('ste_order')->update($objData, array('order_id' => $id));
            //判断订单是否已经送出，若已送出通知小管家新的地址信息
            if (in_array($rs['status'], array(6, 8))) {//正在配送、配送失败
                $res['deployment'] = $this->_sendQIYENotice($rs, 3, $rs['worker_uid']);
                if (!$res['deployment']['result']) {
                    $this->JsonReturn('ok', $res, 1);
                }
                $res['user'] = $this->_sendWXNotice($rs, 4, $rs['worker_uid']);
            }
            //===记录操作日志====
            $logData = array();
            $logData['old'] = $oldData;
            $logData['new'] = $objData;
            parent::saveSySLog(2, $logData, $id, array('order_id' => $id), '订单管理-修改订单');
            //===记录操作日志====
        }
        $this->JsonReturn('ok', $res, 1);
    }

    /*
     * 客服或者管理员操作订单
     */

    public function manage() {
//        $this->JsonReturn('ok',array('prints'=>array(11=>array('name'=>'物美','result'=>'失败'),'home'=>array('name'=>'调度中心','result'=>1)),'user'=>1,'log'=>0),1);
        set_time_limit(0);
        $orderId = $this->_postid('order_id'); //订单编号
        $deploymentUid = $this->_postid('deployment_uid'); //配货员uid
        $workerUid = $this->_postid('worker_uid'); //小管家uid
        $desc = $this->_post('desc'); //操作备注
        $status = $this->_postid('status', 0); //处理状态
        $action = $this->_post('doaction', 0, 'intval'); //额外处理动作
        if (!$orderId) {
            showError('参数丢失');
        }
        $rs = D('steorder')->getOrderInfoById($orderId, true);
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
        if (!isset($this->setOrderStatus[$status])) {
            $this->JsonReturn('不允许的操作类型');
        }
        if (in_array($rs['status'], array(0, 2, 9, 10, 11, 12))) {
            $this->JsonReturn('该订单状态不允许处理！');
        }
        //状态不能回撤或者重复操作
        if (($rs['status'] == 8 && $status != 7) || ($status && !in_array($status, array(7, 8)) && $status <= $rs['status'])) {
            $this->JsonReturn('订单状态不允许该处理！');
        }
        $objData = array();
        $res = array();
        $logData = array();
        $isInsertAll = false;
        switch ($status) {
            case 0://对订单进行备注
                $logData = array('order_id' => $rs['order_id'],
                    'order_status' => $status,
                    'desc' => $desc,
                    'user_id' => steadmin::$adminInfo['user_id'],
                    'info_time' => TIME);
                break;
            case 3:case 4://正在配货//已审核，打印出货单、通知配货员、通知用户
                if (!$deploymentUid) {
                    $this->JsonReturn('缺少配货员信息');
                }
                if ($rs['status'] != 1) {
                    $this->JsonReturn('订单当前状态不可操作');
                }
                $orderData = $this->_getOrderData($rs);
                $objData['confirm_time'] = TIME;
                $objData['service_uid'] = steadmin::$adminInfo['user_id'];
                $objData['deployment_uid'] = $deploymentUid;
                $objData['status'] = 4; //开始配货
                if ($workerUid) {
                    $objData['worker_uid'] = $workerUid; //一起分配配送小管家
                }
                $res['deployment'] = $this->_sendQIYENotice($rs, 1, $deploymentUid);
                if (!$res['deployment']['result']) {
                    $this->JsonReturn('ok', $res, 1);
                }
                $res['prints'] = $this->_sendOrderPrint($orderData);
                $res['user'] = $this->_sendWXNotice($rs, 1, $deploymentUid);
                //记录客服操作
                $logData[] = array('order_id' => $rs['order_id'],
                    'order_status' => 3,
                    'desc' => $desc,
                    'user_id' => steadmin::$adminInfo['user_id'],
                    'info_time' => TIME);
                //记录配货操作
                $logData[] = array('order_id' => $rs['order_id'],
                    'order_status' => 4,
                    'desc' => $desc,
                    'user_id' => $objData['deployment_uid'],
                    'info_time' => TIME);
                $isInsertAll = true;
                break;
            case 5://配货完成，由配送员操作
                $logData = array('order_id' => $rs['order_id'],
                    'order_status' => $status,
                    'desc' => $desc,
                    'user_id' => steadmin::$adminInfo['user_id'],
                    'info_time' => TIME);
                $objData['deployment_uid'] = steadmin::$adminInfo['user_id'];
                $objData['status'] = 5; //开始配货
                break;
            case 6://配送中，选择小管家
                if (!$workerUid) {
                    $this->JsonReturn('请先选择小管家');
                }
                $res['deployment'] = $this->_sendQIYENotice($rs, 2, $workerUid);
                if (!$res['deployment']['result']) {
                    $this->JsonReturn('ok', $res, 1);
                }
                $res['user'] = $this->_sendWXNotice($rs, 2, $workerUid);
                //记录配货操作
                $logData = array('order_id' => $rs['order_id'],
                    'order_status' => $status,
                    'desc' => $desc,
                    'user_id' => $workerUid,
                    'info_time' => TIME);
                $objData['shipping_time'] = TIME;
                $objData['worker_uid'] = $workerUid;
                $objData['status'] = 6; //开始配货
                break;
            case 7://已送达，由小管家操作通知用户可以评论
                $res['user'] = $this->_sendWXNotice($rs, 3, 0);
                //记录配货操作
                $logData = array('order_id' => $rs['order_id'],
                    'order_status' => $status,
                    'desc' => $desc,
                    'user_id' => steadmin::$adminInfo['user_id'],
                    'info_time' => TIME);
                $objData['finish_time'] = TIME;
                $objData['status'] = 7; //已送达
                //完成邀请人赠送
                D('steCoupon')->thankpromotion($rs['uid'], $rs['order_id']);
                break;
            case 8://送货失败
                $logData = array('order_id' => $rs['order_id'],
                    'order_status' => $status,
                    'desc' => $desc,
                    'user_id' => steadmin::$adminInfo['user_id'],
                    'info_time' => TIME);
                $objData['status'] = 8; //送达失败
                break;
        }
        //如果对订单表进行了修改就保存
        if ($objData) {
            D('steorder')->update($objData, array('order_id' => $orderId));
            //===记录操作日志====
            parent::saveSySLog(2, $objData, $orderId, array('order_id' => $orderId), '订单管理-处理');
            //===记录操作日志====
        }
        //保存日志
        $res['log'] = D('steOrderLog')->saveLog($logData, $isInsertAll) ? 1 : 0;
        if ($res['log']) {
            $this->JsonReturn('ok', $res, 1);
        } else {
            $this->JsonReturn('操作失败！');
        }
    }

    /*
     * 编辑配货员和小管家
     */

    public function edit_worker() {
        parent::_authUser(array(1, 2, 9));
        $id = $this->_getid('order_id', 0);
        $type = $this->_getid('type', 1);
        $this->returnJson = false;
        if (!$id) {
            $this->JsonReturn('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('订单不存在');
        }
        if (parent::_checkIsPresident()) {//社长
            if ($rs['service_id'] != steadmin::$adminInfo['service_id']) {
                $this->JsonReturn('没有操作权限!');
            }
        } elseif (!parent::_checkIsAdmin()) {
            $this->JsonReturn('没有操作权限');
        }
        //判断当前订单是否可以修改
        if (!in_array($rs['status'], array(3, 4, 5, 6))) {
            $this->JsonReturn('该订单当前状态不可编辑');
        }
        if ($type == 1) {//修改配货员
            //载入配货员
            $user = M('ste_user')->field('user_id,real_name,phone,work_status')->
                            where(array('status' => 1, 'groupid' => 3, 'service_id' => $rs['service_id']))->findAll(false);
        } else {
            //载入小管家
            $user = M('ste_user')->field('user_id,real_name,phone,work_status')->
                            where(array('status' => 1, 'groupid' => 4, 'service_id' => $rs['service_id']))->findAll(false);
        }
        $this->assign(array('user' => $user, 'type' => $type, 'rs' => $rs, 'setting' => $this->steSetting));
        $this->display('edit_worker');
    }

    /*
     * 保存修改配货员和小管家信息
     */

    public function edit_worker_save() {
        parent::_authUser(array(1, 2, 9));
        $id = $this->_postid('order_id', 0);
        $type = $this->_postid('type', 1);
        $workerUid = $this->_postid('worker_uid', 0);
        if (!$id) {
            $this->JsonReturn('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('订单不存在');
        }
        if (parent::_checkIsPresident()) {//社长
            if ($rs['service_id'] != steadmin::$adminInfo['service_id']) {
                $this->JsonReturn('没有操作权限!');
            }
        } elseif (!parent::_checkIsAdmin()) {
            $this->JsonReturn('没有操作权限');
        }
        //判断当前订单是否可以修改
        if (!in_array($rs['status'], array(3, 4, 5, 6))) {
            $this->JsonReturn('该订单当前状态不可编辑');
        }
        $field = '';
        switch ($type) {
            case 1:
                if ($workerUid == $rs['deployment_uid']) {
                    $this->JsonReturn('配货员未变更');
                }
                $field = 'deployment_uid';
                $msgId = 1;
                $oldUid = $rs['deployment_uid'];
                break;
            case 2:
                if ($workerUid == $rs['worker_uid']) {
                    $this->JsonReturn('小管家未变更');
                }
                $field = 'worker_uid';
                $msgId = 2;
                $oldUid = $rs['worker_uid'];
                break;
        }
        if (!$field) {
            $this->JsonReturn('参数有误');
        }
        //更新数据
        $info = M('ste_order')->update(array($field => $workerUid), array('order_id' => $id));
        if (!$info) {
            $this->JsonReturn('编辑失败');
        }
        $res = array();
        //通知小管家
        $res['deployment'] = $this->_sendQIYENotice($rs, $msgId, $workerUid);
        $res['deployment_old'] = $this->_sendQIYENotice($rs, 4, $oldUid);
        //===记录操作日志====
        parent::saveSySLog(2, array('field' => $field, 'user_id' => $workerUid, 'old_user_id' => $oldUid), $id, array(), '订单管理-更换小管家');
        //===记录操作日志====
        //返回结果
        $this->JsonReturn('ok', $res, 1);
    }

    /*
     * 生成各种需要的数据准备
     */

    private function _getOrderData(&$rs) {
        $printer = F('steward/print_city_' . steadmin::$adminInfo['city_id']);
        if (!$printer) {
            parent::setPrintCache();
            $printer = F('steward/print_city_' . steadmin::$adminInfo['city_id']);
            if (!$printer) {
                showError('没有打印机信息');
            }
        }

        $tmpData = array();
        //先按店铺取商品数据
        foreach ($rs['items'] as $val) {
            if (!isset($tmpData[$val['shop_id']])) {
                $tmpData[$val['shop_id']] = array('items' => array(), 'order_amount' => 0);
            }
            $tmpData[$val['shop_id']]['items'][] = array(
                'goods_name' => $val['goods_name'],
                'goods_spec' => $val['goods_spec'],
                'goods_counts' => $val['goods_counts'],
                'goods_price' => $val['goods_price'],
                'goods_number' => $val['goods_number']
            );
            $tmpData[$val['shop_id']]['order_amount']+=$val['goods_counts'] * $val['goods_price'];
        }
//        $_getDeploymentUsers = array();
        foreach ($tmpData as $key => $val) {
            $tmpData[$key]['order_amount'] = priceFormat($tmpData[$key]['order_amount']);
            $tmpData[$key]['shop_name'] = parent::_getShopName($key);
//            $tmpData[$key]['deployment'] = $this->_getDeploymentUser($key);
            $tmpData[$key]['print'] = isset($printer['shop'][$key]) ?
                    $printer['shop'][$key][array_rand($printer['shop'][$key], 1)] : '';
            if (!$tmpData[$key]['print']) {
                showError('店铺' . $tmpData[$key]['shop_name'] . '没有打印机信息');
            }
//            $_getDeploymentUsers[] = $tmpData[$key]['deployment'];
        }
        $objData = array(
            'info' => array(
                'order_id' => $rs['order_id'],
                'order_sn' => $rs['order_sn'],
                'address' => $rs['address'],
                'phone' => $rs['phone'],
                'username' => $rs['username'],
                'desc' => $rs['desc'],
                'order_time' => outTime($rs['order_time']),
                'arrive_date' => outTime($rs['arrive_date'], 2),
                'arrive_time' => $rs['arrive_time'],
                'order_amount' => $rs['order_amount'],
                'shipping_fee' => $rs['shipping_fee'],
                'offset' => $rs['credit_offset'] + $rs['coupon_offset'] + $rs['sales_offset'],
                'printCounts' => count($tmpData),
                'order_no' => sprintf("%04d", D('steorder')->where(array('status[>=]' => 3, 'status[<=]' => 11,
                            'arrive_date' => $rs['arrive_date'], 'service_id' => $rs['service_id']))->count() + 1),
            ),
            'print' => isset($printer['zone'][$rs['service_id']]) ?
                    $printer['zone'][$rs['service_id']][array_rand($printer['zone'][$rs['service_id']], 1)] : '',
//            'deployment' => array_unique($_getDeploymentUsers), //将发货员进行排重处理
            'order' => $tmpData,
        );
        if (!$objData['print']) {
            showError('该服务点没有打印机信息');
        }
//        F('test_order_data',$objData);
//        z($objData);
        return $objData;
    }

    /*
     * 发送出货单
     */

    private function _sendOrderPrint(&$rs) {
        T('weixin/feiyin.print');
        return feiyinApi::send($rs);
    }

    /*
     * 微信通知接口
     */

    private function _sendWXNotice(&$rs, $type = 1, $userId = 0) {
        if ($userId) {
            $steUser = D('steadmin')->where(array('user_id' => $userId))->find();
            if (!$steUser) {
                $this->JsonReturn('抱歉，小管家不存在，请检查');
            }
        }
        $msgType = 'text';
        switch ($type) {
            case 1://开始配货
                $title = '订单【已审核】';
                $desc = "社主的订单已通过审核，小管家:{$steUser['real_name']}{$steUser['phone']}【正在备货】~ 您期望的配送时间为:" . outTime($rs['arrive_date'], 2) . " {$rs['arrive_time']}。若遇上排队会需要一些时间，请耐心等待哦~";
                $appDesc = "社主的订单已通过审核，小管家:{$steUser['real_name']}{$steUser['phone']}【正在备货】~ 您期望的配送时间为:" . outTime($rs['arrive_date'], 2) . " {$rs['arrive_time']}";
                break;
            case 2://开始配送
                $title = '订单【开始配送】';
                $desc = "社主的订单【开始配送】啦~ 小管家:{$steUser['real_name']}{$steUser['phone']}带着宝贝正飞奔而来，记得给小管家开门哦~";
                $appDesc=&$desc;
                break;
            case 3://配送完成
                $title = '订单【完成配送】啦~ 快去写评价得红包吧~';
                $desc = "亲爱哒社主大人，小管家已经将您选购的宝贝送到您的手上了。快来写个评价，跟朋友分享20个红包吧。跪谢您对结邻的支持，我们会更加努力哒~";
                $appDesc = "亲爱哒社主大人，小管家已经将您选购的宝贝送到您的手上了。快来写个评价，跟朋友分享20个红包吧。";
                $msgType = 'news';
                break;
            case 4://订单变更
                $title = '订单【已变更】';
                $desc = "社主的订单信息已变更，小管家:{$steUser['real_name']}{$steUser['phone']}【正在备货】~ 您期望的配送时间为:" . outTime($rs['arrive_date'], 2) . " {$rs['arrive_time']}。若遇上排队会需要一些时间，请耐心等待哦~";
                $appDesc = "社主的订单信息已变更，小管家:{$steUser['real_name']}{$steUser['phone']}【正在备货】~ 您期望的配送时间为:" . outTime($rs['arrive_date'], 2) . " {$rs['arrive_time']}";
                break;
            case 5://异常订单恢复
                $title = '异常订单【已恢复】，点击查看最新信息';
                $desc = "您的异常订单:{$rs['order_sn']} 已恢复为正常订单，小管家会及时处理，请耐心等待";
                $appDesc=&$desc;
                $msgType = 'news';
                break;
            default:
                $this->JsonReturn('操作类型不正确');
        }
        $appNoticeInfo = '';
        $wxNoticeInfo = array('result' => 0, 'msg' => '未知错误');
        if ($rs['order_source']) {//订单来源是App端，则改为App消息推送
            $appNoticeInfo = $this->_sendAppNotice($rs, $title, $appDesc);
        }
        $touser = D('member')->getOpenidByUids($rs['uid']);
        if (!$rs['order_source'] && !$touser) {
            $this->JsonReturn('用户不存在', array('code' => '', 'msg' => ''), 0);
        }
        if ($touser) {
            if ($msgType == 'news') {
                $sendInfo = array(
                    'touser' => $touser,
                    'msgtype' => 'news',
                    'news' => array(
                        'articles' => array(
                            array(
                                'title' => $title,
                                'createTime' => $rs['order_time'],
                                'description' => $desc,
                                'picurl' => '',
                                'url' => U('steward/order/detail', array('oid' => $rs['order_id']))
                            )
                        )
                    )
                );
            } else {
                $sendInfo = array(
                    'touser' => $touser,
                    'msgtype' => 'text',
                    'text' => array(
                        'content' => $desc . "\n <a href=\"" . U('steward/order/detail', array('oid' => $rs['order_id'])) . "\">查看订单信息</a>"
                    )
                );
            }
            T('weixin/weixin.api');
            $weixinMsgApi = new weixinMsgApi();
            if ($weixinMsgApi->sendCustomMessage($sendInfo)) {
                $wxNoticeInfo = array('result' => 1, 'msg' => 'ok');
            } else {
                $wxNoticeInfo = array('result' => 0, 'msg' => $weixinMsgApi->errCode . '-' . $weixinMsgApi->errMsg);
            }
        }
        return $appNoticeInfo ? $appNoticeInfo : $wxNoticeInfo;
    }

    /*
     * App推送通知接口
     */

    private function _sendAppNotice(&$rs, $title, $content) {
        T('other/xingeApp');
        $sendInfo = array(
            'title' => $title,
            'content' => $content,
            'custom' => array('mod' => 'orderdetail', 'para' => 'oid=' . $rs['order_id'])
        );
        $result = XingeApp::Push($sendInfo, (String) $rs['uid']);
//        print_r($result);
        $res = array('result' => 0, 'msg' => '-1-未知错误');
        //安卓系统
        if ($rs['order_source'] == 2 && isset($result['android']['ret_code'])) {
            if ($result['android']['ret_code'] == 0) {
                $res = array('result' => 1, 'msg' => 'ok');
            } else {
                $res = array('result' => 0, 'msg' => $result['android']['ret_code'] . '-' . $result['android']['err_msg']);
            }
        }
        //IOS系统
        if ($rs['order_source'] == 1 && isset($result['ios']['ret_code'])) {
            if ($result['ios']['ret_code'] == 0) {
                $res = array('result' => 1, 'msg' => 'ok');
            } else {
                $res = array('result' => 0, 'msg' => $result['ios']['ret_code'] . '-' . $result['ios']['err_msg']);
            }
        }
//        z($res);
        return $res;
    }

    /*
     * 企业号通知接口
     * @param $rs 订单信息
     * @param $type 通知类型 1:通知配货员 2:通知小管家 3:订单信息变更
     * @param $userId 接收通知的用户
     */

    private function _sendQIYENotice(&$rs, $type = 1, $userId = 0) {
        $loginType = 0; //登陆身份类型，0:未定义 1:配货员 2:小管家 3:社长
        $gotoUrl = 'steward/worker/orderdetail';
        $msgType = 'news';
        switch ($type) {
            case 1://配货
                $title = '配货员:您有新的订单需要完成【采购】，用户:【' . $rs['username'] . '】';
                $desc = "订单号：{$rs['order_sn']}\n请点击进入页面确认采购完成";
                $loginType = 1;
                break;
            case 2://小管家配送
                $title = '小管家:您有新的订单需要【配送】，用户:【' . $rs['username'] . '】';
                $desc = "订单号：{$rs['order_sn']}\n地址：{$rs['address']}\n收货人：{$rs['username']}\n电话：{$rs['phone']}\n请在送达以后点击进入确认配送完成";
                $loginType = 2;
                $gotoUrl = 'steward/worker/detail';
                break;
            case 3://订单信息变更
                $title = '小管家:订单【已变更】，请注意配送信息，用户:【' . $rs['username'] . '】';
                $desc = "订单号：{$rs['order_sn']}\n地址：{$rs['address']}\n收货人：{$rs['username']}\n电话：{$rs['phone']}\n请在送达以后点击进入确认配送完成";
                $loginType = 2;
                $gotoUrl = 'steward/worker/detail';
                break;
            case 4://取消已分配的订单
                $msgType = 'text';
                $title = "订单【已重新分配】，请注意配送信息，用户:【" . $rs['username'] . "】\n";
                $desc = "订单已分配给其他小管家处理\n订单号：{$rs['order_sn']}\n地址：{$rs['address']}\n收货人：{$rs['username']}\n电话：{$rs['phone']}";
                $loginType = 2;
                break;
            default:
                $this->JsonReturn('企业号操作类型不正确');
        }
        if (!$userId) {
            $this->JsonReturn('请先选择操作员');
        }
        T('weixin/qy/qyWeixin.api');
        qyApi::init(steadmin::$adminInfo['city_id']);
        $touser = D('steadmin')->getUserOpenid($userId);
        if (!$touser) {
            $this->JsonReturn('小管家系统用户不存在（user_id:' . $userId . '）');
        }
        if ($msgType == 'text') {
            $sendInfo = array(
                'touser' => $touser,
                'msgtype' => 'text',
                'text' => array(
                    'content' => $title . $desc
                )
            );
        } else {
            $sendInfo = array(
                'touser' => $touser,
                'msgtype' => 'news',
                'news' => array(
                    'articles' => array(
                        array(
                            'title' => $title,
                            'createTime' => TIME,
                            'description' => $desc,
                            'picurl' => '',
                            'url' => U($gotoUrl, array('oid' => $rs['order_id'], 'type' => $loginType, 'userid' => $userId))
                        )
                    )
                )
            );
        }
        return array('user_id' => $userId, 'name' => parent::_getAdminName($userId),
            'result' => qyApi::messageSend($sendInfo) ? 1 : 0, 'msg' => qyApi::$errorMsg);
    }

    /*
     * 生成配送时间段
     */

    function disttime() {
        $limit = $this->steSetting['shipTime']['limit']; //可购买天数
        $shours = $this->steSetting['shipTime']['shours']; //开始服务时间
        $ehours = $this->steSetting['shipTime']['ehours']; //结束服务时间
        $minute = $this->steSetting['shipTime']['minute']; //起止分钟，00 或者 30
        $objData = array();
        $gtime = date('G'); //当前小时
        $itime = date('i'); //当前分钟
        for ($i = 0; $i < $limit; $i+=1) {
            $date = mktime(0, 0, 0, date('m'), date('d') + $i, date('Y'));
            if (!$i) {//今天时间段
                if ($gtime < $shours) {
                    $shour = $shours;
                } else {
                    $shour = ($itime <= 30) ? $gtime : $gtime + 1;
                }
                $timeLimit = ($itime <= 30) ? 30 : ($gtime < $shours ? $minute : '00');
            } else {
                $shour = $shours;
                $timeLimit = $minute;
            }
            switch ($i) {
                case 0:$desc = '今天';
                    break;
                case 1:$desc = '明天';
                    break;
                case 2:$desc = '后天';
                    break;
                default :$desc = date('m月d日', $date);
            }
            if ($shour < $ehours) {
                $objData[] = array(
                    'show' => outTime($date, 2),
                    'desc' => $desc,
                    'date' => $date,
                    'times' => $this->setTimes($shour, $ehours, $timeLimit)
                );
            }
        }
        return $objData;
    }

    /*
     * 生成指定时间间隔
     */

    private function setTimes($shours, $ehours, $timeLimit = '00') {
        $times = array();
        for ($i = $shours; $i < $ehours; $i+=1) {
            $times[] = $i . ':' . $timeLimit . '-' . ($i + 1) . ':' . $timeLimit;
        }
        return $times;
    }

}
