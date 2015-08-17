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
  <div class="alert alert-info" style="font-size:13px"> 
       <button data-dismiss="alert" class="close" type="button"><i class="fa fa-times"></i></button> 
       <i class="fa fa-info-sign fa-lg"></i>
       <strong>提示信息：</strong> 当订单处于小管家配送阶段，以下数据有变化时会自动通知小管家和用户订单的变更信息，请注意检查操作回执。
   </div>
    <section class="panel portlet-item">
      <ul class="list-group">
        <li class="list-group-item">订单号：<?php echo $rs['order_sn'];?></li>
        <li class="list-group-item">订单状态：<?php echo $setting['order_status'][$rs['status']];?></li>
        <li class="list-group-item" id="select-5">
        <div class="col-sm-3" style="width:100px; padding:0">选择配送日期：</div>
        <div class="col-sm-6" style="padding:0">
        <select class="form-control" style="width:auto;" name="arrive_date" id="arrive_date">
        <?php
        $selectDateTime=$timer[0]['times'];
        $selectData=array();
		$haveDate=false;
		$haveTime=false;
		$showDate='';
        foreach($timer as $key=>$val){
            if ($rs['arrive_date']==$val['date']){
                $selectDateTime=$val['times'];
            }
            $selectData[$key]=$val['times'];
			$showDate.='<option rel="'.$key.'" value="'.$val['date'].'" ';
			if($rs['arrive_date']==$val['date']){
				$haveDate=true;
				$showDate.='selected="selected"';
			}
			$showDate.='>'.$val['desc'].' '.$val['show'].'</option>';
        }
		if (!$haveDate){
         $showDate='<option value="'.$rs['arrive_date'].'" selected="selected">'.outTime($rs['arrive_date'],2).'</option>'.$showDate;
		}
		echo $showDate;
		?>
        </select>
        </div>
        <div style="clear:both"></div>
        </li>
        <li class="list-group-item" id="select-1">
        <div class="col-sm-3" style="width:100px; padding:0">选择配送时间：</div>
        <div class="col-sm-6" style="padding:0">
        <select class="form-control" style="width:auto;" name="arrive_time" id="arrive_time">
        <?php
		$showTime='';
        foreach($selectDateTime as $val){
			$showTime.='<option value="'.$val.'" ';
			if($rs['arrive_time']==$val){
				$haveTime=true;
				$showTime.='selected="selected"';
			}
            $showTime.='>'.$val.'</option>';
        }
		if (!$haveTime){
         $showTime='<option value="'.$rs['arrive_time'].'" selected="selected">'.$rs['arrive_time'].'</option>'.$showTime;
		}
		echo $showTime;
		?>
        </select>
        </div>
        <div style="clear:both"></div>
        </li>
        <li class="list-group-item">收货人姓名：
          <input style="width:180px" type="text" value="<?php echo $rs['username'];?>" class="form-control" placeholder="" id="username" name="username">
        </li>
        <li class="list-group-item">手机号码：
          <input style="width:180px" type="text" value="<?php echo $rs['phone'];?>" class="form-control" placeholder="" id="phone" name="phone">
        </li>
        <li class="list-group-item">收货地址：
          <textarea name="address" id="address" style="width:600px; height:60px; border:1px #CCCCCC solid; padding:5px" placeholder=""><?php echo $rs['address'];?></textarea>
        </li>
        <li class="list-group-item" id="show-result" style="display:none"><strong>操作回执：</strong></li>
        <li class="list-group-item" id="result-info"></li>
      </ul>
    </section>
    <div style="display:none">
	<?php
    foreach($selectData as $k=>$v){
    ?>
    <div id="select-date-detail-<?php echo $k;?>">
    <?php
    foreach($v as $val){
    ?>
    <option value="<?php echo $val;?>"><?php echo $val;?></option>
    <?php
    }
    ?>
    </div>
    <?php
    }
    ?>
    </div>
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
	$("#arrive_date").change(function(){
		var date=$(this).find("option:selected").attr('rel');
		$("#arrive_time").html($("#select-date-detail-"+date).html());
	})
	$("#facebox").click();
	$("#form1").submit(function(){
		Msg.loading();
		$.post('<?php echo U('manage/save');?>',$(this).serialize(),function(res){
			Msg.hide();
			if (res.status==1){
				Msg.ok('操作完成');
				var strHtml=new Array(),i,isOk=true;
				if ('undefined'!==typeof res.data.prints){
					var prints=null;
					for(i in res.data.prints){
						if (res.data.prints[i].result==1){
							prints='<span class="green">成功√</span>';
						}else{
							prints='<span class="red">失败×，错误信息：'+res.data.prints[i].result+'</span>';
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
				if ('undefined'!==typeof res.data.order){
					var saveLog=null;
					if (res.data.order==1){
						saveLog='<span class="green">成功√</span>';
					}else{
						saveLog='<span class="red">失败×</span>';
						isOk=false;
					}
					strHtml.push('修改订单：'+saveLog);
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