<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'user-edit', //页面标示
    'pagename' => '编辑帐号', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array('content/fuelux/fuelux','content/combodate/moment.min','content/combodate/combodate','content/calendar'),
    'head' => true, //加载头部文件
);
include getTpl('header', 'public');
?>
<!--顶部导航 开始-->
<?php include getTpl('top', 'public');?>
<!--顶部导航 结束-->
<!--左侧菜单 开始-->
<?php include getTpl('nav', 'public');?>
<!--左侧菜单 结束-->
<!--主体 开始-->
<section id="content">
  <section class="main padder">
    <div class="clearfix">
      <h4><i class="fa fa-edit"></i><?php echo trim($Document['pagename'],'_');?></h4>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <section class="panel">
          <div class="panel-body">
            <form class="form-horizontal" method="post" id="userForm">
             <div class="form-group">
                <label class="col-sm-1 control-label">帐号组别</label>
                <div class="col-lg-2">
                  <select name="groupid" id="groupid" class="form-control">
                   <?php
				   foreach($group as $key=>$val){
				   ?>
                    <option value="<?php echo $key;?>" <?php if ($rs['groupid']==$key){echo 'selected="selected"';}?>><?php echo $val;?></option>
                    <?php
				   }
				   ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">帐户名</label>
                <div class="col-sm-3">
                  <input type="text" name="username" id="username" placeholder="" class="form-control" readonly="readonly" value="<?php echo $rs['username'];?>" />
                  <span class="help-block">帐户长度为2-30个字符</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">密码</label>
                <div class="col-sm-3">
                  <input type="password" name="psw" id="psw" placeholder="" class="form-control" value="" />
                  <span class="help-block">密码长度必须大于4个字符</span></div>
                <div class="col-sm-3"></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">重复密码</label>
                <div class="col-sm-3">
                  <input type="password" name="repsw" id="repsw" placeholder="" class="form-control" value="" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">帐号有效期</label>
                <div class="col-sm-2">
                  <input type="text" name="effective" id="effective" placeholder="" class="form-control" value="<?php echo $rs['effective']?outTime($rs['effective'],2):'';?>" onClick="new Calendar().show(this);" readonly/>
                  <span class="help-block"></span></div>
                <div class="col-sm-3"></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">真实姓名</label>
                <div class="col-sm-3">
                  <input type="text" name="real_name" id="real_name" placeholder="必填" class="form-control" value="<?php echo $rs['real_name'];?>" />
                  <span class="help-block">姓名长度为2-30个字符</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">手机号码</label>
                <div class="col-sm-3">
                  <input type="text" name="phone" id="phone" placeholder="必填" class="form-control" value="<?php echo $rs['phone'];?>" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">openid</label>
                <div class="col-sm-3">
                  <input type="text" name="openid" id="openid" placeholder="" class="form-control" value="<?php echo $rs['openid'];?>" />
                  <span class="help-block">对应企业号中的帐号，如无特殊情况请勿更改</span></div>
                  <div class="col-sm-3">
                  <a href="javascript:;" id="test-openid" class="btn btn-defaultcol-md-offset-1" rel="pop">测试消息</a>
                  </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">微信号</label>
                <div class="col-sm-3">
                  <input type="text" name="wechat_id" id="wechat_id" placeholder="不要输入中文" class="form-control" value="<?php echo $rs['wechat_id'];?>" />
                  <span class="help-block">微信号，可在微信 "我"的顶部找到</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">微信ID(推广用的id)</label>
                <div class="col-sm-3">
                  <input type="text" name="wx_uid" id="wx_uid" placeholder="不要输入中文" class="form-control" value="<?php echo $rs['wx_uid'];?>" />
                  <span class="help-block">在微信“结邻公社”回复“我的号码”，即可获取</span></div>
              </div>
              <div class="form-group" id="show-areazone" <?php if(!in_array($rs['groupid'],array(3,4,5,9))){echo 'style="display:none"';}?>>
                <label class="col-sm-1 control-label">所属服务中心</label>
                <div class="col-sm-3">
                  <input type="text" name="stitle" id="stitle" placeholder="" class="form-control" value="<?php echo $rs['stitle'];?>" readonly="readonly" />
                  <span class="help-block"></span></div>
                  <div class="col-sm-3">
                  <a href="javascript:;" class="btn btn-default" id="select-areazone" rel="pop">设定服务中心</a>
                  <a href="javascript:;" class="clear-area btn btn-default search-areazone col-md-offset-1" rel="group">清除</a>
                  </div>
              </div>
              <div class="form-group" id="select-shop" <?php if($rs['groupid']!=8){echo 'style="display:none"';}?>>
                <label class="col-sm-1 control-label">绑定店铺</label>
                <div class="col-lg-2">
                  <select name="shop_id" id="shop_id" class="form-control">
                   <?php
				   if ($shop){
				   foreach($shop as $key=>$val){
				   ?>
                    <option value="<?php echo $val['shop_id'];?>" <?php if ($rs['shop_id']==$val['shop_id']){echo 'selected="selected"';}?>><?php echo $val['shop_name'].$val['shop_alt_name'];?></option>
                    <?php
				   }
				   }else{
					   echo '<option value="0">请先添加店铺</option>';
				   }
				   ?>
                  </select>
                </div>
              </div>
              <div id="show-worker-info" <?php if(!$rs['isShowDetail']){echo 'style="display:none"';}?>>
              <!---->
              <div class="form-group">
                <label class="col-sm-1 control-label">用户头像</label>
                <div class="col-sm-3">
                  <input type="text" name="user_avatar" id="user_avatar" placeholder="" class="form-control" value="<?php echo $rs['user_avatar'];?>" />
                  <span class="help-block">用户头像，标准尺寸132px*132px  <?php if ($rs['user_avatar']){?>[<a rel="pop" class="red" target="_blank" href="<?php echo getImgUrl($rs['user_avatar']);?>">预览>></a>]<?php }?></span></div>
                <div class="col-sm-3">
                  <iframe width="280" height="24" src="<?php echo U('upload/index',array('id'=>'user_avatar'));?>" scrolling="no" frameborder="0"></iframe>
                </div>
              </div>
              	<div class="form-group">
                <label class="col-sm-1 control-label">称呼</label>
                <div class="col-sm-3">
                  <input type="text" name="nick_name" id="nick_name" placeholder="必填，展示给用户" class="form-control" value="<?php echo $rs['nick_name'];?>" />
                  <span class="help-block">姓名长度为2-30个字符</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">总服务次数</label>
                <div class="col-sm-1">
                  <input type="text" name="total_service" id="total_service" style="width:80px" placeholder="" class="form-control" value="<?php echo $rs['total_service'];?>" />
                  <span class="help-block">纯数字</span>
               </div>
                <div class="col-sm-1 help-block" style="padding-left:0;">次</div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">平均服务时间</label>
                <div class="col-sm-1">
                  <input type="text" name="average_times" id="average_times" style="width:80px" placeholder="" class="form-control" value="<?php echo $rs['average_times'];?>" />
                  <span class="help-block">纯数字</span></div>
                  <div class="col-sm-1 help-block" style="padding-left:0;">分钟</div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">服务评分</label>
                <div class="col-sm-3">
                  <input type="text" name="score_service" id="score_service" style="width:80px" placeholder="" class="form-control" value="<?php echo $rs['score_service'];?>" />
                  <span class="help-block">评分满分10.0分</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">速度评分</label>
                <div class="col-sm-3">
                  <input type="text" name="score_speed" id="score_speed" style="width:80px" placeholder="" class="form-control" value="<?php echo $rs['score_speed'];?>" />
                  <span class="help-block">评分满分10.0分</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">评论人次</label>
                <div class="col-sm-1">
                  <input type="text" name="total_comment" id="total_comment" style="width:80px" placeholder="" class="form-control" value="<?php echo $rs['total_comment'];?>" />
                  <span class="help-block">纯数字</span>
                  </div>
                  <div class="col-sm-1 help-block" style="padding-left:0;">次</div>
              </div>
              <!---->
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">状态</label>
                <div class="col-sm-5">
                  <div class="row">
                  <?php
                  foreach($status as $k=>$v){
				  ?>
                    <div class="col-xs-2 radio m-l-large">
                      <label class="radio-custom">
                        <input type="radio" name="status"  value="<?php echo $k;?>" <?php if($rs['status']==$k){echo 'checked="checked"';}?>/>
                        <i class="fa fa-circle-o"></i><?php echo $v;?></label>
                    </div>
					<?php
				  }
					?>
                  </div>
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <div class="col-sm-9 col-lg-offset-1">
                  <div class="col-sm-1">
                  	<input type="hidden" name="service_id" id="service_id" placeholder="" class="form-control" value="<?php echo $rs['service_id'];?>" />
                    <input type="hidden" name="id" id="id" placeholder="" class="form-control" value="<?php echo $rs['user_id'];?>" />
                    <button type="submit" id="sub-ok" data-loading-text="正在提交..." class="btn btn-primary">保存</button>
                  </div>
                  <div class="col-md-offset-2">
                    <button type="button" class="btn btn-white">取消</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </section>
      </div>
    </div>
  </section>
