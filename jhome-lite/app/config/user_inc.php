<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 用户配置参数设置
 *
 */
return array(
    'superUser' => array(//超级用户，不受编辑次数限制
        230, 244, 246, 249, 1628, 517, 218, 211, 226, 228, 229, 224, 279,
        799, 213, 6176, 234, 789, 3394, 299, 1092, 3034, 7143, 769, 527, 870,),
    'badName' => array('管理员', '腾讯', '大浙', '官方', '客服', '公社', '结邻', '小管家'), //不可用用户名关键字
    'bindHome' => array(//绑定小区数量
        'community' => 5, //小区
        'office' => 5//写字楼
    ),
    'edits' => array(
        'user' => array(//数据表名称
            'village_id' => array(//字段名
                'title' => '小区', //名称
                'type' => 'day', //统计周期 year|month|week|day
                'desc' => '每天', //统计描述
                'counts' => 1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            )
        ),
    ),
    'credit' => array(//用户积分设置
        'event' => array(//积分来源
            'system' => 0, //系统操作
            'signin' => 1, //签到
            'prize' => 2, //抽奖活动
            'shopping' => 3, //购物
            'admin' => 4, //管理员
            'guess' => 5, //疯狂猜图
        ),
        'shopping' => array(
//已知的event类型有：0:系统 1:小管家购物扣除 2:活动抽奖赠送 3:小管家购物返还 4:后台管理员操作,5:商家电子券扣除,6:商家电子券返还
        )
    )
);
