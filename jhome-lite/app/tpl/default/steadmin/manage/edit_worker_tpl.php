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
  <input name="type" type="hidden" value="<?php echo $type;?>" />
  <div class="table-responsive show-pop-content" style="width:650px; height:480px;">
  <div class="alert alert-info" style="font-size:13px"> 
       <button data-dismiss="alert" class="close" type="button"><i class="fa fa-times"></i></button> 
       <i class="fa fa-info-sign fa-lg"></i>
       <strong>提示信息：</strong> 当订单处于小管家配送阶段，以下数据有变化时会自动通知小管家订单的变更信息，请注意检查操作回执。
   </div>
    <section class="panel portlet-item">
      <ul class="list-group">
        <li class="list-group-item">订单号：<?php echo $rs['order_sn'];?></li>
        <li class="list-group-item">订单状态：<?php echo $setting['order_status'][$rs['status']];?></li>
        <li class="list-group-item" id="select-1">
        <div class="col-sm-3" style="width:110px">更换人员：</div>
        <div class="col-sm-6">
        <select class="form-control" style="width:auto;" name="worker_uid" id="worker_uid">
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
        <li class="list-group-item" id="show-result" style="display:none"><strong>操作回执：</strong></li>
        <li class="list-group-item" id="result-info"></li>
      </ul>
    </section>
    <div class="form-group">
      <div class="col-lg-8 col-lg-offset-4">
        <input class="btn btn-primary" id="do-action"  name="" type="submit" value="提交操作" />
      </div>
    </div>
  </div>
</form>
<script>
var orderId='<?php echo $rs['order_id'];?>';
$(function(){
	$("#facebox").click();
	$("#form1").submit(function(){
		Msg.loading();
		$.post('<?php echo U('manage/edit_worker_save');?>',$(this).serialize(),function(res){
			Msg.hide();
			if (res.status==1){
				Msg.ok('操作完成');
				var strHtml=new Array(),i,isOk=true;
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
				if ('undefined'!==typeof res.data.deployment_old){
					var deployment=null;
					if (res.data.deployment_old.result==1){
						deployment='<span class="green">成功√</span>';
					}else{
						deployment='<span class="red">失败×，错误信息：'+res.data.deployment_old.msg+'</span>';
						$("#form1").show();$("#do-action").show();
						isOk=false;
					}
					strHtml.push('通知员工：'+res.data.deployment_old.name+'：'+deployment);
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
			}else if(res.status==2){
				Msg.alert(res.info);
			}else{
				Msg.error(res.info);
			}	
		},'json').error(function(XMLHttpRequest, textStatus, errorThrown){
			$.post('<?php echo U('manage/savelog');?>',{'error':XMLHttpRequest.status,'info':XMLHttpRequest.responseText});
		});
		return false;
	})
})
</script>