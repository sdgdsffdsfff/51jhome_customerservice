<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<style>
#face-list{width:350px;height:170px;margin:0 auto;display:none}
#link-list{ display:none}
#face-list ul{margin:0;padding:0}
#face-list li{list-style-type:none;margin:2px;padding:0;float:left;cursor:pointer}
#reply-content-list, #face-list, #show-content{height:210px}
</style>
<div style="width:500px; height:400px">
  <p align="left">回复：<strong><?php echo getUser($uid);?></strong></p>
  <div>
    <form class="form-horizontal" method="post" id="form1">
      <div class="form-group">
        <label class="col-sm-2 control-label">回复内容</label>
        <div class="col-sm-8" id="show-content"> 
          <!---->
          <div id="reply-content-list">
            <p><a href="javascript:;" id="show-face">添加表情</a><a href="javascript:;" id="show-link" style="margin-left:20px">添加链接</a></p>
            <textarea class="form-control" id="reply-content" name="content"  style="height:150px"></textarea>
            <p class="help-block">文本内容支持html代码（A标签）</p>
          </div>
          <!----> 
          <!---->
          <div id="face-list">
            <p><a href="javascript:;" id="close-face">关闭表情</a></p>
            <ul>
              <?php
            if ($face){
                foreach($face as $key=>$val){
                    echo '<li><img src="'.IMG_PATH.'content/face/'.$key.'.png" width="24" height="24" rel="'.$val['code'].'" title="'.$val['name'].'"/></li>';
                }
            }
            ?>
            </ul>
          </div>
          <!---->
          <!----> 
          <div id="link-list">
          <div class="form-group">
                <label class="col-sm-3 control-label">链接名称</label>
                <div class="col-sm-9">
                  <input type="text" name="url-title" id="url-title" placeholder="" class="form-control" value="" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">链接地址</label>
                <div class="col-sm-9">
                  <input type="text" name="url-link" id="url-link" placeholder="" class="form-control" value="" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <div class="col-sm-9 col-lg-offset-1">
                  <div class="col-sm-5">
                    <button type="button" id="add-link-ok" class="btn btn-primary">插入</button>
                  </div>
                  <div class="col-md-offset-4">
                    <button type="button" id="add-link-no" class="btn btn-white">取消</button>
                  </div>
                </div>
              </div>
         </div>
          <!---->
        </div>
      </div>
      <p></p>
      <div class="alert alert-info" style="font-size:13px"> <strong>提示</strong> 用户必须是在48小时内与平台有过互动（包括回复消息、点击菜单、关注、购物等）才能接受到消息 </div>
      <p class="btn-group btn-group-justified"><a class="btn btn-success" href="javascript:;" id="send">发送消息</a></p>
      <p></p>
    </form>
  </div>
</div>
<script>
$(function(){
	var uid=<?php echo $uid;?>;
	$("#close-face").click(function(){
		$("#face-list").hide();
		$("#link-list").hide();
		$("#reply-content-list").show()
	})
	$("#show-face").click(function(){
		$("#reply-content-list").hide();
		$("#link-list").hide();
		$("#face-list").show();
	})
	$("#show-link").click(function(){
		$("#reply-content-list").hide();
		$("#link-list").show();
		$("#face-list").hide();
	})
	$("#add-link-no").click(function(){
		$("#reply-content-list").show();
		$("#link-list").hide();
		$("#face-list").hide();
		$("#reply-content");
	})
	$("#add-link-ok").click(function(){
		var urlTitle=$.trim($("#url-title").val());
		var urlLink=$.trim($("#url-link").val());
		if(!urlTitle||!urlLink){
			Msg.error('请输入链接名称和网址');
			return false;
		}
		var content=$("#reply-content").val();
		$("#reply-content").val(content+'<a href="'+urlLink+'">'+urlTitle+'</a>');
		$("#reply-content-list").show();
		$("#link-list").hide();
		$("#face-list").hide();
	})
	$("#face-list").find('img').unbind('click').click(function(){
		$("#close-face").click();
		var content=$("#reply-content").val();
		$("#reply-content").val(content+' '+$(this).attr('rel')+' ');
	})
	$('#send').click(function(){
		var content=$("#reply-content").val();
		if (!content){
			Msg.error('发送内容不能为空');
			return false;
		}
		Msg.loading('正在发送...');
		$.post('<?php echo U('member/send');?>',{"uid":uid,"content":content},function(result){
			Msg.hide();
			if (result.status==1){
				Msg.ok('发送成功',function(){jQuery(document).trigger('close.facebox');},1000);	
			}else{
				Msg.error(result.info+':['+result.data.code+']'+result.data.msg);	
			}
		},'json');
	  return false;
	})	
})
</script>