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
        'host' => '115.29.177.148',
        'port' => '27017',
        'user' => 'supper',
        'password' => 's5Ke8Cu7',
        'dbname' => 'apptest',
        'save_errlog' => true, //是否保存错误日志
        'option' => array('connect' => true), // 参数 
    )
);
