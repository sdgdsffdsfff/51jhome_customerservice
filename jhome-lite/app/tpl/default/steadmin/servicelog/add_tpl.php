<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'servicelog-add', //页面标示
    'pagename' => '添加反馈', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
    'footerjs'=>array('content/fuelux/fuelux','content/combodate/moment.min','content/combodate/combodate','content/calendar', 'content/jquery.smoothConfirm'),
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
        <div class="panel panel-servicelog">
          <div class="panel-body">
            <form class="form-horizontal" action="<?php echo U('servicelog/save');?>" method="post" id="form-fb1">
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">时间</label>
                <div class="col-lg-2">
                  <input class="form-control fbtime" type="text" value="<?php echo $today; ?>" name="fbtime" readonly />
                </div>
              </div>
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">类型</label>
                <div class="col-lg-2">
                  <select name="fbtype" id="fb-typelist"  class="form-control">
                    <option value="" checked>请选择</option>
                    <?php foreach ($types as $key => $value) { ?>
                      <option value="<?php echo $key;?>"><?php echo $value['name'];?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">是否处理</label>
                <div class="col-lg-4">
                    <?php foreach ($status as $key => $value) { ?>
                      <div class="col-xs-3 radio m-l-large">
							<label class="radio-custom">
							  <input type="radio" name="fbstatus" <?php if(!$key){echo 'checked="checked"';}?> value="<?php echo $key;?>">
							  <i class="fa fa-circle-o <?php if(!$key){echo 'checked';}?>"></i><?php echo $value;?></label>
						  </div>
                    <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label for="input_order_id" class="col-sm-1 control-label">订单号</label>
                <div class="col-lg-2">
                  <input type="text" class="form-control" name="order_id" value="" id="input_order_id">
                </div>
              </div>
              <div class="form-group">
                <label for="input_client_username" class="col-sm-1 control-label">用户名</label>
                <div class="col-lg-2">
                  <input type="text" class="form-control" name="username" value="" id="input_username">
                </div>
              </div>
              <div class="form-group">
                <label for="input_phone" class="col-sm-1 control-label">手机号</label>
                <div class="col-lg-2">
                  <input type="text" class="form-control" name="phone" value="" id="input_phone">
                </div>
              </div>
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">反馈内容</label>
                <div class="col-lg-5">
                  <textarea name="fbcontent" cols="30" rows="10" class="form-control servicelogContent"></textarea>
                </div>
              </div>
              
              <div class="form-group">
                <label for="" class="col-sm-1 control-label">附件</label>
                <div class="col-sm-3">
                  <input type="text" name="fbupload" id="fb_upload" placeholder="" class="form-control" value="" />
                  <span class="help-block">请上传相关附件（请控制在2mb以下，多个附件打包成压缩包上传)</span></div>
                <div class="col-sm-3">
                  <iframe width="280" height="24" src="<?php echo U('upload/index',array('id'=>'fb_upload')); ?>" scrolling="no" frameborder="0"></iframe>
                </div>
              </div>
              <div class="form-group">
                <div class="col-lg-3 col-sm-offset-1">
                  <input type="submit" id="btn-savefb" class="btn btn-primary" value="保存"> 
                  <a href="<?php echo U('servicelog/index');?>" class="btn btn-default" id="btn-cancelfb" style="margin:0 10px">返回</a>                 
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
  $(document).on('click', '.fbtime', function(){
    new Calendar().show(this);
  });
  var clicked = false;
  //保存反馈的按钮的监听事件
  $('#btn-savefb').click(function(event){
    event.preventDefault();
    if ($('#fb-typelist').val() == ''){
      Msg.error("请选择类型");
      return ;
    }
    if( $('input[name=fbstatus]:checked').length == 0){
      Msg.error("请选择处理结果");
      return ;
    }
    if( $('.servicelogContent').val() == '' ){
      Msg.error("请填写反馈内容");
      return ;
    }
    if(!clicked){
      clicked = true;
	   Msg.loading();
      $.ajax({
        url: "<?php echo U('servicelog/post');?>",
        type: 'POST',
        data: $('#form-fb1').serialize(),
        dataType: 'json',
        success: function(retJson){
			Msg.hide();
          clicked = false;
          if(retJson.status != 1){
            Msg.error(retJson.info);
          }else{
            Msg.ok(retJson.info);
            $('.servicelogContent').val('');
            $('#fb_upload').val('');
            $('input[type=radio]').removeAttr('checked');
            $('#fb-typelist').val('');
            $('#input_phone').val('');
            $('#input_order_id').val('');
            $('#input_username').val('');
          }
        },
        error: function(){
			    Msg.hide();
          clicked = false;
          Msg.error('保存失败');
        }
      });
    }
    
  });
  //添加反馈那里的取消按钮
  $('#btn-cancelfb').click(function(event){
    var t = this;
    $(t).smoothConfirm('您还有未保存的数据', {
      'okVal': '离开',
      'cancelVal': '留下',
      'ok': function(){
        window.location.href = $(t).attr('href');
        return false;
      },
      'cancel': function(){

      }
    });
    return false;
  });
});
</script>
<?php
include getTpl('footer', 'public');
?>
