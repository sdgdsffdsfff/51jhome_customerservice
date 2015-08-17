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
<div class="table-responsive show-pop-content" style="width:650px; height:480px;">
<section class="panel portlet-item"> 
<header class="panel-heading">订单处理</header> 
<ul class="list-group"> 
<li class="list-group-item">订单号：<?php echo $rs['order_sn'];?></li>
<li class="list-group-item">订单状态：<?php echo $setting['order_status'][$rs['status']];?></li>
<li class="list-group-item">
<div class="col-sm-3" style="width:100px">选择操作：</div>
<div class="col-sm-6">
<select class="form-control" style="width:auto;" name="status" id="status">
<option value="0">选择操作</option>
<?php
if ($act==1){
	$actData=$orderDealStatus;
}else{
	$actData=$action;
}
	foreach($actData as $key=>$val){
		if (($key==7&&$rs['status']==8)||!$key||($key>=$rs['status'])){
?>
<option value="<?php echo $key;?>" <?php if($rs['status']==$key){echo 'selected="selected"';}?>><?php echo $val;?></option>
<?php }}?>
</select>
</div>
<div style="clear:both"></div>
</li>
<li class="list-group-item" id="select-5" <?php if($rs['status']!=3){echo 'style="display:none"';}?>>
<div class="col-sm-3" style="width:110px">选择配货员：</div>
<div class="col-sm-6">
<select class="form-control" style="width:auto;" name="deployment_uid" id="deployment_uid">
<?php
if ($distr){
	foreach($distr as $val){
?>
<option value="<?php echo $val['user_id'];?>" <?php if($rs['deployment_uid']==$val['user_id']){echo 'selected="selected"';}?>><?php echo $val['real_name'].':'.$val['phone'];?>[<?php echo $setting['work_status'][$val['work_status']]?>]</option>
<?php }}else{?>
<option value="0">请先添加配货员帐号</option>
<?php }?>
</select>
</div>
<div style="clear:both"></div>
</li>
<li class="list-group-item" id="select-1" <?php if($rs['status']!=6){echo 'style="display:none"';}?>>
<div class="col-sm-3" style="width:110px">选择小管家：</div>
<div class="col-sm-6">
<select class="form-control" style="width:auto;" name="worker_uid" id="worker_uid">
<option value="0">==分配小管家==</option>
<?php
if ($user){
	foreach($user as $val){
?>
<option value="<?php echo $val['user_id'];?>" <?php if($rs['worker_uid']==$val['user_id']){echo 'selected="selected"';}?>><?php echo $val['real_name'].':'.$val['phone'];?>[<?php echo $setting['work_status'][$val['work_status']]?>]</option>
<?php }}else{?>
<option value="0">请先添加小管家帐号</option>
<?php }?>
</select>
</div>
<div style="clear:both"></div>
</li>
<li class="list-group-item">操作备注：</li>
<li class="list-group-item"><textarea name="desc" style="width:600px; height:60px; border:1px #CCCCCC solid; padding:5px" placeholder="选填，请在有特殊操作时记录下"></textarea></li>
<li class="list-group-item" id="show-result" style="display:none"><strong>操作回执：</strong></li>
    <li class="list-group-item" id="result-info"></li>
</ul> 
</section>
<div class="form-group"> 
  <div class="col-lg-8 col-lg-offset-4"> 
   <input class="btn btn-primary" id="do-action" name="" type="submit" value="提交操作" />
   <input class="btn btn-success" id="do-print" name="" type="button" value="重新打印" <?php if(!in_array($rs['status'],array(3,4))){echo 'style="display:none"';}?> />
  </div> 
</div>
</div>
</form>
<script>
var orderId='<?php echo $rs['order_id'];?>';
var isPrinting=false;
$(function(){
	$("#status").change(function(){
		var status=$(this).val();
		$("#select-1").hide();
		switch(status){
			case 3:case '3':
			case 4:case '4':
			$("#select-5").show();
			$("#select-1").show();
			break;
			case 5:case '5':
			$("#select-1").hide();
			$("#select-5").hide();
			break;
			case 6:case '6':
			$("#select-1").show();
			$("#select-5").hide();
			break;
			case 7:case '7':
			case 8:case '8':
			$("#select-1").hide();
			$("#select-5").hide();
			break;
		}
	})
	$("#facebox").click();
	$("#form1").submit(function(){
		Msg.loading();
		$.post('<?php echo U('manage/manage');?>',$(this).serialize(),function(res){
			showResData(res);	
		},'json').error(function(XMLHttpRequest, textStatus, errorThrown){
			$.post('<?php echo U('manage/savelog');?>',{'error':XMLHttpRequest.status,'info':XMLHttpRequest.responseText});
		});
		return false;
	})
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