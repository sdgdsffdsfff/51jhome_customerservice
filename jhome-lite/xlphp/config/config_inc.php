<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * Config 系统配置文件
 *
 */
$System = array(
    'title' => '', //网站标题
    'main_url' => '', //主路径
    'main_path' => '',
    'cookie' => //设置cookie
    array(
        'pre' => '', //COOKIE前缀
        'path' => '/', //COOKIE作用路径
        'domain' => '', //COOKIE作用域
    ),
    'time_zone' => 'PRC', //设置区时
    'vcode' => 'ytuyiqwrwriocv*hhi@kl', //密钥
    'encrypt_delimiter' => '|:)', //加密解密字符串分隔符
    'skin' => 'default', //当前主题
    'c' => 'index', //默认控制器名称
    'm' => 'index', //默认模型
    'g' => '', //当前分组
    'default_group' => 'index', //默认分组
    'group_list' => array(), //分组列表
    'path_mod' => 1, //路由模式，1 path_info，2 普通
    'delimiter' => '/', //分隔符号，建议为 "/"or "-" or "_"
    'postfix' => '.html', //URL后缀
    'hide_index' => false, //是否隐藏 index.php，需要配置服务器
    'hide_urlkey' => false, //是否隐藏URL前缀，可以使URL更简洁，但需要服务器支持
    'url_rule' => array(), //自定义路由规则 array('index/index'=>'index','news/index'=>'news')
    'filter' => 'htmlspecialchars', //POST,GET 默认过滤函数
    'gzip' => false, //开启GZIP压缩模式
    'session' => array(//SESSION设置
        'auto_start' => true, // 是否自动开启Session
        'options' => array(), // session 配置数组 支持type name id path expire domian 等参数
        'pre' => '', // session 前缀
    //'var_session_id'      => 'session_id',     //sessionID的提交变量
    ),
    'var_ajax_submit' => 'is_ajax', //全局判断ajax提交
    'autoload_action' => array()//自动加载的控制器
);
$config_path = APP_PATH . 'config/config_inc.php';
if (is_file($config_path)) {
    $newSystem = include $config_path;
    $System = array_merge($System, $newSystem);
    unset($newSystem);
}
$config_path = APP_PATH . 'function/common.php';
if (is_file($config_path)) {
    include $config_path;
}
unset($config_path);
date_default_timezone_set($System['time_zone']); //默认时间区域
