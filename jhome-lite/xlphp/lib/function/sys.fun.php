<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 系统调试用函数
 */

/** 输出调试信息
 *  @param $str string or array 输出内容
 *  @return string
 */
function outPut($str) {
    if (isAjax()) {
        return false;
    }
    if (is_array($str)) {
        $str = var_export($str, true);
    }
    echo '<div style="border:1px #996600 solid; padding:5px; margin:10px; background:#F1EBEE; font-size:12px"><pre>' . $str . '</pre></div>';
}

/** 输出程序运行消耗资源信息
 *  @param 无
 *  @return string
 */
function getRunInfo() {
    echo outPut('消耗内存:' . changeFileSize(memory_get_usage()) . '<br/>查询数据:' .
            (class_exists('Mydb') ? Mydb::$querynum : 0) . '次<br/>执行时间:' .
            round(((microtime(true) - $GLOBALS['startTime'])), 4) . 's<br/>');
}

/** 发送Http状态信息
 *  @param $code int http状态码
 *  @return
 */
function sendHttpStatus($code) {
    static $_status = array(
// Success 2xx
        200 => 'OK',
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',
// Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable'
    );
    if (isset($_status[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:' . $code . ' ' . $_status[$code]);
    }
}
