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
<div class="table-responsive show-pop-content" style="width:650px; height:400px;">
<section class="panel portlet-item"> 
<header class="panel-heading">详细信息</header> 
<ul class="list-group"> 
<li class="list-group-item">
<pre>
<?php print_r($rs);?>
</pre>
</li>
</ul> 
</section> 
</div>
<script>
$(function(){
	$("#facebox").click();	
})
</script>