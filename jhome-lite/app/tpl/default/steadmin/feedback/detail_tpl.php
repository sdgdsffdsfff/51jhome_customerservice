<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'feedback-detail', //页面标示
    'pagename' => '查看反馈', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
    'footerjs'=>array('content/calendar', 'content/msgbox', 'content/jquery.easing'),
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
        <div class="panel panel-feedback">
          <div class="panel-body">
            <form class="form-horizontal" action="" method="post" id="form-fb1">
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">时间</label>
                <div class="col-lg-2">
                  <input class="form-control fbtime" type="text" value="<?php echo $feedback['fb_time']; ?>" name="fbtime" readonly />
                </div>
              </div>
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">反馈人</label>
                <div class="col-lg-2">
                    <p class="form-control"><?php echo $feedback['username'];?></p>
                </div>
              </div>
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">反馈人真实姓名</label>
                <div class="col-lg-2">
                    <p class="form-control"><?php echo $feedback['real_name'];?></p>
                </div>
              </div>
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">类型</label>
                <div class="col-lg-2">
                  <input type="text" readonly="" class="form-control" value="<?php echo $type[$feedback['type_id']]['name']; ?>">                  
                </div>
              </div>
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">是否处理</label>
                <div class="col-lg-2">
                    <p class="form-control" id="fb_status"><?php echo $status[$feedback['status_id']];?></p>
                </div>
                <?php /* 与配置文件强耦合 需要优化 */ ?>
                <?php if($feedback['status_id'] == 1){ ?>
                  <div class="col-lg-2">
                    <button class="btn btn-default" id="btn-dealfb" data-fid="<?php echo $feedback['fid'];?>">标记为已处理</button>
                  </div>
                <?php } ?>
                
              </div>
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">反馈内容</label>
                <div class="col-lg-5">
                  <p class="form-control"><?php echo $feedback['feedback']; ?></p>
                </div>
              </div>
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">附件</label>
                <div class="col-lg-2">
                  <?php if(isHave($feedback['upload'])){ ?>
                    <?php if(preg_match('/^.*?\.(jpg|png|gif|jpeg|bmp|jpe)$/', $feedback['upload'])){ ?>
                      <img src="<?php echo getImgUrl($feedback['upload']);?>" />
                    <?php }else{ ?>
                      <p class="form-control"><a href="<?php echo getImgUrl($feedback['upload']);?>"><i class="fa fa-download"></i>
点击此处下载附件</a></p>
                    <?php } ?>
                  <?php }else{ ?>
                    <p class="form-control">无附件</p>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <div class="col-lg-2 col-sm-offset-1">
                  <a href="<?php echo U('feedback/index');?>" class="btn btn-default">返回至反馈页面</a>
                </div>
              </div>
            </form>         
          </div>
        </div>     
      </div>
    </div>
  </section>
</section>
<!--主体 结束--> 


<script>
$(function(){
  $('#btn-dealfb').click(function(event){
    var t = $(this);
    event.preventDefault();
    var fid = parseInt($(this).data('fid'));
    if(typeof fid === 'number'){
      if(confirm('确定要标记为已处理么?')){
        $.ajax({
          url: '<?php echo U("feedback/update_feedback_status"); ?>',
          type: 'POST',
          data: 'fid=' + fid,
          dataType: 'json',
          success: function(data){
            if(data && data.status == 1){
              Msg.alert(data.info);
              $('#fb_status').text('已处理');
              $(t).remove();
            }else{
              Msg.error(data.info);
            }
          },
          error: function(){
            Msg.error('服务器正在开小差');
          }
        });
      }      
    }
  });
});
</script>
<?php
include getTpl('footer', 'public');
?>
