<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of steorder
 * 小管家订单
 * @author xlp
 */
class steorderModel extends Model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'ste_order';
    }

    /*
     * 获取订单信息
     * @param $orderId int 订单编号，注意不是订单号
     * @param $getAll bool 是否获取订单下的商品信息
     * @return array 订单信息
     */

    public function getOrderInfoById($orderId, $getAll = false) {
        $rs = $this->where(array('order_id' => $orderId))->find();
        if ($rs && $getAll) {
            $rs['items'] = M('ste_order_goods')->where(array('order_id' => $orderId))->findAll(false);
        }
        return $rs;
    }

    /*
     * 取消订单
     * @param $orderId 订单号
     * @param $must 是否强制取消订单
     * @param $cancelUserid 强制取消订单的操作人uid
     * @return array 处理结果
     */

    public function cancelOrderByOrderId($orderId, $must = false, $cancelUserid = 0) {
        $rs = $this->getOrderInfoById($orderId, true);
        if (!$rs) {
            return array('status' => 0, 'info' => '订单不存在');
        }
        if (!$must && !in_array($rs['status'], array(0, 1, 12))) {
            return array('status' => 0, 'info' => '订单状态不可取消');
        }
        //返还扣除的结邻币
        if ($rs['credit_offset'] > 0) {
            D('member')->setUserScore(array('uid' => $rs['uid'], 'act' => 1,
                'credit' => $rs['credit_offset'] * 100, 'event' => 1, 'fid' => $orderId,
                'content' => '小管家配送取消订单，订单号：' . $rs['order_sn']), 'shopping');
        }
        //退还已使用的抵价券
        if ($rs['coupon_offset'] > 0) {
            D('steCoupon')->cancelCoupon($rs['order_sn'], $rs['uid']);
        }
        //处理库存和店铺销量
//        $shopGoodsSales = array();
        $goodsList = array();
        foreach ($rs['items'] as $val) {
            $goodsList[] = array(
                'gid' => $val['gid'],
                'goods_sn' => $val['goods_sn'],
                'goods_name' => $val['goods_name'],
                'goods_spec' => $val['goods_spec'],
                'goods_counts' => $val['goods_counts'],
                'goods_price' => $val['goods_price'],
                'credits' => $val['credits']
            );
            M('ste_goods')->update('sale_counts=sale_counts-' .
                    $val['goods_counts'] . ',storage_counts=storage_counts+' .
                    $val['goods_counts'], array('gid' => $val['gid']));
//            if (!isset($shopGoodsSales[$val['shop_id']])) {
//                $shopGoodsSales[$val['shop_id']] = $val['goods_counts'];
//            } else {
//                $shopGoodsSales[$val['shop_id']]+=$val['goods_counts'];
//            }
        }
        //更新店铺产品销量
//        if ($shopGoodsSales) {
//            foreach ($shopGoodsSales as $key => $val) {
//                M('ste_shop')->update('goods_sales=goods_sales-' . $val, array('shop_id' => $key));
//            }
//        }
        //如果订单已付款，则添加退款申请
        if ($rs['pay_amount'] > 0) {
            $refund = array(
                'cancel_userid' => $cancelUserid,
                'order_id' => $rs['order_id'],
                'buyer_id' => $rs['uid'],
                'refund_info' => json_encode($goodsList),
                'refund_shipping' => $rs['shipping_fee'],
                'refund_note' => '',
                'refund_time' => TIME,
                'refund_amount' => $rs['pay_amount']//实际付款金额
            );
//            $refund['refund_amount']+=$rs['shipping_fee'];
            D('steOrderRefund')->addOrderRefund($refund);
            //发送申请退款成功的短信
            $objData = array(
                //短信消息结构体
                'sms' => array(
                    'phone' => $rs['phone'],
                    'content' => array('order_sn' => $rs['order_sn'], 'type' => '全额'),
                    'tplId' => 804919
                ),
            );
            asynHttp('msg', $objData);
        }
        //修改订单状态
        $this->update(array('status' => 2), array('order_id' => $orderId));
        return array('status' => 1, 'info' => 'ok');
    }

    /*
     * 检查是否是新人（没有任何购买记录）
     * @param $uid int UID
     */

    public function checkIsNewShopper($uid) {
        return $this->where(array('uid' => $uid, 'status' => array(0, 1, 3, 4, 5, 6, 7, 8, 9, 11, 12, 13)))->find() ? false : true;
    }

    /*
     * 结算订单赠送的结邻币
     */

    public function accountsOrderByOrderId($orderId) {
        $rs = $this->getOrderInfoById($orderId, true);
        if (!$rs) {
            return array('status' => 0, 'info' => '订单不存在');
        }
        //订单是否已评论
        if ($rs['status'] != 11) {
            return array('status' => 0, 'info' => '订单状态不可操作');
        }
        //先查询该订单是否有结邻币结算
        $has = M('shopping_credit_log')->field('id')->where(array('uid' => $rs['uid'],
                    'event' => 3, 'act' => 1, 'fid' => $orderId))->find();
        if ($has) {
            return array('status' => 0, 'info' => '该订单已结算');
        }
        $credits = 0;
        foreach ($rs['items'] as $val) {
            $credits+=intval($val['goods_counts']) * intval($val['credits']);
        }
        if ($credits) {
            D('member')->setUserScore(array('act' => 1, 'credit' => $credits,
                'event' => 3, 'fid' => $orderId, 'content' => '小管家配送赠送，订单号：' .
                $rs['order_sn']), 'shopping');
        }
        return array('status' => 1, 'credits' => $credits);
    }

    /*
     * 删除订单，慎用！将删除订单一切数据
     */

    public function delOrderByOrderId($orderId) {
        //先取消订单
        $this->cancelOrderByOrderId($orderId);
        //再删除订单数据和订单商品及相关数据
        M('ste_order')->delete(array('order_id' => $orderId));
        M('ste_order_comment')->delete(array('order_id' => $orderId));
        M('ste_order_goods')->delete(array('order_id' => $orderId));
        M('ste_order_log')->delete(array('order_id' => $orderId));
        M('ste_order_refund')->delete(array('order_id' => $orderId));
        return true;
    }

}
