<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 分享类型配置文件
 */
return array(
    'default' => array(
        'wx_name' => '结邻公社', 'wx_logo' => 'upload/pics/default_logo.jpg',
        'wx_appid' => '',
        'wx_qrcode' => 'upload/pics/default_qrcode.jpg',
        'regUrl' => 'http://mp.weixin.qq.com/s?__biz=MzA5Njc2NjgyOA==&mid=200510413&idx=1&sn=602a08a77f3d2d494034b24c09eea1ed&scene=0#rd',
        'tip' => array(
            'title' => '用户未关注',
            'content' => '您好，您还没有关注“' . WEB_TITLE . '”，现在关注立即享受小管家快送服务，还有更多惊喜！'
        ),
        'share' => array(
            'title' => '下午茶，净菜，超市，每天有特价。快使唤小管家，享大优惠~',
            'desc' => '时光短暂，不要辜负美好食光哦~',
            'imgUrl' => getImgUrl('statics/default/images/steward/share_2.jpg')
        )
    ),
    'type' => array(
        'news' => array('id' => 1, 'name' => '新闻'),
        'event' => array('id' => 2, 'name' => '活动'),
        'forum' => array('id' => 3, 'name' => '业主论坛'),
        'carpool' => array('id' => 4, 'name' => '拼车'),
        'housekeep' => array('id' => 5, 'name' => '家政'),
        'sechand_trade' => array('id' => 6, 'name' => '二手置换'),
        'scenery' => array('id' => 7, 'name' => '小区时景'),
    ),
    'share' => array(
        'title' => '我抢到心晴大红包，缠绵雨季要有好心晴~',
        'desc' => '缠绵雨季 我好想你~结邻送你心晴红包~雨天遇见小管家，好心晴哟~',
        'imgUrl' => 'http://wx.51jhome.com/statics/default/images/steward/2015/xinqing-share.jpg'
    )
);
