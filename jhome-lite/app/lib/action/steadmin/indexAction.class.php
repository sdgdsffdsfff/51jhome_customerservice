<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of indexAction
 * 默认首页
 * @author xlp
 */
class indexAction extends commonAction {

    function __construct() {
        parent::__construct();
    }

    function index() {
        if (!$this->_checkLogin(true)) {
            jumpTo(U('login/index'));
        }
        if (parent::_checkIsAdmin() && steadmin::$adminInfo['isAdmin']) {
            jumpTo(U('home/index'));
        } else {
            $this->assign(array('group' => C('steward/admin', 'group')));
            $this->display('index');
        }
    }

    function _empty() {
        return $this->index();
    }

}
