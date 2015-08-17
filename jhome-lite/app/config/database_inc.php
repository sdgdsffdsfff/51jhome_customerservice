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
        'user' => 'root',
        'password' => '1234567890',
        'dbname' => 'hz_ihome_lite',
        'dbprefix' => 'hz_',
        'dbcharset' => 'utf8',
        'pconnect' => false, //使用持续链接
        'save_errlog' => true, //是否保存错误日志
    )
);
