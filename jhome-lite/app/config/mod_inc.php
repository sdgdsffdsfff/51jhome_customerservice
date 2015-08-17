<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 模块简写，便于检查和分拣消息
 */
return array(
    'notice' => array(
        'title' => '通知',
        'key' => 'nid',
        'url' => U('', array('form' => 'message'), true)
    ), //通知
    'forum' => array(
        'title' => '社区',
        'key' => 'fid',
        'url' => U('forum/detail', array('form' => 'message'), true)
    ), //社区
    'forum_post' => array(
        'title' => '社区回复',
        'key' => 'fid',
        'url' => U('forum/detail', array('form' => 'message'), true)
    ), //社区回复
    'forum_post_zone' => array(
        'title' => '社区生活圈回复',
        'key' => 'fid',
        'url' => U('forum/detail', array('form' => 'message','type'=>1), true)
    ), //社区回复
    'carpool' => array(
        'title' => '拼车',
        'key' => 'cid',
        'url' => U('carpool/detail', array('form' => 'message'), true)
    ), //拼车
    'carpool_post' => array(
        'title' => '拼车回复',
        'key' => 'cid',
        'url' => U('carpool/detail', array('form' => 'message'), true)
    ), //拼车回复
    'housekeep' => array(
        'title' => '家政',
        'key' => 'hid',
        'url' => U('homemaking/detail', array('form' => 'message'), true)
    ), //家政
    'housekeep_post' => array(
        'title' => '家政回复',
        'key' => 'hid',
        'url' => U('homemaking/detail', array('form' => 'message'), true)
    ), //家政回复
    'sechand' => array(
        'title' => '二手置换',
        'key' => 'sid',
        'url' => U('sechand/detail', array('form' => 'message'), true)
    ), //二手置换
    'sechand_post' => array(
        'title' => '二手置换回复',
        'key' => 'sid',
        'url' => U('sechand/detail', array('form' => 'message'), true)
    ), //二手置换回复
    'scenery' => array(
        'title' => '小区时景',
        'key' => 'sid',
        'url' => U('scenery/detail', array('form' => 'message'), true)
    ), //小区时景
    'scenery_post' => array(
        'title' => '小区时景回复',
        'key' => 'sid',
        'url' => U('scenery/detail', array('form' => 'message'), true)
    ), //小区时景回复
);
