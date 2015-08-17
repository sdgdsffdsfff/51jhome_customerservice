<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 小管家参数设置
 *
 */
return array(
    'order_source' => array('微信', 'ios', 'android'), //下单客户端类型
    'use_client' => array('不限', '微信', 'App'), //抵价券客户端
    'payLimitTime' => 60 * 10, //订单支付过期时间
    'paymentLimitTime' => 60 * 10, //申请代付过期时间
    'shipTime' => array(
        'limit' => 3, //可购买天数
        'shours' => 9, //开始服务时间
        'ehours' => 21, //结束服务时间
        'minute' => 30//起止分钟，00 或者 30
    ), //配送时间
    'expTime' => array(
        0 => array(//默认预定时间
            'name' => '默认当天【9:30-20:30】', //标题
            'limit' => 3, //可购买天数
            'shours' => 9, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => 30, //起止分钟，00 或者 30
            'startDate' => 0, //起止天数，0:今天 1:明天 2:后天
        ),
        6 => array(//默认预定时间
            'name' => '预定当天【15:00-21:00】', //标题
            'limit' => 3, //可购买天数
            'shours' => 15, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => '00', //起止分钟，00 或者 30
            'startDate' => 0, //起止天数，0:今天 1:明天 2:后天
        ),
        20 => array(//预定下午茶配送时间
            'name' => '预定第二天【9:30-20:30】', //标题
            'limit' => 2, //可购买天数
            'shours' => 9, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => 30, //起止分钟，00 或者 30
            'startDate' => 1, //起止天数，0:今天 1:明天 2:后天
        ),
        21 => array(//预定下午茶配送时间
            'name' => '预定第二天【10:00-21:00】', //标题
            'limit' => 2, //可购买天数
            'shours' => 10, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => '00', //起止分钟，00 或者 30
            'startDate' => 1, //起止天数，0:今天 1:明天 2:后天
        ),
        22 => array(//预定下午茶配送时间
            'name' => '预定第二天【11:00-21:00】', //标题
            'limit' => 2, //可购买天数
            'shours' => 11, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => '00', //起止分钟，00 或者 30
            'startDate' => 1, //起止天数，0:今天 1:明天 2:后天
        ),
        23 => array(//预定下午茶配送时间
            'name' => '预定第二天【12:00-21:00】', //标题
            'limit' => 2, //可购买天数
            'shours' => 12, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => '00', //起止分钟，00 或者 30
            'startDate' => 1, //起止天数，0:今天 1:明天 2:后天
        ),
        24 => array(//预定下午茶配送时间
            'name' => '预定第二天【13:00-21:00】', //标题
            'limit' => 2, //可购买天数
            'shours' => 13, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => '00', //起止分钟，00 或者 30
            'startDate' => 1, //起止天数，0:今天 1:明天 2:后天
        ),
        25 => array(//预定下午茶配送时间
            'name' => '预定第二天【14:00-21:00】', //标题
            'limit' => 2, //可购买天数
            'shours' => 14, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => '00', //起止分钟，00 或者 30
            'startDate' => 1, //起止天数，0:今天 1:明天 2:后天
        ),
        26 => array(//净菜配送时间
            'name' => '预定第二天【15:00-21:00】', //标题
            'limit' => 2, //可购买天数
            'shours' => 15, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => '00', //起止分钟，00 或者 30
            'startDate' => 1, //起止天数，0:今天 1:明天 2:后天
        ),
        27 => array(//预定下午茶配送时间
            'name' => '预定第二天【16:00-21:00】', //标题
            'limit' => 2, //可购买天数
            'shours' => 16, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => '00', //起止分钟，00 或者 30
            'startDate' => 1, //起止天数，0:今天 1:明天 2:后天
        ),
        28 => array(//预定下午茶配送时间
            'name' => '预定第二天【17:00-21:00】', //标题
            'limit' => 2, //可购买天数
            'shours' => 17, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => '00', //起止分钟，00 或者 30
            'startDate' => 1, //起止天数，0:今天 1:明天 2:后天
        ),
        41 => array(//预定第三天配送时间
            'name' => '预定第三天【9:30-20:30】', //标题
            'limit' => 2, //可购买天数
            'shours' => 9, //开始服务时间
            'ehours' => 21, //结束服务时间
            'minute' => 30, //起止分钟，00 或者 30
            'startDate' => 2, //起止天数，0:今天 1:明天 2:后天
        ),
    ),
    'status' => array('否', '是'), //通用状态信息
    'user_status' => array('<span class="red">不可用</span>', '正常'), //员工状态
    'work_status' => array('<span class="green">空闲</span>', '<span class="yellow">忙碌</span>', '<span class="gray">离线</span>'), //员工工作状态
    'shop_status' => array('关闭', '正常'), //店铺状态
    'shop_type' => array('合作', '自营'), //店铺类型
    'shop_tips' => array(1 => '<span class="tag-sx-c">促</span>', 2 => '<span class="tag-sx-f">返</span>', 3 => '<span class="tag-sx-j">减</span>', 4 => '<span class="tag-sx-te">新</span>'), //店铺标签
    'shop_power' => array('未评级', '一星', '二星', '三星', '四星', '五星', '六星', '七星', '八星', '九星', '十星'),
    'goods_tips' => array(1 => '促销', 2 => '优惠'), //商品标签
    'goods_status' => array('<span class="gray">下架</span>', '<span class="green">正常</span>'), //商品状态
    'score_server' => array('很好', '一般', '很不好'), //服务评分
    'score_post' => array('很好', '正常', '不准时'), //配送评分
    'score_goods' => array('上乘', '可以', '不新鲜'), //商品评分
    'order_type' => array('当日订单', '预约订单', '实时订单'),
    'pay_type' => array('微支付', '支付宝', '结邻币', '货到付款'),
    'order_status' => array('未支付', '已支付', '订单已取消', '已审核', '正在配货', '配货完成', '配送中', '已送达', '送货失败', '申请退款', '退款成功', '已评论', '订单过期', '异常订单'),
    'actual_type' => array('结邻币', '微支付', '支付宝', '银行卡'), //退款途径
    'ticket_status' => array('<span class="gray">未支付</span>', '<span class="yellow">未使用</span>', '<span class="green">已使用</span>', '<span class="red">已过期</span>'), //商家电子券状态
    'comment_complain' => array(1 => '商品买错', 2 => '商品漏买', 3 => '质量不满', 4 => '配送超时'), //评论投诉项
    'coupon_type' => array('普通发放', '指定生成', '新人券', '限时券', '折扣券', '满减券', '免邮券', '品类券', '商家券'),
);
