<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 系统默认信息模板
 *
 */
echo '<style type="text/css">body{background-color:#F7F7F7;font-family:Arial;font-size:0.9em;line-height:150%;}.main{min-width:290px;margin:10px;padding:6px;list-style:none;}.info{border:#DFDFDF 1px solid;color:#666;background-color:#FFF;}.success{border:1px solid;color:#4F8A10;background-color:#DFF2BF;}.error{border:1px solid;color:#D8000C;background-color:#FFBABA;} p{line-height:18px;margin:5px 0px;}</style></head><body><div class="main error"><p>' . (is_array($msg) ? '<pre>' . var_export($msg, true) . '</pre>' : $msg) . '</p></div>';
?>
