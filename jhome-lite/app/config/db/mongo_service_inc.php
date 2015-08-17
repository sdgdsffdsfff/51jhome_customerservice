<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * mongoDB参数设置
 *
 */
return array(
    'mongo' => array(
        'host' => '127.0.0.1',
        'port' => '27017',
        'user' => '',
        'password' => '',
        'dbname' => 'serversystem',
        'save_errlog' => true, //是否保存错误日志
        'option' => array('connect' => true), // 参数 
    )
);
