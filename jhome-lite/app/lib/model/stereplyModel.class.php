<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of stereplyModel
 *
 * @author xlp
 */
class stereplyModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'ste_reply';
    }

    function checkReplyIsExists($str = '', $cityId = 0) {
        if (!$str) {
            return false;
        }
        return $this->field('id,fid,reply_type')->where(array('keyword' => $str, 'city_id' => $cityId))->find();
    }

}
