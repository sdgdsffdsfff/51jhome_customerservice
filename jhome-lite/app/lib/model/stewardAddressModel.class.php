<?php

defined('IN_XLP') or exit('Access Denied');

class stewardAddressModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'address';
    }

    /**
     * 添加收货地址
     */
    public function add($uid, $vid, $data = array()) {

        foreach ($data as $k => $v) {
            $data[$k] = trim($v);
        }
        if (empty($data['tel']) or empty($data['name']) or empty($data['province'])
                or empty($data['city']) or empty($data['county']) or empty($data['detail'])) {
            return false;
        }

        $data['uid'] = $uid;
        $data['vid'] = $vid;
        $data['last_use'] = TIME;

        return $this->insert($data);
    }

    /**
     * 编辑收货地址
     */
    public function edit($id, $uid, $data = array()) {
        //检查用户地址
        $rs = $this->field('`uid`')->where(array('id' => $id, 'uid' => $uid))->find();
        if (!$rs) {
            return false;
        }

        foreach ($data as $k => $v) {
            $data[$k] = trim($v);
        }
        if (empty($data['tel']) or empty($data['name']) or empty($data['province'])
                or empty($data['city']) or empty($data['county']) or empty($data['detail'])) {
            return false;
        }

        $data['last_use'] = TIME;
        $this->update($data, array('id' => $id));

        return true;
    }

    /*
     * 删除地址
     */

    public function del($id, $uid) {
        //检查用户地址
        $rs = $this->field('`uid`')->where(array('id' => $id, 'uid' => $uid))->find();
        if (!$rs) {
            return false;
        }
        return $this->delete(array('id' => $id));
    }

    /*
     * 默认地址信息
     */

    public function default_addr($uid, $vid) {
        $serviceId = M('village')->where(array('vid' => $vid))->getField('service_id');
        $result = $this->query('SELECT a.* FROM  __TABLE__  AS a LEFT JOIN  __PRE__village AS b ON a.vid=b.vid '
                . ' where a.uid=' . $uid . ' AND b.service_id=' . $serviceId . ' ORDER BY a.last_use DESC LIMIT 1', true);
        return $result;
    }

    /*
     * 全部地址信息
     */

    public function all($uid, $field = '*') {
        $result = $this
                ->field($field)
                ->where(array('uid' => $uid, 'vid[>]' => 0))
                ->order('`last_use` DESC')
                ->findAll();

        return $result;
    }

    /*
     * 根据地址id获得详细信息
     */

    public function getAddrById($id, $uid, $field = '*') {
        $rs = $this
                ->field($field)
                ->where(array('id' => $id, 'uid' => $uid))
                ->find();
        return $rs;
    }

}
