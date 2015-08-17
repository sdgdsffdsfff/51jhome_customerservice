<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 后台菜单配置文件-管理员
 */
return array(
    array('title' => '后台首页', 'url' => '', 'icon' => 'fa-home', 'short' => 'home', 'item' =>
        array(
            array(
                'title' => '后台首页',
                'url' => U('home/index'),
                'short' => 'home-index'
            ),
            array(
                'title' => '客服日报',
                'url' => U('servicelog/index'),
                'short' => 'servicelog-index'
            ),
            array(
                'title' => '短信发送日志',
                'url' => U('log/sms'),
                'short' => 'log-sms'
            ),
            array(
                'title' => '异步操作日志',
                'url' => U('log/asyn'),
                'short' => 'log-asyn'
            ),
            array(
                'title' => '后台操作日志',
                'url' => U('log/sys'),
                'short' => 'log-sys'
            ),
        )
    ),
    array('title' => '系统维护', 'url' => '', 'icon' => 'fa-wrench', 'short' => 'system', 'item' =>
        array(
            array(
                'title' => '自定义菜单',
                'url' => U('menu/index'),
                'short' => 'menu-index'
            ),
            array(
                'title' => '企业号设置',
                'url' => U('home/qyedit'),
                'short' => 'home-qyedit'
            ),
            array(
                'title' => '小管家设置',
                'url' => U('home/edit'),
                'short' => 'home-edit'
            ),
            array(
                'title' => '编辑资料',
                'url' => U('my/edit'),
                'short' => 'my-edit'
            ),
            array(
                'title' => '服务中心管理',
                'url' => U('service/index'),
                'short' => 'service-index'
            ),
            array(
                'title' => '自动回复设置',
                'url' => U('reply/index'),
                'short' => 'reply-index'
            ),
            array(
                'title' => '分享文案设置',
                'url' => U('home/share'),
                'short' => 'home-share'
            ),
        )
    ),
    array('title' => '帐号管理', 'url' => '', 'icon' => 'fa-user', 'short' => 'user', 'item' =>
        array(
            array(
                'title' => '帐号列表',
                'url' => U('user/index'),
                'short' => 'user-index'
            ),
        )
    ),
//    array('title' => '商家电子券', 'url' => '', 'icon' => 'fa-ticket', 'short' => 'ticket', 'item' =>
//        array(
//            array(
//                'title' => '电子券列表',
//                'url' => U('ticket/index'),
//                'short' => 'ticket-index'
//            ),
//            array(
//                'title' => '购买列表',
//                'url' => U('ticket/user'),
//                'short' => 'ticket-user'
//            ),
//        )
//    ),
    array('title' => '订单管理', 'url' => '', 'icon' => 'fa-truck', 'short' => 'order', 'item' =>
        array(
            array(
                'title' => '待处理订单',
                'url' => U('order/index', array('deal' => 'service')),
                'short' => 'order-index'
            ),
            array(
                'title' => '全部订单',
                'url' => U('order/index'),
                'short' => 'order-index'
            )
        )
    ),
    array('title' => '预定查询', 'url' => '', 'icon' => 'fa-cutlery', 'short' => 'shop', 'item' =>
        array(
            array(
                'title' => '半成品菜',
                'url' => U('shop/report', array('cid' => 275)),
                'short' => 'shop-report'
            ),
            array(
                'title' => '下午茶',
                'url' => U('shop/report', array('cid' => 273)),
                'short' => 'shop-report'
            ),
        )
    ),
    array('title' => '商品管理', 'url' => '', 'icon' => 'fa-shopping-cart', 'short' => 'goods', 'item' =>
        array(
            array(
                'title' => '新增商品',
                'url' => U('goods/add'),
                'short' => 'goods-add'
            ),
            array(
                'title' => '商品管理',
                'url' => U('goods/index'),
                'short' => 'goods-index'
            ),
            array(
                'title' => '低库存商品',
                'url' => U('goods/index', array('deal' => 'storage')),
                'short' => 'goods-index'
            ),
            array(
                'title' => '店铺管理',
                'url' => U('shop/index'),
                'short' => 'shop-index'
            ),
            array(
                'title' => '分类管理',
                'url' => U('goodscate/index'),
                'short' => 'goodscate-index'
            ),
//            array(
//                'title' => '导出商品',
//                'url' => U('goods/out'),
//                'short' => 'goods-out'
//            ),
            array(
                'title' => '商品套餐上架',
                'url' => U('integration/index'),
                'short' => 'integration-index'
            ),
        )
    ),
    array('title' => '抵价券管理', 'url' => '', 'icon' => 'fa-ticket', 'short' => 'coupon', 'item' =>
        array(
            array(
                'title' => '抵价券列表',
                'url' => U('coupon/index'),
                'short' => 'coupon-index'
            ),
            array(
                'title' => '抵价券报表',
                'url' => U('coupon/report'),
                'short' => 'coupon-report'
            ),
            array(
                'title' => '抵价券查询',
                'url' => U('coupon/search'),
                'short' => 'coupon-search'
            ),
        )
    ),
    array('title' => '机器管理', 'url' => '', 'icon' => 'fa-print', 'short' => 'print', 'item' =>
        array(
            array(
                'title' => '打印机管理',
                'url' => U('print/index'),
                'short' => 'print-index'
            ),
        )
    ),
    array('title' => '区域管理', 'url' => '', 'icon' => 'fa-windows', 'short' => 'village', 'item' =>
        array(
            array(
                'title' => '小区列表',
                'url' => U('village/index'),
                'short' => 'village-index'
            )
        )
    ),
    array('title' => '财务相关', 'url' => '', 'icon' => 'fa-yen', 'short' => 'finance', 'item' =>
        array(
            array(
                'title' => '入账报表',
                'url' => U('finance/income'),
                'short' => 'finance-income'
            ),
            array(
                'title' => '出账报表',
                'url' => U('finance/outgo'),
                'short' => 'finance-outgo'
            ),
            array(
                'title' => '票据管理',
                'url' => U('bills/index'),
                'short' => 'bills-index'
            ),
        )
    ),
    array('title' => '数据报表', 'url' => '', 'icon' => 'fa-bar-chart-o', 'short' => 'order', 'item' =>
        array(
            array(
                'title' => '实时统计',
                'url' => U('report/today'),
                'short' => 'report-today'
            ),
            array(
                'title' => '新增订单',
                'url' => U('report/index'),
                'short' => 'report-index'
            ),
            array(
                'title' => '数据报表',
                'url' => U('report/chart'),
                'short' => 'report-chart'
            ),
            array(
                'title' => '订单数据',
                'url' => U('report/order'),
                'short' => 'report-order'
            ),
            array(
                'title' => '商品数据',
                'url' => U('report/goods'),
                'short' => 'report-goods'
            ),
            array(
                'title' => '客户端数据',
                'url' => U('report/client'),
                'short' => 'report-client'
            ),
            array(
                'title' => '用户数据',
                'url' => U('report/user'),
                'short' => 'report-user'
            ),
            array(
                'title' => '小管家数据报表',
                'url' => U('report/worker'),
                'short' => 'report-worker'
            ),
            array(
                'title' => '小管家绩效考核',
                'url' => U('report/workerassess'),
                'short' => 'report-workerassess'
            ),
            array(
                'title' => '小管家票据数据',
                'url' => U('workerdata/index'),
                'short' => 'workerdata-index'
            )
        )
    ),
    array('title' => '考核管理', 'url' => '', 'icon' => 'fa-thumbs-o-up', 'short' => 'comment', 'item' =>
        array(
            array(
                'title' => '用户评价',
                'url' => U('comment/index'),
                'short' => 'comment-index'
            )
        )
    ),
    array('title' => '赠单管理', 'url' => '', 'icon' => 'fa-thumbs-o-up', 'short' => 'comment', 'item' =>
        array(
            array(
                'title' => '赠单设置',
                'url' => U('give/setting'),
                'short' => 'give-setting'
            ),
            array(
                'title' => '商品管理',
                'url' => U('give/good'),
                'short' => 'give-good'
            ),
            array(
                'title' => '赠单管理',
                'url' => U('give/giving'),
                'short' => 'give-giving'
            ),
            array(
                'title' => '订单管理',
                'url' => U('give/order'),
                'short' => 'give-order'
            ),
            array(
                'title' => '要求管理',
                'url' => U('give/demand'),
                'short' => 'give-demand'
            ),
        )
    ),
);
