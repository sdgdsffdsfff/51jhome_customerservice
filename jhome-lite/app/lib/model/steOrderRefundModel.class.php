<?php

defined('IN_XLP') or exit('Access Denied');
/*
 * 小管家退款模型
 */

class steOrderRefundModel extends Model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'ste_order_refund';
    }

    /*
     * 添加退款申请
     * @param $orderId int 订单号
     * @param $refundAmount int 申请退款金额
     * @return bool;
     */

    public function addOrderRefund($rs) {
        if ($this->field('order_id')->where(array('order_id' => $rs['order_id']))->find()) {
            return false;
        } else {
            $rs['actual_refund'] = 0;
            $rs['score_return'] = 0;
            $rs['user_id'] = 0;
            $rs['serial_number'] = 0;
            $rs['finance_note'] = 0;
            $rs['actual_type'] = 0;
            $rs['act_time'] = 0;
            return $this->insert($rs);
        }
    }

}
