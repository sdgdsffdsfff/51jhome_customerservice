<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'servicelog-index', //页面标示
    'pagename' => '客服日报', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
    'footerjs'=>array('content/jquery.smoothConfirm','content/calendar'),
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
    <style>
    .label{font-size:13px}
    </style>
    <div class="row">
      <div class="col-sm-12">
        <div class="panel-body">
          <div class="row text-small">
            <div class="col-sm-6">
              <a href="<?php echo U('servicelog/add');?>" class="btn btn-default">添加反馈</a>
              <a href="<?php echo U('servicelog/index');?>" class="btn btn-default">查看全部</a>
              <div class="btn-group" data-resize="auto" style="margin-left: 5px;">
                <button class="btn btn-white dropdown-toggle" type="button" data-toggle="dropdown">
                  <span class="dropdown-label">
                    <span class="green">反馈类型</span>
                  </span>
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li data-value="0"><a href="<?php echo U('servicelog/index', getSearchUrl(array('fbtype'=> null)));?>">全部</a></li>
                  <?php foreach ($types as $key => $value) { ?>
                    <?php $key += 1; ?>
                    <li data-value="<?php echo $key;?>" <?php if($key == ($type_id)) echo "class=\"active\"";?>>
                      <a href="<?php echo U('servicelog/index', getSearchUrl(array('fbtype' => $key)));?>">
                        <?php echo $value['name'];?>
                      </a>
                    </li>
                  <?php } ?>
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
                  <li data-value="0"><a href="<?php echo U('servicelog/index', getSearchUrl(array('fbresult' => null)));?>">全部</a></li>
                  <?php foreach ($status as $key => $value) { ?>
                    <?php $key += 1; ?>
                    <li data-value="<?php echo $key;?>" <?php if($key == ($status_id)) echo "class=\"active\"";?>>
                      <a href="<?php echo U('servicelog/index', getSearchUrl(array('fbresult' => $key)));?>">
                        <?php echo $value; ?>
                      </a>
                    </li>
                  <?php } ?>
                </ul>
              </div><!-- end of btn-group -->

            </div><!-- end of col-sm-3 -->
            <div class="col-sm-6">
              <form action="<?php echo U('servicelog/index', getSearchUrl()); ?>" method="post" class="form-inline">
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
                      <th>编号</th>
                      <th>时间</td>
                      <th>反馈类型</th>
                      <th>反馈内容</th>
                      <th>发布者</th>
                      <th>发布时间</th>
                      <th>附件</th>
                      <th>订单号</th>
                      <th>用户名</th>
                      <th>手机号</th>
                      <th>处理结果</th>
                      <th>操作</th>
                    </tr>
                  </thead> 
                  <tbody>
                    <?php foreach ($rs as $key => $value) { ?>
                      <tr id="list_detail_<?php echo $value['fid'];?>">
                        <td><a href="<?php echo U('servicelog/detail_card', array('fid' => $value['fid']));?>" rel="pop"><?php echo $value['fid'];?></a></td>
                        <td><a href="<?php echo U('servicelog/daily', array('date'=>$value['fb_time'])) ?>" rel="pop"><?php echo $value['fb_time'];?></a></td>
                        <td><?php echo $value['type_text'];?></td>
                        <td><a href="<?php echo U('servicelog/detail_card', array('fid' => $value['fid']));?>" rel="pop"><?php echo $value['feedback'];?></a></td>
                        <td><?php echo $value['worker_name'];?></td>
                        <td><?php echo $value['ct_time'];?></td>
                        <td>
                          <?php if(isHave($value['upload'])){ ?>
                            <?php if(preg_match('/^.*?\.(jpg|png|gif|jpeg|bmp|jpe)$/', $value['upload'])){ ?>
                              <a href="<?php echo getImgUrl($value['upload']);?>" rel="pop">查看图片</a>
                            <?php }else{ ?>
                              <a href="<?php echo getImgUrl($value['upload']);?>" target="_blank">下载附件</a>
                            <?php } // if preg_match end?>
                          <?php } // if isHave end?>
                        </td>
                        <td><?php if($value['order_id']){ echo $value['order_id'];} else {?>无<?php }?></td>
                        <td><?php if($value['username']){ echo $value['username'];} else {?>无<?php }?></td>
                        <td><?php if($value['phone']){ echo $value['phone'];} else {?>无<?php }?></td>
                        <?php if($value['status_id'] == 1){ ?>
                          <td class="fb_status" id="show-status-fid-<?php echo $value['fid'];?>">
                            <span class="label label-danger"><?php echo $value['status_text'];?></span>
                          </td>
                        <?php }else{ ?>
                          <td class="fb_status" id="show-status-fid-<?php echo $value['fid'];?>">
                            <span class="label label-default"><?php echo $value['status_text'];?></span>
                          </td>
                        <?php } ?>
                        <td>
                        <div class="btn-group">
                            <button class="btn btn-white btn-xs dropdown-toggle" data-toggle="dropdown">操作<span class="caret"></span></button> 
                             <ul class="dropdown-menu" style="width:95px">
                                <li><a href="<?php echo U('servicelog/detail').'?fid='.$value['fid'];?>">详细</a></li>
                                <?php if($value['status_id'] == 1) { ?>
                                  <li><a href="#" class="btn-dealfb" data-fid="<?php echo $value['fid'];?>">状态</a></li>
                                <?php } ?>
                                <li><a href="<?php echo U('servicelog/edit', array('fid'=> $value['fid']));?>" class="btn-editfb" data-fid="<?php echo $value['fid'];?>">编辑</a></li>
                                <li><a href="javascript:;" rel="<?php echo $value['fid'];?>" class="delete">删除</a></li>
                            </ul> 
                        </div>
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
              <div class="col-sm-12 text-right text-center-sm"><?php echo page($total,$p,'',$pageShow,'p');?></div>
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
	
	$('.btn-dealfb').click(function(){
		 var t = this;
		var fid = parseInt($(this).data('fid'));
	  $(this).smoothConfirm("确认操作吗？", {
		  okVal:'已处理',
		  cancelVal:'取消',
		  ok: function() {
			  Msg.loading();
			  $.post('<?php echo U("servicelog/servicelog_status"); ?>',{"fid":fid,"act":1},function(result){
				  Msg.hide();
				 if(result && result.status == 1){
				  $("#show-status-fid-"+fid).html('<span class="label label-default">已处理</span>');
				  $(t).siblings('span').remove();
				  $(t).remove();
				}else{
				  Msg.error(result.info);
				}
			  },'json');
		  },
		  cancel: function() {}
	  });
	  $("body").click();//取消弹出层对对话框的遮蔽
	  return false;
	})

    var clicked = false;
  //保存反馈的按钮的监听事件
  $('#btn-savefb').click(function(event){
    event.preventDefault();
    if(!clicked){
      clicked = true;
	  Msg.loading();
      $.ajax({
        url: "<?php echo U('servicelog/saveservicelog');?>",
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
$('.delete').click(function(){
		var id=$(this).attr('rel');
	  $(this).smoothConfirm("确认删除该信息吗？", {
		  ok: function() {
			  Msg.loading();
			  $.post('<?php echo U('servicelog/delete');?>',{"id":id},function(result){
				  Msg.hide();
				 if (result.status==1){
					$("#list_detail_"+id).fadeOut();  
			     }else{
					 Msg.error(result.info);
				}
			  },'json');
		  },
		  cancel: function() {
			  return false;
		  }
	  });
	  $("body").click();//取消弹出层对对话框的遮蔽
	  return false;
	})
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
