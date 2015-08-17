<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of homeAction
 * 默认首页
 * @author xlp
 */
class homeAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
    }

    /*
     * 后台默认首页 
     */

    function index() {
        if (parent::_checkIsAdmin()) {
            //访问统计数据展示
            $p = $this->_getid('p', 1);
            $startTime = $this->_get('stime', '', 'urldecode'); //开始时间
            $endTime = $this->_get('etime', '', 'urldecode'); //结束时间
            $recordId = $this->_getid('id', ''); //
            $rs = array();
            $data = array();
            $code = setEnocde(array('a' => 0, 's' => 1, 'city_id' => steadmin::$adminInfo['city_id']));
            $url = U('steward/wxapi/index') . '?token=' . $code;
            $code = urldecode($code);
            V('db/mongo');
            $db = mongoApi::getInstance();
            $pageShow = 20;
            $where = array();
            $sort = array('infotime' => -1);
            if ($startTime && $endTime) {
                if ($startTime == $endTime) {
                    $endTimeDay = 60 * 60 * 24 - 1;
                } else {
                    $endTimeDay = 0;
                }
                $where['infotime'] = array('$gte' => inTime($startTime), '$lte' => (inTime($endTime) + $endTimeDay));
            }
            if ($startTime) {
                $_GET['stime'] = urlencode($startTime);
            } else {
                unset($_GET['stime']);
            }
            if ($endTime) {
                $_GET['etime'] = urlencode($endTime);
            } else {
                unset($_GET['etime']);
            }
            if (!$recordId) {
                unset($_GET['id']);
            }
            if ($recordId) {
                $where['record_id'] = $recordId;
            }
            $count = $db->table('steadminLog')->where($where)->count();
            $rsLog = $db->table('steadminLog')->where($where)
                    ->order($sort)
                    ->limit($pageShow)
                    ->skip(($p - 1) * $pageShow)
                    ->findAll();
            $this->assign(array('rs' => $rs, 'data' => $data, 'url' => $url, 'token' => md5($code . VCODE), 'p' => $p,
                'recordId' => $recordId,
                'total' => $count, 'rsLog' => $rsLog, 'pageShow' => $pageShow, 'startTime' => $startTime, 'endTime' => $endTime));
            $this->display();
        } else {
            jumpTo(U('index/index'));
        }
    }

    function logdetail() {
        $id = $this->_get('id');
        if (!$id) {
            $this->returnJson = true;
            showError('参数丢失');
        }
        V('db/mongo');
        $db = mongoApi::getInstance();
        $data = $db->table('steadminLog')->where(array('_id' => $db->getId($id)))->find();
        if ($data) {
            unset($data['_id']);
            $data['infotime'] = outTime($data['infotime']);
        }
        $this->assign(array('rs' => $data));
        $this->display();
    }

    function clear() {
        showError(404);
//        V('db/mongo');
//        $db = mongoApi::getInstance();
//        $db->table('steadminLog')->delete(array('infotime'=>array('$gte' => inTime('2015-05-01 00:00:00'), '$lte' => (inTime('2015-05-21 00:00:00')))));
    }

    /*
     * 编辑小管家设置
     */

    function edit() {
        $this->assign(array('rs' => parent::_getAdminSetting('order_setting')));
        $this->display();
    }

    /*
     * 保存资料 
     */

    function save() {
        $objData = array();
        $init = parent::_getDefaultSetting('order_setting');
        foreach ($init as $k => $v) {
            $objData[$k] = $this->_post($k, $v);
        }
        parent::_setAdminSetting('order_setting', $objData);
        $setting = parent::_getAdminSetting();
        foreach ($setting as $key => $val) {
            $setting[$key] = json_decode($val, true);
        }
        F('steward/setting_city_' . steadmin::$adminInfo['city_id'], $setting);
        //===记录操作日志====
        //parent::saveSySLog(2, $setting, 0, array(), '系统设置-编辑');
        //===记录操作日志====
        $this->JsonReturn('操作成功', null, 1);
    }

    /*
     * 编辑企业号资料 
     */

    function qyedit() {
        $this->assign(array('rs' => parent::_getAdminSetting('corp_info')));
        $this->display();
    }

    /*
     * 保存企业号资料 
     */

    function qysave() {

        $objData = array();
        $init = parent::_getAdminSetting('corp_info');
        foreach ($init as $k => $v) {
            $objData[$k] = $this->_post($k, $v);
        }
        parent::_setAdminSetting('corp_info', $objData);
        $setting = parent::_getAdminSetting();
        foreach ($setting as $key => $val) {
            $setting[$key] = json_decode($val, true);
        }
        F('steward/setting_city_' . steadmin::$adminInfo['city_id'], $setting);
        //===记录操作日志====
        //parent::saveSySLog(2, $setting, 0, array(), '系统设置-企业号编辑');
        //===记录操作日志====
        $this->JsonReturn('操作成功', null, 1);
    }

    //分享文案设置
    function share() {
        $set = F('steward/share_order');
        if (!$set) {
            $init = C('share');
            if ($init['share']['title']) {
                $set = $init['share'];
            } else {
                $set = $init['default']['share'];
            }
            F('steward/share_order', $set);
        }
        $this->assign(array('rs' => $set));
        $this->display();
    }

    //保存分享文案
    function saveshare() {
        $title = $this->_post('title', '');
        $imgUrl = $this->_post('imgUrl', '');
        $desc = $this->_post('desc', '');
        if ($title && $imgUrl && $desc) {
            F('steward/share_order', array('title' => $title, 'imgUrl' => strExists($imgUrl, 'http://') ? $imgUrl : getImgUrl($imgUrl), 'desc' => $desc));
            $this->JsonReturn('操作成功', null, 1);
        } else {
            $this->JsonReturn('内容不能为空');
        }
    }

    //日常反馈
    function feedback(){
        header('Cache-Control:no-cache,must-revalidate');  
        header('Pragma:no-cache'); 
        if(load('string')){
            $feedbackConfig = C('feedback');
            $today = Date('Y-m-d', time());

            $type = $this->_getid('fbtype', ''); // 反馈类型
            $deal = $this->_getid('fbresult', ''); //处理结果
            $worker_uid = $this->_getid('worker_uid', ''); // 发布小管家的uid
            $startTime = $this->_get('start', ''); //选择反馈对应时间的A点
            $endTime = $this->_get('end', ''); //选择反馈对应时间的B点
            $ctStartTime = $this->_get('ctstart', ''); //选择反馈插入数据库时间的A点
            $ctEndTime = $this->_get('ctend', ''); //选择反馈插入数据库时间的b点

            $p = $this->_getid('p', 1);

            $pageShow = 20;

            $query = array();
            if(checkNum($type)){
                $query['type'] = intval($type);
            }
            if(!empty($deal) && checkNum($deal)){
                $query['result'] = intval($deal);
            }
            if(checkNum($worker_uid)){
                $query['worker_uid'] = inTime($worker_uid);
            }
            if(isHave($startTime) && isHave($endTime)){
                $query['fb_time[>=]'] = inTime($startTime);
                $query['fb_time[<=]'] = inTime($endTime);
            }
            if(isHave($ctStartTime) && isHave($ctEndTime)){
                $query['ct_time[>=]'] = inTime($ctStartTime);
                $query['ct_time[<=]'] = inTime($ctEndTime) + 60 * 60 * 24 - 1;
            }
            $rs = M('ste_feedback')->where($query)->page($p, $pageShow)->order('ct_time DESC')->findAll();
            $total = M('ste_feedback')->getTotal();

            $fb_types = array();
            foreach ($feedbackConfig['type'] as $key => $value) {
                $fb_types[$value['id']] = $value;
            }
            $results = array();
            foreach ($feedbackConfig['result'] as $key => $value) {
                $results[$value['id']] = $value;
            }

            foreach($rs as $key => $val){
                $rs[$key]['fb_time'] = outTime($rs[$key]['fb_time'], 2);
                $rs[$key]['ct_time'] = outTime($rs[$key]['ct_time']);
                $rs[$key]['type_text'] = $fb_types[$rs[$key]['type']]['name'];
                $rs[$key]['result_text'] = $results[$rs[$key]['result']]['name'];
            }

            $this->assign(
                array(
                    'types' => $feedbackConfig['type'], 
                    'result' => $feedbackConfig['result'], 
                    'today' => $today,
                    'rs' => $rs,
                    'pageShow' => $pageShow,
                    'total' => $total,
                    'p' => $p,
                    'type_id' => $type,
                    'result_id' => $deal,
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'ctStartTime' => $ctStartTime,
                    'ctEndTime' => $ctEndTime
                )
            );
            $this->display();

        }
        
    }
    //添加反馈
    function addFeedback(){
        if(load('string')){
            $feedbackConfig = C('feedback');
            $today = Date('Y-m-d', time());

            $this->assign(
                array(
                    'types' => $feedbackConfig['type'], 
                    'result' => $feedbackConfig['result'], 
                    'today' => $today
                )
            );
            $this->display();
        }else{  
            showError('加载string函数库时发生了错误');
        }
    }

    //保存反馈
    function saveFeedback(){
        if(load('string')){
            $fb_time = removeXss($this->_post('fbtime', ''));
            $fb_type = strtolower(removeXss($this->_post('fbtype', '')));
            $fb_result = removeXss($this->_post('fbresult', ''));
            $fb_content = trim(removeXss($this->_post('feedbackContent', '')));
            $fb_upload = trim($this->_post('fb_upload', ''));

            $feedbackConfig = C('feedback');
            if( !isHave($feedbackConfig['type']) 
                    || !is_array($feedbackConfig['type']) 
                        || !isHave($feedbackConfig['maxLen'])
                            || !is_numeric($feedbackConfig['maxLen']) ){
                return $this->JsonReturn('配置信息错误');
            }

            if( !preg_match('/^[_a-z]+$/', $fb_type) ){
                return $this->JsonReturn('反馈类型错误');
            }
            if( !isHave($feedbackConfig['type'][$fb_type]) ){
                return $this->JsonReturn('无该反馈类型');
            }
            if( !preg_match('/^[_a-z]+$/', $fb_result)){
                return $this->JsonReturn('请选择正确的处理结果');
            }
            if( !isHave($feedbackConfig['result'][$fb_result])){
                return $this->JsonReturn('无该处理结果');
            }
            if( !strtotime($fb_time) ){
                return $this->JsonReturn('时间错误');
            }
            if( strlen($fb_content) <= 0 ){
                return $this->JsonReturn('请填写反馈内容，最大长度2000字符');
            }
            if( strlen($fb_content) >= $feedbackConfig['maxLen'] ){
                return $this->JsonReturn('反馈内容超出限制');
            }
            /* TODO
             * 对于upload应该怎么样检查
             */
            $feedback = array(
                'worker_uid' => steadmin::$adminInfo['user_id'],
                'type' => $feedbackConfig['type'][$fb_type]['id'],
                'result' => $feedbackConfig['result'][$fb_result]['id'],
                'content' => $fb_content,
                'fb_time' => inTime($fb_time),
                'fb_upload' => $fb_upload
            );

            if(D('feedback')->save($feedback)){
                return $this->JsonReturn('保存成功，可继续添加反馈', null, 1);
            }else{
                return $this->JsonReturn('保存失败');
            }
        }else{
            $this->JsonReturn('加载string函数库时发生了错误');
        }      
    }

    //查看反馈
    function viewFeedback(){
        header('Cache-Control:no-cache,must-revalidate');  
        header('Pragma:no-cache');   

        $fid = $this->_getid('fid', 0);

        $feedbackConfig = C('feedback');

        $fb_types = array();
        foreach ($feedbackConfig['type'] as $key => $value) {
            $fb_types[$value['id']] = $value;
        }
        $results = array();
        foreach ($feedbackConfig['result'] as $key => $value) {
            $results[$value['id']] = $value;
        }
        D('feedback');
        $m = new feedbackModel();
        $feedback = $m->getFeedback($fid);
        $feedback['fb_time'] = outTime($feedback['fb_time'], 2);
        $feedback['ct_time'] = outTime($feedback['ct_time'], 2);

        $this->assign(array(
            'feedback' => $feedback,
            'type' => $fb_types,
            'results' => $results
        ));
        $this->display();
    }

    function upateFeedbackStatus(){
        if(isAjax()){
            $fid = $this->_postid('fid', 0);
            if(D('feedback')->update(array('result' => 1), array('fid' => $fid))){
                $this->JsonReturn('更新成功', null, 1);
            }else{
                $this->JsonReturn('更新失败');
            }
        }else{
            showError('访问方式错误');
        }       
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
