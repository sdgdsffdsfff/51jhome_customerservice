<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 自定义系统配置文件，将覆盖默认的配置信息
 *
 */
return array(
    'title' => '结邻公社', //网站标题
    'logo' => 'upload/pics/default_logo.jpg', //LOGO
    'is_debug' => false, //是否调试模式
    'is_savePic' => true, //保存图片
    'is_saveVoice' => false, //保存语音
    'checkLoginField' => 'checkLogin', //检查是否登录的标记字段
    'main_url' => '', //主路径
    'main_path' => 'http://static.51jhome.com/', //CDN资源
    'regexp_phone' => '/^1[34578][0-9]{9}$/',
    'cookie' => //设置cookie
    array(
        'pre' => '', //COOKIE前缀
        'path' => '/', //COOKIE作用路径
        'domain' => '', //COOKIE作用域
    ),
    'time_zone' => 'PRC', //设置区时
    'vcode' => 'ytuyiqwrwriocv%^*hhi@kl', //密钥
    'skin' => 'default', //当前主题
    'c' => 'index', //默认控制器名称
    'm' => 'index', //默认模型
    'g' => 'index', //当前分组
    'default_group' => 'index', //默认分组
    'group_list' => array('content', 'weixin', 'test', 'game', 'open', 'api', 'wxpay', 'steward', 'steadmin', 'unite', 'event', 'give'), //分组列表
    'path_mod' => 1, //路由模式，1 path_info，2 普通
    'delimiter' => '/', //分隔符号，建议为 "/"or "-" or "_"
    'postfix' => '.html', //URL后缀
    'hide_index' => true, //是否隐藏 index.php，需要配置服务器
    'filter' => 'htmlspecialchars', //POST,GET 默认过滤函数
    'gzip' => false, //开启GZIP压缩模式
    'autoload_action' => array(
        'index' => 'common',
        'content' => 'common',
        'weixin' => 'common',
        'api' => 'common',
        'test' => 'common',
        'game' => 'common',
        'open' => 'common',
        'wxpay' => 'common',
        'steward' => 'common',
        'steadmin' => 'common',
        'event' => 'common',
        'unite' => 'common',
        'give' => 'common',
    ), //自动加载的控制器
    'autoload_model' => array(), //自动加载的模型
);
