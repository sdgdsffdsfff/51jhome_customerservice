<?php
if (!defined('IN_XLP')||!isset($Document) || !is_array($Document)) {
    exit('Access Denied!');
}
if (isset($Document['head'])) {
	if ($Document['pagename']){
		$Document['pagename'].='_';	
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $Document['pagename'].WEB_TITLE; ?></title> 
<!--[if lt IE 9]> <?php echo getJs(array('content/ie/respond.min','content/ie/html5'));?><![endif]-->
<?php
  	getCss(array('content/global','content/app.v2'));
    if ($Document['mycss']) {
        getCss($Document['mycss']);
    }
?>
<?php
	getJs('content/app.v2');
    if ($Document['myjs']) {
        getJs($Document['myjs']);
    }
}
?>
<script>
var MAIN_PATH='<?php echo WEB_URL; ?>';
var PUBLIC_URL='<?php echo PUBLIC_PATH; ?>';
</script>
 </head>
 <body <?php if($menuSetting['navbar']){echo 'class="navbar-fixed"';}?>>