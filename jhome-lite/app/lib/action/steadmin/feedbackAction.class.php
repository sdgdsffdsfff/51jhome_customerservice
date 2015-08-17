<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of homeAction
 * 默认首页
 * @author xlp
 */
class feedbackAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
    }

    //日常反馈
    function index(){
        load('string');
        $post_startTime = $this->_post('start', '');
        $post_endTime = $this->_post('end', '');
        if(isHave($post_startTime) && isHave($post_endTime)){
            return jumpTo(U('feedback/index', getSearchUrl(array('start' => $post_startTime, 'end' => $post_endTime))));
        }
        $feedbackConfig = C('feedback');
        $today = Date('Y-m-d', time());

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
        if($type_id){
            $query['type_id'] = $type_id - 1;
        }
        if($status_id){
            $query['status_id'] = $status_id - 1;
        }
        if($worker_uid){
            $query['worker_uid'] = $worker_uid;
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

        $fb_types = $feedbackConfig['type'];
        $fb_status = $feedbackConfig['status'];

        foreach($rs as $key => $val){
            $rs[$key]['fb_time'] = outTime($rs[$key]['fb_time'], 2);
            $rs[$key]['ct_time'] = outTime($rs[$key]['ct_time']);
            $rs[$key]['type_text'] = $fb_types[$rs[$key]['type_id']]['name'];
            $rs[$key]['status_text'] = $fb_status[$rs[$key]['status_id']];
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
    function add(){
        load('string');
        $feedbackConfig = C('feedback');
        $today = Date('Y-m-d', TIME);

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
    function save(){
        if(load('string')){
            $feedback = array();
            $feedback['fb_time'] = $this->_post('fbtime', '');
            $feedback['fb_type'] = $this->_postid('fbtype', 0);
            $feedback['fb_status'] = $this->_postid('fbstatus', 0);
            $feedback['fb_content'] = $this->_post('fbcontent', '');
            $feedback['fb_upload'] = $this->_post('fbupload', '');

            $feedbackConfig = C('feedback');
            if( !isHave($feedbackConfig['type']) 
                    || !is_array($feedbackConfig['type']) 
                        || !isHave($feedbackConfig['maxLen'])
                            || !is_numeric($feedbackConfig['maxLen']) ){
                return $this->JsonReturn('配置信息错误');
            }

            
            T('content/validate');
            $validation = array(
                array('fb_time', 'required', '请选择时间'),
                array('fb_type', 'required', '请选择类型'),
                array('fb_status', 'required', '请选择处理状态'),
                array('fb_content', 'required', '填填写反馈'),
                array('fb_content', 'max_length', '反馈内容请不要超过'.$feedbackConfig['maxLen'].'个字符', $feedbackConfig['maxLen'])
            );

            if( !isHave($feedbackConfig['type'][$feedback['fb_type']]) ){
                return $this->JsonReturn('无该反馈类型'.$feedback['fb_type']);
            }
            if( !isHave($feedbackConfig['status'][$feedback['fb_status']])){
                return $this->JsonReturn('无该处理结果');
            }

            if (!validate::check($validation, $feedback)) {
                return $this->JsonReturn(validate::getError());
            }
            // validate 中并没有如 2015-08-03 这样的无时间格式检查
            if( !strtotime($feedback['fb_time']) ){
                return $this->JsonReturn('时间错误');
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
                'ct_time' => TIME
            );

            if(D('feedback')->insert($fb_insert)){
                return $this->JsonReturn('保存成功，可继续添加反馈', null, 1);
            }else{
                return $this->JsonReturn('保存失败');
            }
        }else{
            $this->JsonReturn('加载string函数库时发生了错误');
        }      
    }

    //查看反馈
    function detail(){
        header('Cache-Control:no-cache,must-revalidate');  
        header('Pragma:no-cache');   

        $fid = $this->_getid('fid', 0);

        $feedbackConfig = C('feedback');

        $feedback = D('feedback')->getFeedback($fid);
        $feedback['fb_time'] = outTime($feedback['fb_time'], 2);
        $feedback['ct_time'] = outTime($feedback['ct_time'], 2);

        $this->assign(array(
            'feedback' => $feedback,
            'type' => $feedbackConfig['type'],
            'status' => $feedbackConfig['status']
        ));
        $this->display();
    }
    //更新反馈的状态
    function update_feedback_status(){
        if(isAjax()){
            $fid = $this->_postid('fid', 0);
            if(D('feedback')->update(array('status_id' => 0), array('fid' => $fid))){
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
