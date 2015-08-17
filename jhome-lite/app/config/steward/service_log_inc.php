<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 分享类型配置文件
 */
return array(
    'type' => array(
        array('name' => '技术故障'),
        array('name' => '物流配送'),
        array('name' => '使用问题'),
        array('name' => '下单有误'),
        array('name' => '用户投诉'),
        array('name' => '用户答疑'),
        array('name' => '退款'),
        array('name' => '退换货')
    ),
    'status' => array('已处理', '未处理'),
    'maxLen' => 2000
);