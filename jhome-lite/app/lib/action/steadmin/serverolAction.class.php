<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

class serverolAction extends commonAction {
    function __construct() {
        parent::__construct();
        parent::_checkLogin();
    }

    function server(){
        $uid = $this->_get('uid', '') | '123';
        $uname = $this->_get('uname', '') | '露露';
        $this->assign(array('uid' => $uid, 'uname' => $uname));
        $this->display();
    }

    function chathistory(){
        $page_num = 10;
        V('db/mongo');
        $db = mongoApi::getInstance('db/mongo_service');
        $clientid = $this->_get('clientid', '');
        $serverid = $this->_get('serverid', '');
        $page = $this->_getid('p', 1);
        if( !empty($clientid) && !empty($serverid) ){
            $ret = $db->table('chats')->find(array("clientid" => $clientid, "serverid" => $serverid), array(), $page_num);
            if(!empty($ret) || count($ret) > 0){
                $tmp_ret = $ret['contents'];
                $allchats = array();
                foreach ($tmp_ret as $key => $value) {
                    $allchats[$value['time']] = $value;
                }
                krsort($allchats, SORT_NUMERIC);
                $chats = array_slice($allchats, ($page-1)*$page_num, $page_num);
                $sortedchats = array();
                foreach ($chats as $key => $value) {
                    $sortedchats[$value['time']] = $value;
                }
                ksort($sortedchats, SORT_NUMERIC);
                $this->assign(array(
                    "chats" => $sortedchats,
                    "total" => count($ret['contents']),
                    "page" => $page,
                    "pageshow" => $page_num,
                    "servername" => $ret['servername'],
                    "clientname" => $ret['clientname']
                ));
                $this->display();
            }else{
                echo '无聊天记录';
            }            
        }else{
            echo 'null';
        }
    }

    function client(){
        $uid = $this->_getid('uid');
        $uname = $this->_get('uname');
        $this->assign(array('uid' => $uid, 'uname' => $uname));
        $this->display();
    }
}