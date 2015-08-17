<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<style>
.pictureList ul {
	margin:0;
	padding:0;
}
.pictureList li {
	list-style-type:none;
	float:left;
	margin:3px
}
.show-pop-content {
	overflow-y:scroll;
	overflow-x:hidden;
	table-layout: fixed;
	word-wrap:break-word;
	word-break:break-all;
}
</style>
<form class="form1" id="form1">
<div class="table-responsive show-pop-content" style="width:400px; height:130px;">
<section class="panel portlet-item"> 
<header class="panel-heading"><strong>操作回执：</strong></header> 
<ul class="list-group">
<li class="list-group-item" id="result-info">
<?php
if ($rs['result']){
?>
<span class="green">发送成功√</span>
<?php }else{?>
<span class="red">失败×，错误信息：<?php echo $rs['msg'];?></span>
<?php }?>
</li>
</ul> 
</section>
</div>
</form>