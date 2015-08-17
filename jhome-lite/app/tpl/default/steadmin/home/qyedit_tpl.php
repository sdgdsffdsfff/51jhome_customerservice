<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'home-edit', //页面标示
    'pagename' => '企业号设置', //当前页面名称
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
                <label class="col-sm-1 control-label">企业CorpID</label>
                <div class="col-sm-3">
                  <input type="text" style="" name="corpID" id="corpID" placeholder="" class="form-control" value="<?php echo $rs['corpID'];?>" />
                  <span class="help-block">企业CorpID，可在企业号后台设置中查看</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">管理组对应Secret</label>
                <div class="col-sm-3">
                  <input type="text" style="" name="corpSecret" id="corpSecret" placeholder="" class="form-control" value="<?php echo $rs['corpSecret'];?>" />
                  <span class="help-block">企业corpSecret，可在企业号后台设置->权限管理对应的管理组中查看</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">应用ID</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="agentId" id="agentId" placeholder="" class="form-control" value="<?php echo $rs['agentId'];?>" />
                  <span class="help-block">应用ID，可在企业号后台应用中心对应的应用中查看</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">应用回调模式<br/>Token</label>
                <div class="col-sm-3">
                  <input type="text" style="" name="token" id="token" placeholder="" class="form-control" value="<?php echo $rs['token'];?>" />
                  <span class="help-block">在回调模式中查看</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">应用回调模式EncodingAESKey</label>
                <div class="col-sm-3">
                  <input type="text" style="" name="encodingAESKey" id="encodingAESKey" placeholder="" class="form-control" value="<?php echo $rs['encodingAESKey'];?>" />
                  <span class="help-block">在回调模式中查看</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">小管家所在部门ID</label>
                <div class="col-sm-3">
                  <input type="text" style="" name="departmentId" id="departmentId" placeholder="" class="form-control" value="<?php echo $rs['departmentId'];?>" />
                  <span class="help-block">可在企业号后台通讯录中移动到对应的部门点击右侧小箭头查看</span></div>
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
		Msg.loading();
		  $.post('<?php echo U('home/qysave');?>',$(this).serialize(),function(result){
			  Msg.hide();
			  resetSubmit("#sub-ok",'保存');
			  if (result.status==1){
				Msg.ok(result.info);  
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
