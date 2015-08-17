<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of servicelogAction
 * 客服日报
 * @author xlp
 */
class servicelogAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
    }

    //日常反馈
    function index() {
        load('string');
        $post_startTime = $this->_post('start', '');
        $post_endTime = $this->_post('end', '');
        if (isHave($post_startTime) && isHave($post_endTime)) {
            return jumpTo(U('feedback/index', getSearchUrl(array('start' => $post_startTime, 'end' => $post_endTime))));
        }
        $feedbackConfig = C('steward/service_log');
        $today = date('Y-m-d', TIME);
        $type_id = $this->_getid('fbtype', 0); // 反馈类型
        $status_id = $this->_getid('fbresult', 0); //处理结果
        $worker_uid = $this->_getid('worker_uid', 0); // 发布小管家的uid
        $startTime = $this->_get('start', ''); //选择反馈对应时间的A点
        $endTime = $this->_get('end', ''); //选择反馈对应时间的B点
        $ctStartTime = $this->_get('ctstart', ''); //选择反馈插入数据库时间的A点
        $ctEndTime = $this->_get('ctend', ''); //选择反馈插入数据库时间的b点
        $p = $this->_getid('p', 1);
        $pageShow = 20;
        $query = array();
        if ($type_id) {
            $query['type_id'] = $type_id - 1;
        }
        if ($status_id) {
            $query['status_id'] = $status_id - 1;
        }
        if ($worker_uid) {
            $query['worker_uid'] = $worker_uid;
        }
        if (isHave($startTime) && isHave($endTime)) {
            $query['fb_time[>=]'] = inTime($startTime);
            $query['fb_time[<=]'] = inTime($endTime);
        }
        if (isHave($ctStartTime) && isHave($ctEndTime)) {
            $query['ct_time[>=]'] = inTime($ctStartTime);
            $query['ct_time[<=]'] = inTime($ctEndTime) + 60 * 60 * 24 - 1;
        }

        $rs = D('serviceLog')->where($query)->page($p, $pageShow)->order('ct_time DESC')->findAll();
        $total = D('serviceLog')->getTotal();

        $fb_types = $feedbackConfig['type'];
        $fb_status = $feedbackConfig['status'];

        foreach ($rs as $key => $val) {
            $rs[$key]['fb_time'] = outTime($val['fb_time'], 2);
            $rs[$key]['ct_time'] = outTime($val['ct_time']);
            $rs[$key]['worker_name'] = parent::_getAdminName($val['worker_uid']);
            $rs[$key]['type_text'] = $fb_types[$val['type_id']]['name'];
            $rs[$key]['status_text'] = $fb_status[$val['status_id']];
        }
        $this->assign(
                array(
                    'types' => $feedbackConfig['type'],
                    'status' => $feedbackConfig['status'],
                    'today' => $today,
                    'rs' => $rs,
                    'pageShow' => $pageShow,
                    'total' => $total,
                    'p' => $p,
                    'type_id' => $type_id,
                    'status_id' => $status_id,
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'ctStartTime' => $ctStartTime,
                    'ctEndTime' => $ctEndTime
                )
        );
        $this->display();
    }

    //添加反馈
    function add() {
        load('string');
        $feedbackConfig = C('steward/service_log');
        $today = date('Y-m-d', TIME);

        $this->assign(
                array(
                    'types' => $feedbackConfig['type'],
                    'status' => $feedbackConfig['status'],
                    'today' => $today
                )
        );
        $this->display();
    }

    //保存反馈
    function post() {
        load('string');
        $feedback = array();
        $feedback['fb_time'] = $this->_post('fbtime', '');
        $feedback['fb_type'] = $this->_postid('fbtype', 0);
        $feedback['fb_status'] = $this->_postid('fbstatus', 0);
        $feedback['fb_content'] = $this->_post('fbcontent', '');
        $feedback['fb_upload'] = $this->_post('fbupload', '');
        $feedback['order_id'] = $this->_postid('order_id');
        $feedback['phone'] = $this->_post('phone');
        $feedback['username'] = $this->_post('username');

        $feedbackConfig = C('steward/service_log');
        if (!isHave($feedbackConfig['type']) || !is_array($feedbackConfig['type']) || !isHave($feedbackConfig['maxLen']) || !is_numeric($feedbackConfig['maxLen'])) {
            return $this->JsonReturn('配置信息错误');
        }
        T('content/validate');
        $validation = array(
            array('fb_time', 'required', '请选择时间'),
            array('fb_type', 'required', '请选择类型'),
            array('fb_status', 'required', '请选择处理状态'),
            array('fb_content', 'required', '填填写反馈'),
            array('fb_content', 'max_length', '反馈内容请不要超过' . $feedbackConfig['maxLen'] . '个字符', $feedbackConfig['maxLen']),
        );

        if (!isHave($feedbackConfig['type'][$feedback['fb_type']])) {
            return $this->JsonReturn('无该反馈类型' . $feedback['fb_type']);
        }
        if (!isHave($feedbackConfig['status'][$feedback['fb_status']])) {
            return $this->JsonReturn('无该处理结果');
        }

        if (!validate::check($validation, $feedback)) {
            return $this->JsonReturn(validate::getError());
        }

        if(isHave($feedback['phone'])){
            $phoneValidation = array(
                array('phone', 'phone', '请填写正确的电话号码')
                );
            if(!validate::check($phoneValidation, $feedback)){
                return $this->JsonReturn(validate::getError());
            }
        }

        if(isHave($feedback['username'])){
            $usernameValidation = array(
                array('username', 'username', '请填写正确的电话号码')
                );
            if(!validate::check($usernameValidation, $feedback)){
                return $this->JsonReturn(validate::getError());
            }
        }

        /* TODO
         * 对于upload应该怎么样检查
         */
        $fb_insert = array(
            'worker_uid' => steadmin::$adminInfo['user_id'],
            'type_id' => $feedback['fb_type'],
            'status_id' => $feedback['fb_status'],
            'feedback' => $feedback['fb_content'],
            'fb_time' => inTime($feedback['fb_time']),
            'upload' => $feedback['fb_upload'],
            'ct_time' => TIME,
            'order_id' => $feedback['order_id'],
            'phone' => $feedback['phone'],
            'username' => $feedback['username']
        );

        if (D('serviceLog')->insert($fb_insert)) {
            //===记录操作日志====
            parent::saveSySLog(1, $fb_insert, 0, array(), '客服日报-添加');
            //===记录操作日志====
            return $this->JsonReturn('保存成功，可继续添加反馈', null, 1);
        } else {
            return $this->JsonReturn('保存失败');
        }
    }

    //查看反馈
    function detail() {
        $fid = $this->_getid('fid', 0);
        $feedbackConfig = C('steward/service_log');
        $feedback = D('serviceLog')->getServiceLog($fid);
        $feedback['fb_time'] = outTime($feedback['fb_time'], 2);
        $feedback['ct_time'] = outTime($feedback['ct_time'], 2);
        $this->assign(array(
            'servicelog' => $feedback,
            'type' => $feedbackConfig['type'],
            'status' => $feedbackConfig['status']
        ));
        $this->display();
    }

    function detail_card() {
        $fid = $this->_getid('fid', 0);
        $feedbackConfig = C('steward/service_log');
        $feedback = D('serviceLog')->getServiceLog($fid);
        $feedback['fb_time'] = outTime($feedback['fb_time'], 2);
        $feedback['ct_time'] = outTime($feedback['ct_time'], 2);
        $this->assign(array(
            'rs' => $feedback,
            'type' => $feedbackConfig['type'],
            'status' => $feedbackConfig['status']
        ));
        $this->display();
    }

    //更新反馈的状态
    function servicelog_status() {
        if (isAjax()) {
            $fid = $this->_postid('fid', 0);
            if (D('serviceLog')->update(array('status_id' => 0), array('fid' => $fid))) {
                $this->JsonReturn('更新成功', null, 1);
            } else {
                $this->JsonReturn('更新失败');
            }
        } else {
            showError('访问方式错误');
        }
    }

    function edit() {
        $fid = $this->_getid('fid', 0);
        $feedbackConfig = C('steward/service_log');
        $feedback = D('serviceLog')->getServiceLog($fid);
        $feedback['fb_time'] = outTime($feedback['fb_time'], 2);
        $feedback['ct_time'] = outTime($feedback['ct_time'], 2);
        $this->assign(array(
            'servicelog' => $feedback,
            'types' => $feedbackConfig['type'],
            'status' => $feedbackConfig['status']
        ));
        $this->display();
    }
    // 保存
    function save() {
        $fid = $this->_postid('fid', 0);
        if (!$fid) {
            showError('参数丢失');
        }
        $feedbackConfig = C('steward/service_log');
        $servicelog = array();
        $servicelog['fb_time'] = $this->_post('fbtime', '');
        $servicelog['type_id'] = $this->_postid('fbtype', 0);
        $servicelog['status_id'] = $this->_postid('fbstatus', 0);
        $servicelog['feedback'] = $this->_post('fbcontent', '');
        $servicelog['upload'] = $this->_post('fbupload');
        $servicelog['order_id'] = $this->_postid('order_id');
        $servicelog['phone'] = $this->_post('phone');
        $servicelog['username'] = $this->_post('username');

        T('content/validate');        
        $validation = array(
            array('fb_time', 'required', '请选择时间'),
            array('type_id', 'required', '请选择类型'),
            array('status_id', 'required', '请选择处理状态'),
            array('feedback', 'required', '填填写反馈'),
            array('feedback', 'max_length', '反馈内容请不要超过' . $feedbackConfig['maxLen'] . '个字符', $feedbackConfig['maxLen']),
        );
        if (!validate::check($validation, $servicelog)) {
            return $this->JsonReturn(validate::getError());
        }

        if(isHave($servicelog['phone'])){
            $phoneValidation = array(
                array('phone', 'phone', '请填写正确的电话号码')
                );
            if(!validate::check($phoneValidation, $servicelog)){
                return $this->JsonReturn(validate::getError());
            }
        }

        if(isHave($servicelog['username'])){
            $usernameValidation = array(
                array('username', 'username', '请填写正确的电话号码')
                );
            if(!validate::check($usernameValidation, $servicelog)){
                return $this->JsonReturn(validate::getError());
            }
        }

        if (!isHave($feedbackConfig['type'][$servicelog['type_id']])) {
            return $this->JsonReturn('无该反馈类型' . $servicelog['type_id']);
        }
        if (!isHave($feedbackConfig['status'][$servicelog['status_id']])) {
            return $this->JsonReturn('无该处理结果');
        }

        $servicelog['fb_time'] = inTime($servicelog['fb_time']);
        D('serviceLog')->update($servicelog, array('fid' => $fid));
        $this->JsonReturn('保存成功', null, 1);
    }

    function daily(){
        $today = $this->_get('date', '');
        if(!isHave($today)){
            return showError('请传入一个日期');
        }

        $query = array();
        $timestamp = inTime($today);
        $query['fb_time[>=]'] = $timestamp;
        $query['fb_time[<]'] = $timestamp + 86400;//

        $rs = D('serviceLog')->where($query)->findAll();

        $feedbackConfig = C('steward/service_log');

        foreach ($rs as $key => $value) {
            $rs[$key]['fb_time'] = outTime($value['fb_time'], 2);
            $rs[$key]['ct_time'] = outTime($value['ct_time'], 2);
        }

        $this->assign(array(
            'today' => $today,
            'rs' => $rs,
            'type' => $feedbackConfig['type'],
            'status' => $feedbackConfig['status']
        ));
        $this->display();
    }

    function delete() {
        $id = $this->_postid('id');
        if (!$id) {
            showError('参数丢失');
        }
        if (!parent::_checkIsAdmin()) {
            showError('没有编辑权限');
        }
        D('serviceLog')->delete(array('fid' => $id));
        //===记录操作日志====
        parent::saveSySLog(3, array(), $id, array('id' => $id), '客服日报-删除');
        //===记录操作日志====
        $this->JsonReturn('ok', null, 1);
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
