<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'feedback-index', //页面标示
    'pagename' => '每日反馈', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
    'footerjs'=>array('content/calendar.js', 'content/msgbox.js', 'content/jquery.easing.js'),
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
        <div class="panel-body">
          <div class="row text-small">
            <div class="col-sm-3">
              <a href="<?php echo U('feedback/add');?>" class="btn btn-default">添加反馈</a>
              <a href="<?php echo U('feedback/index');?>" class="btn btn-default">查看全部</a>
              <div class="btn-group" data-resize="auto" style="margin-left: 5px;">
                <button class="btn btn-white dropdown-toggle" type="button" data-toggle="dropdown">
                  <span class="dropdown-label">
                    <span class="green">反馈类型</span>
                  </span>
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li data-value="0"><a href="<?php echo U('feedback/index', getSearchUrl(array('fbtype'=> null)));?>">全部</a></li>
                  <?php foreach ($types as $key => $value) { ?>
                    <?php $key += 1; ?>
                    <li data-value="<?php echo $key;?>" <?php if($key == ($type_id)) echo "class=\"active\"";?>>
                      <a href="<?php echo U('feedback/index', getSearchUrl(array('fbtype' => $key)));?>">
                        <?php echo $value['name'];?>
                      </a>
                    </li>
                  <? } ?>
                </ul>
              </div><!-- end of btn-group -->

              <div class="btn-group" style="margin-left: 5px;">
                <button class="btn btn-white dropdown-toggle" type="button" data-toggle="dropdown">
                  <span class="dropdown-label">
                    <span class="green">处理结果</span>
                  </span>
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li data-value="0"><a href="<?php echo U('feedback/index', getSearchUrl(array('fbresult' => null)));?>">全部</a></li>
                  <?php foreach ($status as $key => $value) { ?>
                    <?php $key += 1; ?>
                    <li data-value="<?php echo $key;?>" <?php if($key == ($status_id)) echo "class=\"active\"";?>>
                      <a href="<?php echo U('feedback/index', getSearchUrl(array('fbresult' => $key)));?>">
                        <?php echo $value; ?>
                      </a>
                    </li>
                  <? } ?>
                </ul>
              </div><!-- end of btn-group -->

            </div><!-- end of col-sm-3 -->
            <div class="col-sm-offset-5 col-sm-4">
              <form action="<?php echo U('feedback/index', getSearchUrl()); ?>" method="post" class="form-inline">
                <div class="form-group">
                  <input type="text" readonly="" onclick="new Calendar().show(this);" value="<?php if(isHave($startTime)) echo $startTime;?>" class="form-control" placeholder="开始时间" id="btn-starttime" name="start">
                </div>
                <div class="form-group">
                  <input type="text" readonly="" onclick="new Calendar().show(this);" value="<?php if(isHave($endTime)) echo $endTime;?>" class="form-control" placeholder="结束时间" id="btn-endtime" name="end">
                </div>
                <div class="form-group">
                  <button class="btn btn-default" type="submit">查找</button>
                </div>
                
              </form>
              
            </div>
          </div><!-- end of row -->
        </div> <!-- end of panel body -->
        
        <div class="panel">
          <div class="panel-body">
            <div class="row">
              <div class="table-responsive">
                <table class="table table-stripped">
                  <thead>
                    <tr>
                      <th class="col-sm-1">编号</th>
                      <th class="col-sm-1">时间</td>
                      <th class="col-sm-1">反馈类型</th>
                      <th class="col-sm-5">反馈内容</th>
                      <th  class="col-sm-1">处理结果</th>
                      <!--<th class="col-sm-1">反馈人</th>-->
                      <th class="col-sm-1">发表时间</th>
                      <th class="col-sm-1">附件</th>
                      <th class="col-sm-1">操作</th>
                    </tr>
                  </thead> 
                  <tbody>
                    <?php foreach ($rs as $key => $value) { ?>
                      <tr>
                        <td><?php echo $value['fid']; ?></td>
                        <td><?php echo $value['fb_time'];?></td>
                        <td><?php echo $value['type_text'];?></td>
                        <td><?php echo $value['feedback'];?></td>
                        <?php if($value['status_id'] == 1){ ?>
                          <td class="fb_status ">
                            <span class="label label-danger"><?php echo $value['status_text'];?></span>
                          </td>
                        <?php }else{ ?>
                          <td class="fb_status ">
                            <span class="label label-default"><?php echo $value['status_text'];?></span>
                          </td>
                        <?php } ?>
                        
                        <!--<td><?php echo $value['worker_uid'];?></td>-->
                        <td><?php echo $value['ct_time'];?></td>
                        <td>
                          <?php if(isHave($value['upload'])){ ?>
                            <?php if(preg_match('/^.*?\.(jpg|png|gif|jpeg|bmp|jpe)$/', $value['upload'])){ ?>
                              <a href="<?php echo getImgUrl($value['upload']);?>" target="_blank">查看图片</a>
                            <?php }else{ ?>
                              <a href="<?php echo getImgUrl($value['upload']);?>" target="_blank">下载附件</a>
                            <?php } // if preg_match end?>
                          <?php } // if isHave end?>
                        </td>
                        <td>
                          <a href="<?php echo U('feedback/detail').'?fid='.$value['fid'];?>">详细</a>
                          <?php if($value['status_id'] == 1) { ?>
                          <span>|</span>
                          <a href="#" class="btn-dealfb" data-fid="<?php echo $value['fid'];?>">标记为已处理</a>
                          <? } ?>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="panel-footer">
            <div class="row">
              <div class="col-sm-12 text-right text-center-sm">
                        <?php echo page($total,$p,'',$pageShow,'p');?>
              </div>
            </div>
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
    if(!clicked){
      clicked = true;
      $.ajax({
        url: "<?php echo U('feedback/saveFeedback');?>",
        type: 'POST',
        data: $('#form-fb1').serialize(),
        dataType: 'json',
        success: function(retJson){
          clicked = false;
          if(retJson.status != 1){
            Msg.error(retJson.info);
          }else{
            Msg.alert(retJson.info);
            $('.feedbackContent').val('');
          }
        },
        error: function(){
          clicked = false;
          Msg.error('保存失败');
        }
      });
    }
    
  });

  $('.btn-dealfb').click(function(event){
    event.preventDefault();
    var t = this;
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
              $(t).parent().siblings('.fb_status').html('<span class="label label-default">已处理</span>');
              $(t).siblings('span').remove();
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

  $('.btn-group').each(function(){
    var active = $(this).find('.dropdown-menu > .active');
    if(active.length > 0){

      $(this).find('.dropdown-label').children('span').text(active.find('a:first').text());
    }    
  });
});
</script>
<?php
include getTpl('footer', 'public');
?>
