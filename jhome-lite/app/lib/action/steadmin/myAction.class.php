<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of myAction
 *
 * @author xlp
 */
class myAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
    }

    function index() {
        $this->edit();
    }

    function edit() {
        $this->display('edit');
    }

    function savepsw() {
        $objData = array(
            'psw' => $this->_post('psw', '')
        );
        //内容规则检查
        T('content/validate');
        $validate = array(
            array('psw', 'min_length', '密码长度必须大于4位', 4),
        );
        if (!validate::check($validate, $objData)) {
            $this->JsonReturn(validate::getError());
        }
        if ($objData['psw'] != $this->_post('repsw')) {
            $this->JsonReturn('两次密码不一致，请检查');
        }
        if ($objData['psw']) {
            $psw = D('admin')->setUserPassword($objData['psw']);
            if ($psw != steadmin::$adminInfo['psw']) {
                D('steadmin')->update(array('psw' => $psw), array('user_id' => steadmin::$adminInfo['user_id']));
                D('steadmin')->setUserLogin(array('user_id' => steadmin::$adminInfo['user_id'], 'psw' => $psw), 0, false);
                //===记录操作日志====
                parent::saveSySLog(2, array(), 0, array('user_id' => steadmin::$adminInfo['user_id']), '修改密码-编辑');
                //===记录操作日志====
                $this->JsonReturn('操作成功', null, 1);
            }
        }
        $this->JsonReturn('密码未变更', null, 0);
    }

    /*
     * 后台个性化设置
     */

    function setting() {
        $data = $this->_post('data');
        if (!$data || !is_array($data)) {
            $this->JsonReturn('参数错误');
        }
        $adminMenuSetting = F('content/setting/steward_id_' . steadmin::$adminInfo['user_id']);
        $list = array('navbar', 'nav', 'header', 'navbg', 'music');
//        z($data);
        foreach ($data as $k => $v) {
            if (!in_array($k, $list)) {
                $this->JsonReturn('参数错误');
            }
            $adminMenuSetting[$k] = $v;
        }
        F('content/setting/steward_id_' . steadmin::$adminInfo['user_id'], $adminMenuSetting);
//        z($objData);
        $this->JsonReturn('ok', $adminMenuSetting, 1);
    }

}
