<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 系统默认信息模板
 *
 */
if ($msg == '404') {
    header("HTTP/1.1 404 Not Found");
    $msg = '404 请求页面不存在！';
    $returnStatus = 0;
}
switch ($returnStatus) {
    case 1:$returnStatus = 'success';
        break;
    case 2:$returnStatus = 'info';
        break;
    case 0:default:$returnStatus = 'error';
}
global $System;
$btitle = ($System['title']) ? $System['title'] . '-' . $title : $title;
$str = '';
$urlTitle=$urlTitle?$urlTitle:'返回上页';
//ob_end_clean(); //清除缓存
$str.='<!DOCTYPE HTML><html><head>';
if ($isAutoGo) {
    $str.="<meta http-equiv=\"refresh\" content=\"3;url=$url\" />";
}
$str.='<meta charset="utf-8">
<meta name="viewport" content="width=device-width,height=device-height,inital-scale=1.0,maximum-scale=1.0,user-scalable=no;">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no"><meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /><title>' . $btitle . '</title><style type="text/css">body{background-color:#F7F7F7;font-family:Arial;font-size:0.9em;line-height:150%;}.main{min-width:290px;margin:50px auto;padding:6px;list-style:none;}.info{border:#DFDFDF 1px solid;color:#666;background-color:#FFF;}.success{border:1px solid;color:#4F8A10;background-color:#DFF2BF;}.error{border:1px solid;color:#D8000C;background-color:#FFBABA;} p{line-height:18px;margin:5px 0px;}</style></head><body><div class="main ' . $returnStatus . '"><p><strong>' . $title . '：</strong></p><p>' . (is_array($msg) ? '<pre>' . var_export($msg, true) . '</pre>' : $msg) . '</p>';
if ($url) {
    $str.='<p><a href="' . $url . '">&laquo;' . $urlTitle . '</a></p>';
}
$str.='</div></body></html>';
echo $str;
if ($exit) {
    exit;
}
?>
