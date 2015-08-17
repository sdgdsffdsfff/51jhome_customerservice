<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

class feedbackModel extends model {
    function __construct() {
        parent::__construct();
        $this->dbTable = 'ste_feedback';
    }

    function getFeedback($fid){
        $query = array(
            'fid' => $fid
            );
        $row = $this->query('SELECT a.*, b.real_name, b.username FROM __TABLE__ AS a INNER JOIN __PRE__ste_user AS b ON a.worker_uid = b.user_id WHERE a.fid = '.intval($fid), true);
        return $row;
    }

}