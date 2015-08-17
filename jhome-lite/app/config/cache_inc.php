<?php
if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 缓存配置文件
 */
return array(
        'open' => false, //开启缓存
        'time' => 86400, //缓存有效时间，单位：秒，默认为24小时
        'dir' => 'cache'//缓存目录
    );
