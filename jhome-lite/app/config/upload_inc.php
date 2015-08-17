<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 上传配置文件
 */
return array(
    'dir' => 'upload', //上传目录
    'maxsize' => 3, //单位M
    'dirtype' => 3, //上传保存目录 1：Ymd、2：Y-m-d、3：Y/md、4：Y/m/d，默认为3
    'pic_type' => 'jpg|png|gif|jpeg|bmp|jpe',
    'attach_type' => 'mp3|wav|wma|ppt|zip|rar',
    'yun' => array(//云存储设置
        'open' => true,
        'bucket' => 'jhimg',
        'user' => 'reg2010',
        'pwd' => '@Xlp123456',
        'dir' => 'yun',
        'url' => 'http://img.51jhome.com/'
    ),
    'thumb_size' => array(//缩略图尺寸,t:对应云储存上的缩略图版本
        array('w' => 150, 'h' => 150, 't' => '!p1'), //正方形,
        array('w' => 640, 'h' => 0, 't' => '!p2'), //高度自适应
        array('w' => 300, 'h' => 0, 't' => '!p3'), //高度自适应
        array('w' => 480, 'h' => 336, 't' => '!p4'), //新版商品大图
    )
);
