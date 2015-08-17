<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'user-add', //页面标示
    'pagename' => '添加帐号', //当前页面名称
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
                    <option value="<?php echo $key;?>"><?php echo $val;?></option>
                    <?php
				   }
				   ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">帐户名</label>
                <div class="col-sm-3">
                  <input type="text" name="username" id="username" placeholder="" class="form-control" value="" />
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
                  <input type="text" name="effective" id="effectiveeffective" placeholder="" class="form-control" value="" onClick="new Calendar().show(this);" readonly/>
                  <span class="help-block"></span></div>
                <div class="col-sm-3"></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">真实姓名</label>
                <div class="col-sm-3">
                  <input type="text" name="real_name" id="real_name" placeholder="必填" class="form-control" value="" />
                  <span class="help-block">姓名长度为2-30个字符</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">手机号码</label>
                <div class="col-sm-3">
                  <input type="text" name="phone" id="phone" placeholder="必填" class="form-control" value="" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">微信号</label>
                <div class="col-sm-3">
                  <input type="text" name="wechat_id" id="wechat_id" placeholder="不要输入中文" class="form-control" value="" />
                  <span class="help-block">微信号，可在微信 “我”的顶部找到</span></div>
                  <span id="wechat_id_exist" style="color:red;"></span>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">微信ID(推广用的id)</label>
                <div class="col-sm-3">
                  <input type="text" name="wx_uid" id="wx_uid" placeholder="不要输入中文" class="form-control" value="" />
                  <span class="help-block">在微信"结邻公社"回复"我的号码"，即可获取</span></div>
                  <span id="wechat_id_exist" style="color:red;"></span>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">UserId</label>
                <div class="col-sm-3">
                  <input type="text" name="openid" id="openid" placeholder="不要输入中文" class="form-control" value="" />
                  <span class="help-block">选填，如果该员工已经在企业号录入过则在此填写</span></div>
                  <div class="col-sm-3">
                  <a href="javascript:;" id="test-openid" class="btn btn-defaultcol-md-offset-1" rel="pop">测试消息</a>
                  </div>
              </div>
              <div class="form-group" id="show-areazone"  <?php if(!$isPresident){echo 'style="display:none"';}?>>
                <label class="col-sm-1 control-label">所属服务中心</label>
                <div class="col-sm-3">
                  <input type="text" name="stitle" id="stitle" placeholder="" class="form-control" value="" readonly="readonly" />
                  <span class="help-block"></span></div>
                  <div class="col-sm-3">
                  <a href="javascript:;" class="btn btn-default" id="select-areazone" rel="pop">设定服务中心</a>
                  <a href="javascript:;" class="clear-area btn btn-default search-areazone col-md-offset-1" rel="group">清除</a>
                  </div>
              </div>
              <div class="form-group" id="select-shop" style="display:none">
                <label class="col-sm-1 control-label">绑定店铺</label>
                <div class="col-lg-2">
                  <select name="shop_id" id="shop_id" class="form-control">
                   <?php
				   if ($shop){
				   foreach($shop as $key=>$val){
				   ?>
                    <option value="<?php echo $val['shop_id'];?>"><?php echo $val['shop_name'].$val['shop_alt_name'];?></option>
                    <?php
				   }
				   }else{
					   echo '<option value="0">请先添加店铺</option>';
				   }
				   ?>
                  </select>
                </div>
              </div>
              <div id="show-worker-info" <?php if(!$isPresident){echo 'style="display:none"';}?>>
              <!---->
              <div class="form-group">
                <label class="col-sm-1 control-label">用户头像</label>
                <div class="col-sm-3">
                  <input type="text" name="user_avatar" id="user_avatar" placeholder="" class="form-control" value="" />
                  <span class="help-block">用户头像，标准尺寸132px*132px</span></div>
                <div class="col-sm-3">
                  <iframe width="280" height="24" src="<?php echo U('upload/index',array('id'=>'user_avatar'));?>" scrolling="no" frameborder="0"></iframe>
                </div>
              </div>
              	<div class="form-group">
                <label class="col-sm-1 control-label">称呼</label>
                <div class="col-sm-3">
                  <input type="text" name="nick_name" id="nick_name" placeholder="必填，展示给用户" class="form-control" value="" />
                  <span class="help-block">姓名长度为2-30个字符</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">总服务次数</label>
                <div class="col-sm-1">
                  <input type="text" name="total_service" id="total_service" style="width:80px" placeholder="" class="form-control" value="10" />
                  <span class="help-block">纯数字</span>
               </div>
                <div class="col-sm-1 help-block" style="padding-left:0;">次</div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">平均服务时间</label>
                <div class="col-sm-1">
                  <input type="text" name="average_times" id="average_times" style="width:80px" placeholder="" class="form-control" value="45" />
                  <span class="help-block">纯数字</span></div>
                  <div class="col-sm-1 help-block" style="padding-left:0;">分钟</div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">服务评分</label>
                <div class="col-sm-3">
                  <input type="text" name="score_service" id="score_service" style="width:80px" placeholder="" class="form-control" value="10.0" />
                  <span class="help-block">评分满分10.0分</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">速度评分</label>
                <div class="col-sm-3">
                  <input type="text" name="score_speed" id="score_speed" style="width:80px" placeholder="" class="form-control" value="10.0" />
                  <span class="help-block">评分满分10.0分</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">评论人次</label>
                <div class="col-sm-1">
                  <input type="text" name="total_comment" id="total_comment" style="width:80px" placeholder="" class="form-control" value="0" />
                  <span class="help-block">纯数字</span>
                  </div>
                  <div class="col-sm-1 help-block" style="padding-left:0;">次</div>
              </div>
              <!---->
              </div>
              <div class="form-group">
                <div class="col-sm-9 col-lg-offset-1">
                  <div class="col-sm-1">
                  	<input type="hidden" name="service_id" id="service_id" placeholder="" class="form-control" value="0" />
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
function checkifenglish(string){
    var letters = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz1234567890_";
    var i;
    var c;
    for( i = 0; i < string.length; i ++ ){
        c = string.charat( i );
		if (letters.indexof( c ) < 0){
		    return 321;
		}
    }
    return 123;
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
		if (!psw||!repsw){
			Msg.error('密码不能为空');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		if (psw!=repsw){
			Msg.error('密码输入不一致');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		Msg.loading();
		  $.post('<?php echo U('user/post');?>',$(this).serialize(),function(result){
			  //alert(result);
			  Msg.hide();
			  resetSubmit("#sub-ok",'保存');
			  if (result.status==1){
				Msg.ok('添加成功',function(){
					document.location='<?php echo U('user/index');?>';	
				},1000);  
			}else{
				Msg.error(result.info);
			}
		  //});
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
        $("#wechat_id").focusout(function() {
            $.post('<?php echo U('user/checkexist'); ?>', {'wechat_id': $("#wechat_id").val()}, function(result) {
                if (result.status == 1) {
                    $('#wechat_id_exist').text('微信号已经存在，'+result.data.real_name+'-'+result.data.username+'-'+result.data.phone);
                    $('#openid').val('steward_'+result.data.user_id);
                }
            }, 'json');
            return false;
        });
})
</script>
<?php
include getTpl('footer', 'public');
?>