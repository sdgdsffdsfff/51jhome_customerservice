<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of loginAction 用户登录
 *
 * @author xlp
 */
class loginAction extends commonAction {

    function __construct() {
        parent::__construct();
    }

    function index() {
        if ($this->_checkLogin(true)) {
            if (parent::_checkIsAdmin()) {
                jumpTo(U('home/index'));
            } else {
                jumpTo(U('index/index'));
            }
        }
        $this->assign('refer', $this->_get('refer', urlencode(U('home/index'))));
        $this->display('index');
    }

    function _empty() {
        return $this->index();
    }

    function ajax() {
        $this->display('ajaxlogin');
    }

    function ajaxlogin() {
        if (!formCheck()) {
            $this->JsonReturn('表单提交有误，请刷新页面再试');
        }
        //接受参数
        $user = $this->_post('user');
        $pass = $this->_post('password');
        $refer = urldecode($this->_post('refer'));
        $remember = $this->_postid('remember', 0);
        if (!$user || !$pass) {
            $this->JsonReturn('账号或密码为空', null, 0);
        }
        $rs = D('steadmin')->where(array('username' => $user, 'psw' => D('steadmin')->setUserPassword($pass), 'status' => 1))->find();
        //===记录操作日志====
        parent::saveSySLog(5, array('username' => $user), 0, array(), '后台登陆');
        //===记录操作日志====
        if ($rs) {
            if (!$rs['effective'] || ($rs['effective'] && $rs['effective'] - TIME > 0)) {//帐号无期限或者有期限并且还未过期
                if (in_array($rs['groupid'], array(3, 4))) {
                    $this->JsonReturn('抱歉，您的组别无法登陆');
                }
                //修改登录信息
                D('steadmin')->setUserLogin($rs, $remember);
                formClear();
                $this->JsonReturn('ok', array('user_id' => $rs['user_id'], 'refer' => $refer), 1);
            } else {
                $this->JsonReturn('抱歉，你的帐号已过期，暂时无法登陆', null, 0);
            }
        } else {
            //===记录操作日志====
            parent::saveSySLog(5, array('username' => $user, 'psw' => $pass), 0, array(), '后台登陆-失败');
            //===记录操作日志====
            $this->JsonReturn('帐号不存在或者密码错误', null, 0);
        }
    }

    /*
     * 退出登录 
     */

    function logout() {
        steadmin::setUserLoginOut();
        jumpTo(U('login/index'));
    }

}
