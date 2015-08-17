<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 管理员参数设置
 *
 */
return array(
    'group' => array(1 => '系统管理员', 2 => '街道管理员', 3 => '社区管理员', 4 => '小区管理员', 5 => '网站编辑', 6 => '在线客服', 7 => '财务'),
    'counts' => array(
        1 => array(//系统管理员
            'notice_passive' => array(//被动通知
                'key' => 1, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_active' => array(//主动通知（群发）
                'key' => 2, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -2, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_radio' => array(//系统广播（群发）
                'key' => 3, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
        ),
        2 => array(//街道管理员
            'notice_passive' => array(//被动通知
                'key' => 1, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => 1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_active' => array(//主动通知（群发）
                'key' => 2, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => 1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_radio' => array(//系统广播（群发）
                'key' => 3, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => 4, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
        ),
        3 => array(//社区管理员
            'notice_passive' => array(//被动通知
                'key' => 1, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_active' => array(//主动通知（群发）
                'key' => 2, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => 2, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_radio' => array(//系统广播（群发）
                'key' => 3, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => 4, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
        ),
        4 => array(//小区管理员
            'notice_passive' => array(//被动通知
                'key' => 1, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_active' => array(//主动通知（群发）
                'key' => 2, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => 1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_radio' => array(//系统广播（群发）
                'key' => 3, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => 4, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
        ),
        5 => array(//网站编辑
            'notice_passive' => array(//被动通知
                'key' => 1, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_active' => array(//主动通知（群发）
                'key' => 2, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -2, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_radio' => array(//系统广播（群发）
                'key' => 3, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -1, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
        ),
        6 => array(//网站客服
            'notice_passive' => array(//被动通知
                'key' => 1, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => 0, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_active' => array(//主动通知（群发）
                'key' => 2, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -2, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_radio' => array(//系统广播（群发）
                'key' => 3, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -2, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
        ),
        7 => array(//财务
            'notice_passive' => array(//被动通知
                'key' => 1, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => 0, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_active' => array(//主动通知（群发）
                'key' => 2, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -2, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
            'notice_radio' => array(//系统广播（群发）
                'key' => 3, //标记
                'type' => 'month', //统计周期 year|month|day
                'counts' => -2, //允许次数 -2:不允许|-1:不限制|>0:具体次数
            ),
        ),
    )
);
