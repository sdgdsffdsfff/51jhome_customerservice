<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 小管家订单分享红包配置
 *
 */
return array(
    'couponTotal' => 20, //每个订单发放的总数
    'dateLimit' => 2, //单个手机每天的领取上限
    'startTime' => '2015-05-01 00:00:00', //可以领红包的起点时间
    'orderStatus' => array(1, 3, 4, 5, 6, 7, 11), //允许发放红包的订单状态 7:已送达 11:已评论
    'couponList' => array(
        array('pk' => 0, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 1, 'start' => 0, 'date' => 15, 'rank' => 0.15, 'count' => 20), //默认券
        array('pk' => 1, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 10, 'start' => 59, 'date' => 15, 'rank' => 0.01, 'count' => 1),
        array('pk' => 2, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 9, 'start' => 59, 'date' => 15, 'rank' => 0.02, 'count' => 1),
        array('pk' => 3, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 8, 'start' => 59, 'date' => 15, 'rank' => 0.03, 'count' => 1),
        array('pk' => 4, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 7, 'start' => 29, 'date' => 15, 'rank' => 0.03, 'count' => 1),
        array('pk' => 5, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 6, 'start' => 29, 'date' => 15, 'rank' => 0.04, 'count' => 2),
        array('pk' => 6, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 5, 'start' => 0, 'date' => 15, 'rank' => 0.05, 'count' => 2),
        array('pk' => 7, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 4, 'start' => 0, 'date' => 15, 'rank' => 0.1, 'count' => 3),
        array('pk' => 8, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 3, 'start' => 0, 'date' => 15, 'rank' => 0.1, 'count' => 3),
        array('pk' => 9, 'name' => '全品类券', 'client' => 0, 'cate' => '', 'shop' => '', 'money' => 2, 'start' => 0, 'date' => 15, 'rank' => 0.15, 'count' => 4),
        array('pk' => 10, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 10, 'start' => 49, 'date' => 15, 'rank' => 0.06, 'count' => 1),
        array('pk' => 11, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 9, 'start' => 49, 'date' => 15, 'rank' => 0.05, 'count' => 1),
        array('pk' => 12, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 8, 'start' => 49, 'date' => 15, 'rank' => 0.06, 'count' => 2),
        array('pk' => 13, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 7, 'start' => 39, 'date' => 15, 'rank' => 0.06, 'count' => 2),
        array('pk' => 14, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 6, 'start' => 39, 'date' => 15, 'rank' => 0.06, 'count' => 3),
        array('pk' => 15, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 5, 'start' => 0, 'date' => 15, 'rank' => 0.1, 'count' => 4),
        array('pk' => 16, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 4, 'start' => 0, 'date' => 15, 'rank' => 0.2, 'count' => 5),
        array('pk' => 17, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 3, 'start' => 0, 'date' => 15, 'rank' => 0.2, 'count' => 6),
        array('pk' => 18, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 2, 'start' => 0, 'date' => 15, 'rank' => 0.15, 'count' => 4),
        array('pk' => 19, 'name' => '净菜券', 'client' => 0, 'cate' => '275', 'shop' => '', 'money' => 1, 'start' => 0, 'date' => 15, 'rank' => 0.15, 'count' => 4),
        array('pk' => 20, 'name' => '星巴克券', 'client' => 0, 'cate' => '', 'shop' => '22,23,45,95,96,139', 'money' => 5, 'date' => 15, 'start' => 25, 'rank' => 0.2, 'count' => 1),
        array('pk' => 21, 'name' => '星巴克券', 'client' => 0, 'cate' => '', 'shop' => '22,23,45,95,96,139', 'money' => 6, 'date' => 15, 'start' => 25, 'rank' => 0.2, 'count' => 1),
        array('pk' => 22, 'name' => '星巴克券', 'client' => 0, 'cate' => '', 'shop' => '22,23,45,95,96,139', 'money' => 7, 'date' => 15, 'start' => 38, 'rank' => 0.2, 'count' => 1),
        array('pk' => 23, 'name' => '星巴克券', 'client' => 0, 'cate' => '', 'shop' => '22,23,45,95,96,139', 'money' => 8, 'date' => 15, 'start' => 38, 'rank' => 0.2, 'count' => 1),
        array('pk' => 24, 'name' => '星巴克券', 'client' => 0, 'cate' => '', 'shop' => '22,23,45,95,96,139', 'money' => 10, 'date' => 15, 'start' => 58, 'rank' => 0.2, 'count' => 1),
        array('pk' => 25, 'name' => '星巴克券', 'client' => 0, 'cate' => '', 'shop' => '22,23,45,95,96,139', 'money' => 15, 'date' => 15, 'start' => 68, 'rank' => 0.2, 'count' => 1),
        array('pk' => 26, 'name' => '鲜丰水果券', 'client' => 0, 'cate' => '', 'shop' => '31,32,33,85,86,88', 'money' => 5, 'date' => 15, 'start' => 50, 'rank' => 0.2, 'count' => 1),
        array('pk' => 27, 'name' => '浮力森林券', 'client' => 0, 'cate' => '', 'shop' => '40,41,42,97,98,99', 'money' => 5, 'date' => 15, 'start' => 30, 'rank' => 0.2, 'count' => 1),
    ),
);
