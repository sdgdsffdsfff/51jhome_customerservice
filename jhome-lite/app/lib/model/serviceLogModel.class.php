<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

class serviceLogModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'service_log';
    }

    function getServiceLog($fid) {
        return $this->query('SELECT a.fid, a.worker_uid, a.type_id, a.status_id, a.feedback as servicelog, a.upload, a.fb_time, a.ct_time, b.real_name, b.username, a.order_id, a.phone, a.username as client_username FROM __TABLE__ AS a INNER JOIN __PRE__ste_user AS b ON a.worker_uid = b.user_id WHERE a.fid = ' . intval($fid), true);
    }

}
