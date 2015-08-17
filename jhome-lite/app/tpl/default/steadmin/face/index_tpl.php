<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<style>
#face-list{width:350px;height:170px;margin:0 auto}
#face-list ul{margin:0;padding:0}
#face-list li{list-style-type:none;margin:2px;padding:0;float:left;cursor:pointer}
</style>
<div id="face-list">
  <ul>
    <?php
if ($rs){
	foreach($rs as $key=>$val){
		echo '<li><img src="'.IMG_PATH.'content/face/'.$key.'.png" width="24" height="24" rel="'.$val['code'].'" title="'.$val['name'].'"/></li>';
	}
}
?>
  </ul>
</div>
<script>
$(function() {
	$.addFace=function(id,code){
		var content=$("#"+id).val();
		$("#"+id).val(content+' '+code+' ');
	}
	$("#face-list").find('img').click(function(){
		$.addFace('<?php echo $id;?>',$(this).attr('rel'));
	})
})
</script> 