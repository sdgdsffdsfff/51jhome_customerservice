<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of memberAction
 *
 * @author xlp
 */
class memberAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
        $this->sex = array('未知', '男', '女');
        $this->status = array(0 => '<span class="red">无效</span>', 1 => '正常',
            2 => '<span class="grey">冻结</span>', 3 => '<span class="red">取消关注</span>');
        $this->villageType = array(0 => '未入住', 1 => '已入住');
        $this->assign(array('status' => $this->status,
            'villageType' => $this->villageType,
            'regType' => array(0 => '主动关注', 1 => '二维码扫描', 2 => '网站二维码', 3 => '地推', 4 => '第三方', 5 => '商家推广'),
            'auth' => array('否', '<span class="red">是</span>')));
    }

    function msg() {
        $uid = $this->_getid('uid');
        if (!$uid) {
            $this->returnJson = false;
            $this->JsonReturn('参数丢失');
        }
        $checkTime = TIME - (24 * 60 * 60 * 2);
        //检查用户是否48小时内有回复
        $checkHas = M('access_log')->field('id')->where(array('uid' => $uid, 'create_time[>=]' => $checkTime, 'create_time[<=]' => TIME))->find();
        if (!$checkHas) {
            $this->returnJson = false;
            $this->JsonReturn('用户48小时内没有互动，无法发送消息');
        }
        $this->assign(array('uid' => $uid, 'face' => C('face')));
        $this->display();
    }

    function send() {
        $uid = $this->_post('uid');
        $content = parent::_postContent('content');
        if (!$uid) {
            $this->JsonReturn('参数丢失', array('code' => '', 'msg' => ''), 0);
        }
        if (!$content) {
            $this->JsonReturn('回复内容不能为空', array('code' => '', 'msg' => ''), 0);
        }
        $touser = D('member')->getOpenidByUids($uid);
        if (!$touser) {
            $this->JsonReturn('用户不存在', array('code' => '', 'msg' => ''), 0);
        }
        $sendInfo = array(
            'touser' => $touser,
            'msgtype' => 'text',
            'text' => array(
                'content' => $content
            )
        );
//        z($sendInfo);
        T('weixin/weixin.api');
        $weixinMsgApi = new weixinMsgApi();
        if ($weixinMsgApi->sendCustomMessage($sendInfo)) {
            return $this->JsonReturn('回复成功', null, 1);
        } else {
            return $this->JsonReturn('回复失败', array('code' => $weixinMsgApi->errCode, 'msg' => $weixinMsgApi->errMsg), 0);
        }
    }

    function detail() {
        $uid = $this->_getid('uid');
        $showAll = $this->_getid('all', 0);
        if (!$uid) {
            $this->returnJson = false;
            showError('用户参数丢失');
        }
        $rs = D('member')->getUserInfoById($uid, 'all');
        if ($rs) {
            $rs['village_name'] = $rs['village_id'] ? parent::getVillageName($rs['village_id']) : '';
        }
        $this->assign(array('rs' => $rs, 'sex' => $this->sex, 'showAll' => $showAll));
        $this->display();
    }

}
