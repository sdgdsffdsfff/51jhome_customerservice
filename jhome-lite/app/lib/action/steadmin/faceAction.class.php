<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of faceAction
 *
 * @author xlp
 */
class faceAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
        $this->assign(array('rs' => C('face')));
    }

    function index() {
        $id = $this->_get('id', 'content');
        $this->assign(array('id' => $id));
        $this->display();
    }

}
