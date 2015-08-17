<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
!defined('TRACE') && define('TRACE', 0);
if (DEBUG || TRACE) {
    $GLOBALS['startTime'] = microtime(true);
}
require XLPHP_PATH . 'config/config_inc.php';
include XLPHP_PATH . 'lib/function/global.fun.php';
if ($System['session']['auto_start']) {
    session_start();
}
//set_error_handler('showErrorFun');
//set_exception_handler('showErrorFun');
//开启gzip页面压缩
$System['gzip'] ? ob_start('obGzip') : ob_start();
include XLPHP_PATH . 'lib/core/base.class.php';
define('SCRIPT_NAME', basename($_SERVER['SCRIPT_NAME']));
$dirName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('URL', getSiteUrl());
define('MAIN_URL', getSiteUrl('main'));
define('BASE_URL', rtrim(MAIN_URL, SCRIPT_NAME));
define('WEB_URL', isHave($System['main_url']) ? $System['main_url'] : ($dirName == '/' ? $dirName : $dirName . '/'));
define('WEB_TITLE', $System['title']);
define('VCODE', $System['vcode']);
define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0 );
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);
$url = parse_url($dirName);
$url = isset($url['path']) ? $url['path'] : '';
define('SITE_PATH', isHave($System['main_path']) ? $System['main_path'] : ($url == '/' ? $url : $url . '/'));
define('WEB_PATH', $url == '/' ? $url : $url . '/');
unset($url, $dirName);
define('TIME', $_SERVER['REQUEST_TIME']);
define('USER_AGENT', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
xlphp::run();
if (TRACE && !isAjax()) {
    load('sys.fun');
    getRunInfo();
}
if ($System['gzip']) {
    ob_end_flush(); //页面gzip压缩
}