</section>
<!--主体 结束--> 
<script>
function setAreaZone(sid,stitle){
		//console.log(sid);
		//console.log(stitle);
		$("#service_id").val(sid);
		$("#stitle").val(stitle);
		$(document).trigger('close.facebox');
}
$(function(){
	var groupid=$.trim($("#groupid").val());
	$('#userForm').submit(function(){
		var username=$.trim($("#username").val());
		var psw=$.trim($("#psw").val());
		var repsw=$.trim($("#repsw").val());
		var sid=$.trim($("#service_id").val());
		var isOk=true;
		if (!username){
			Msg.error('帐户名不能为空');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		if (psw&&psw!=repsw){
			Msg.error('密码输入不一致');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		Msg.loading();
		  $.post('<?php echo U('user/save');?>',$(this).serialize(),function(result){
			  Msg.hide();
			  resetSubmit("#sub-ok",'保存');
			  if (result.status==1){
				Msg.ok('编辑成功',function(){
					//document.location='<?php echo U('user/index');?>';
					history.go(-1);
				},1000);  
			}else{
				Msg.error(result.info);
			}
		  },'json');
		  return false;
	})
	$("#groupid").change(function(){
		groupid=$(this).val();
		switch(groupid){
			case '1':case '2':case '6':
			$("#show-areazone").hide();
			$("#show-worker-info").hide();
			$("#select-shop").hide();
			break;
			case '7':
			$("#show-areazone").hide();
			$("#select-shop").hide();
			break;
			case '8':
			$("#select-shop").show();
			break;
			default:
			$("#show-areazone").show();
			$("#show-worker-info").show();
			$("#select-shop").hide();
		}
	});
	$(".search-areazone").click(function(){
		var t=$(this).attr("rel");
		$("#stitle").val('');
		$("#service_id").val('');
		return false;
	})
	$("#select-areazone").click(function(){
		$(this).attr('href','<?php echo U('service/search',array(),true);?>');
	})
	$("#test-openid").click(function(){
		var openid=$("#openid").val();
		if (openid){
			$(this).attr('href','<?php echo U('user/testqy');?>?openid='+openid);
		}else{
			Msg.error('请先输入openid');
		}	
	})
})
</script>
<?php
include getTpl('footer', 'public');
?>