<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of orderlogModel
 * 小管家订单状态操作类
 * @author xlp
 */
class steOrderLogModel extends Model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'ste_order_log';
    }

    /*
     * 增加订单状态，只能是服务商操作
     */

    public function saveLog($objData = array(), $isInsertAll = false) {
        if ($isInsertAll) {
            return $this->insertAll($objData);
        } else {
            return $this->insert($objData);
        }
    }

    /*
     * 读取某个订单的全部操作日志
     * @param $orderId int 订单号
     * @param $getAll bool 是否读取全部的信息，包括订单备注，只有后台才需要
     */

    public function getOrderLogByOrderId($orderId, $getAll = false) {
        $where = $getAll ? '' : ' and order_status>0 ';
        return $this->query('SELECT a.*,b.real_name,b.username FROM __TABLE__ AS a LEFT JOIN __PRE__ste_user AS b ON a.user_id=b.user_id WHERE a.order_id=' . $orderId . $where . ' ORDER BY log_id ASC ');
    }

}
