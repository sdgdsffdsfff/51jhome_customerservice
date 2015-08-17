<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'user-edit', //页面标示
    'pagename' => '编辑个人资料', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array('content/fuelux/fuelux','content/combodate/moment.min','content/combodate/combodate'),
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
                  <label class="col-sm-1 control-label">新密码</label> 
                  <div class="col-sm-3"> 
                   <input type="password" name="psw" id="psw" placeholder=""  class="bg-focus form-control"/>
                   <span class="help-block">密码长度为6-20个字符</span>
                  </div> 
             </div> 

            <div class="form-group"> 
                  <label class="col-sm-1 control-label">重复密码</label> 
                  <div class="col-sm-3"> 
                   <input type="password" name="repsw" id="repsw" class="bg-focus form-control" />
                   <span class="help-block">请重新输入密码</span>
                  </div> 
             </div>

              <div class="form-group">
                  <div class="col-sm-9 col-lg-offset-1">
                    <div class="col-sm-1"><button type="submit" id="sub-ok" data-loading-text="正在提交..." class="btn btn-primary">保存</button></div>
                    <div class="col-md-offset-2"><button type="button" class="btn btn-white">取消</button></div>
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
$(function(){
	$('#userForm').submit(function(){
		if (!$('#psw').val()||!$('#repsw').val()){
			Msg.error('请输入新密码');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		if ($('#psw').val()!=$('#repsw').val()){
			resetSubmit("#sub-ok",'保存');
			Msg.error('两次密码不一致');
			return false;
		}
		Msg.loading();
		  $.post('<?php echo U('my/savepsw');?>',$(this).serialize(),function(result){
			  Msg.hide();
			  resetSubmit("#sub-ok",'保存');
			  if (result.status==1){
				Msg.ok('编辑成功');  
			}else{
				Msg.error(result.info);
			}
		  },'json');
		  return false;
	})	
})
</script>
<?php
include getTpl('footer', 'public');
?>
