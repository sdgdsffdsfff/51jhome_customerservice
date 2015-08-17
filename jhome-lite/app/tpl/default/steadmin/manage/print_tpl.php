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
<input name="order_id" type="hidden" value="<?php echo $rs['order_id'];?>" />
<div class="table-responsive show-pop-content" style="width:650px; height:380px;">
<section class="panel portlet-item"> 
<header class="panel-heading">订单处理</header> 
<ul class="list-group"> 
<li class="list-group-item">订单号：<?php echo $rs['order_sn'];?></li>
<li class="list-group-item">订单状态：<?php echo $setting['order_status'][$rs['status']];?></li>
<li class="list-group-item" id="show-result" style="display:none"><strong>操作回执：</strong></li>
    <li class="list-group-item" id="result-info"></li>
</ul> 
</section>
<div class="form-group"> 
  <div class="col-lg-8 col-lg-offset-4">
  <?php if(in_array($rs['status'],array(1,3,4,5,6,11))){?>
   <input class="btn btn-success" id="do-print" name="" type="button" value="确认重新打印" />
   <?php
   }else{
   ?>
   <input class="btn btn-danger disabled" name="" type="button" value="订单当前不可打印" />
   <?php }?>
  </div> 
</div>
</div>
</form>
<script>
var orderId='<?php echo $rs['order_id'];?>';
var isPrinting=false;
$(function(){
	$("#facebox").click();
	function showResData(res){
		Msg.hide();
			if (res.status==1){
				Msg.ok('操作完成');
				var strHtml=new Array(),i,isOk=true;
				$("#do-action").hide();
				if ('undefined'!==typeof res.data.prints){
					var prints=null;
					for(i in res.data.prints){
						if (res.data.prints[i].result==1){
							prints='<span class="green">成功√</span>';
						}else{
							prints='<span class="red">失败×，错误信息：'+res.data.prints[i].result+'</span>';
							$("#do-print").show();
							isOk=false;
						}
						strHtml.push('打印机：'+res.data.prints[i].name+'：'+prints);
					}
				}
				if ('undefined'!==typeof res.data.deployment){
					var deployment=null;
					if (res.data.deployment.result==1){
						deployment='<span class="green">成功√</span>';
					}else{
						deployment='<span class="red">失败×，错误信息：'+res.data.deployment.msg+'</span>';
						$("#form1").show();$("#do-action").show();
						isOk=false;
					}
					strHtml.push('通知员工：'+res.data.deployment.name+'：'+deployment);
				}
				if ('undefined'!==typeof res.data.user){
					var user=null;
					if (res.data.user.result==1){
						user='<span class="green">成功√</span>';
					}else{
						user='<span class="red">失败×，错误信息：'+res.data.user.msg+'</span>';
						isOk=false;
					}
					strHtml.push('通知用户：'+user);
				}
				if ('undefined'!==typeof res.data.log){
					var saveLog=null;
					if (res.data.log==1){
						saveLog='<span class="green">成功√</span>';
					}else{
						saveLog='<span class="red">失败×</span>';
						isOk=false;
					}
					strHtml.push('保存日志：'+saveLog);
				}
				//console.log(strHtml);
				$("#show-result").show();$("#result-info").html(strHtml.join('<br/>'));
				if (!isOk){
					$.post('<?php echo U('manage/savelog');?>',{'error':'order:'+orderId,'info':res.data});
				}
			}else{
				Msg.error(res.info);
			}	
	}
	$("#do-print").click(function(){
		if (isPrinting){
			return false;	
		}
		isPrinting=true;
		Msg.loading();
		$.post('<?php echo U('manage/printer');?>',{"id":orderId},function(res){
			showResData(res);
			isPrinting=false;
		},'json').error(function(XMLHttpRequest, textStatus, errorThrown){
			$.post('<?php echo U('manage/savelog');?>',{'error':XMLHttpRequest.status,'info':XMLHttpRequest.responseText});
			isPrinting=false;
		});
	})
})
</script>