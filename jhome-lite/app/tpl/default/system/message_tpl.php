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
$btitle = $System['title'] ? $System['title']: $title;
$str = '';
$urlTitle=$urlTitle?$urlTitle:'返回上页';
//ob_end_clean(); //清除缓存
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="white">
<meta name="format-detection" content="telephone=no">
<?php
if ($isAutoGo) {
?>
<meta http-equiv="refresh" content="3;url=<?php echo $url;?>" />
<?php
}
?>
<title><?php echo $btitle;?></title>
<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL.'statics/default/css/global/style-3.0.css';?>" />
</head>
<body>
	<!-- header -->
	<header class="header">
    	<a href="javascript:history.back(-1);"><i class="icon-chevron-left"></i></a>
    	<span>出错啦</span>
    </header>
    
    <div id="container" class="red-packet">
        <div style="width:12rem;margin:2rem auto;"><img width="100%" src="http://static.51jhome.com/statics/default/images/event/0505red/man.png"></div>
        <div style="margin:1rem; background:#fff; border-radius:1.5rem; font-size:1.2rem; padding:2rem; text-align:center; line-height:1.6rem;">
       		<p><?php echo (is_array($msg) ? '<pre>' . var_export($msg, true) . '</pre>' : $msg);?></p>
        </div>
    </div>
</body>
</html>
<?php
if ($exit) {
    exit;
}
?>
