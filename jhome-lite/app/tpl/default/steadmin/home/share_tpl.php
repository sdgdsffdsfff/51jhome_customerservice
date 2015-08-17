<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'home-feedback', //页面标示
    'pagename' => '分享', //当前页面名称
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
                <label class="col-sm-1 control-label">分享标题</label>
                <div class="col-sm-3">
                  <input type="text" name="title" id="title" placeholder="" class="form-control" value="<?php echo $rs['title'];?>" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">分享图片</label>
                <div class="col-sm-3">
                  <input type="text" name="imgUrl" id="imgUrl" placeholder="" class="form-control" value="<?php echo $rs['imgUrl'];?>" />
                  <span class="help-block">LOGO图标，标准尺寸110px*110px <?php if ($rs['imgUrl']){?>[<a rel="pop" class="red" target="_blank" href="<?php echo strExists($rs['imgUrl'],'http://')?$rs['imgUrl']:getImgUrl($rs['imgUrl']);?>">预览>></a>]<?php }?></span></div>
                <div class="col-sm-3">
                  <iframe width="280" height="24" src="<?php echo U('upload/index',array('id'=>'imgUrl')); ?>" scrolling="no" frameborder="0"></iframe>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">分享内容</label>
                <div class="col-sm-3">
                  <textarea name="desc"  class="form-control" style="width:500px; height:100px"><?php echo $rs['desc'];?></textarea>
                  <span class="help-block"></span></div>
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
		  $.post('<?php echo U('home/saveshare');?>',$(this).serialize(),function(result){
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
