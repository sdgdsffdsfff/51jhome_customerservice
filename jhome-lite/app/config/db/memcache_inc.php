<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * memcache参数设置
 *
 */
return array(
    'memcache' => array(
        array(
            'host' => '127.0.0.1',
            'port' => '11211',
            'save_errlog' => true, //是否保存错误日志
        ),
    )
);
