<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 数据库配置文件
 */
return array(
    'dbtype' => 'mysql', //使用哪种数据库  mysql or sqlite
    'dbdrive' => 'pdo', //数据库驱动 pdo、mysql
    'debug' => true, //是否是调试模式，开启的话会在有错误时直接将错误抛出，上线前请务必关掉！
    'mysql' => array(
        'host' => '127.0.0.1',
        'port' => '3306',
        'user' => 'jhome_wx_event',
        'password' => '0Jkdf9K2DqL18Ff9XvWP',
        'dbname' => 'jhome_wx_event',
        'dbprefix' => 'hz_',
        'dbcharset' => 'utf8',
        'pconnect' => false, //使用持续链接
        'save_errlog' => true, //是否保存错误日志
    )
);
