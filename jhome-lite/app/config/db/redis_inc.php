<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * redisDB参数设置
 *
 */
return array(
    'redis' => array(
        'host' => '127.0.0.1',
        'port' => '6379',
        'save_errlog' => true, //是否保存错误日志
    )
);
