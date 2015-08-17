<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of mapAction
 *
 * @author xlp
 */
class mapAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
        $this->key = '1aa6a77c877c9d026f2f7640bb722f41';
    }

    /*
     * 实时跟踪订单位置
     */

    function order() {
        $id = $this->_getid('id', 0);
        $from = $this->_get('from', '');
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
        if (!$rs) {
            showError('订单不存在');
        }
        $worker = D('steadmin')->getUserInfoById($rs['worker_uid']);
        $this->assign(array('rs' => $rs, 'worker' => $worker, 'from' => $from));
        $this->display();
    }

    function map_data() {
        $id = $this->_getid('id', 0);
        if (!$id) {
            $this->JsonReturn('参数丢失');
        }
        $rs = M('ste_order')->where(array('order_id' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('订单不存在');
        }
        //当前状态是否可以查看地图
        if ($rs['status'] < 6 || !$rs['worker_uid']) {
            $this->JsonReturn('订单当前时间不可跟踪位置');
        }
        $worker = D('steadmin')->getUserInfoById($rs['worker_uid']);
//        $orderExpress = $this->getWorkerMapInfo('17705818536', inTime('2014-11-19 11:00:00'), inTime('2014-11-19 12:30:00'));
        $finishTime = $rs['finish_time'] ? $rs['finish_time'] : TIME;
        $orderExpress = $this->getWorkerMapInfo($worker['phone'], $rs['shipping_time'], $finishTime);
        $center = array();
        if ($orderExpress) {
            $center = end($orderExpress);
        }
        $this->assign(array('orderExpress' => $orderExpress, 'center' => $center));
//        z($this->tVar);
        $this->JsonReturn('ok', $this->getFetch('map_data'), 1);
    }

    /*
     * 三维接口，获取订单位置信息
     */

    private function getWorkerMapInfo($phone, $stime, $etime) {
        $str = getHttp('http://sun-talk.com.cn/qchat2_0API/gisLatlngtmp/findGISData.do?loginName=tfkj&loginPasswd=123456&startTime=' .
                urlencode(outTime($stime)) . '&endTime=' . urlencode(outTime($etime)) . '&mobile=' . $phone . '&corpCode=tfkj');
        $res = json_decode($str, true);
        $rs = array();
        if ($res['statusCode'] == 3000 && isset($res['pagina']['resultList'])) {
//            z($res['pagina']['resultList']);
            $str = array();
            foreach ($res['pagina']['resultList'] as $val) {
                $str[] = $val['latitude'] . ',' . $val['longitude'];
            }
            $arr = array_chunk($str, 30);
//            z($arr);
            $resArr = array();
            foreach ($arr as $v) {
                $rs = json_decode(getHttp('http://apis.map.qq.com/ws/coord/v1/translate?locations=' .
                                implode(';', $v) . '&type=1&key=1aa6a77c877c9d026f2f7640bb722f41'), true);
                if ($rs['status'] == 0 && isset($rs['locations']) && is_array($rs['locations'])) {
                    $resArr = array_merge($resArr, $rs['locations']);
                }
            }
//            z($resArr);
            return $resArr;
        } else {
            showError('小管家实时地理位置获取失败');
        }
    }

    function address() {
        $x = $this->_get('x');
        $y = $this->_get('y');
        if (!$x || !$y) {
            $this->JsonReturn('参数不正确');
        }
        $rs = json_decode(getHttp('http://apis.map.qq.com/ws/geocoder/v1/?location=' . $y . ',' . $x . '&key=' . $this->key), true);
        if (isset($rs['status']) && $rs['status'] == 0) {
//            z($rs['result']);
            if (isHave($rs['result']['address_component']['street_number'])) {
                $path = $rs['result']['address_component']['city'] . $rs['result']['address_component']['district'] . $rs['result']['address_component']['street_number'];
            } else {
                $path = $rs['result']['address_component']['city'] . $rs['result']['address_component']['district'] . $rs['result']['address_component']['street'];
            }
            return $this->JsonReturn('ok', array('location' => $rs['result']['location'], 'address' => $rs['result']['address'], 'component' => $rs['result']['address_component'], 'path' => $path), 1);
        } else {
            return $this->JsonReturn('error');
        }
    }

}

function getSteAdmin($userId, $field = 'real_name') {
    return D('steadmin')->where(array('user_id' => $userId))->getField($field);
}
