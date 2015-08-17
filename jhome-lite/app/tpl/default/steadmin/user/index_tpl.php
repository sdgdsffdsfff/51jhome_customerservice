<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'user-index', //页面标示
    'pagename' => '帐号列表', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array('content/jquery.smoothConfirm'),
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
<!-- content -->
  <section id="content">
    <!-- main padder --> 
    <section class="main padder">
        <div class="clearfix"> 
			<h4><i class="fa fa-table"></i><?php echo trim($Document['pagename'],'_');?></h4> 
        </div>
        <div class="row">
			<!-- table 11 -->
			<div class="col-lg-12"> 
            <!---->
          <div class="panel-body">
            <div class="row text-small">
              <div class="col-sm-8">
              <a class="btn btn-default" href="<?php echo U('user/add');?>">添加帐号</a>
               <div data-resize="auto" class="select btn-group" style="margin-left:10px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">
                  <?php echo $group_id&&isset($group[($group_id-1)])?$group[($group_id-1)]:'帐号类型';?></span> <span class="caret"></span></button> 
                <ul class="dropdown-menu">
                <li data-value="0" <?php if(!$group_id){echo 'class="active"';}?>><a href="<?php echo U('user/index');?>">全部</a></li>
                <?php
                foreach($group as $key=>$val){
                ?>
                 <li data-value="<?php echo ($key+1);?>" <?php if($group_id==($key+1)){echo 'class="active"';}?>><a href="<?php echo U('user/index',array('group'=>($key+1)));?>"><?php echo $val;?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
              </div>
              <div class="col-sm-4">
                <form action="<?php echo U(ACTION_NAME.'/index');?>" method="get" name="form1" target="_self">
                  <div class="input-group">
                    <input type="text" name="q" class="input-sm form-control" placeholder="请输入帐号名关键字..." />
                    <input name="g" type="hidden" value="<?php echo GROUP_NAME;?>" />
                    <input name="c" type="hidden" value="<?php echo ACTION_NAME;?>" />
                    <input name="m" type="hidden" value="<?php echo MODEL_NAME;?>" />
                    <span class="input-group-btn">
                    <button class="btn btn-sm btn-white" type="submit">搜索</button>
                    </span> </div>
                </form>
              </div>
            </div>
          </div>
          <!---->
				<section class="panel"> 
					<div class="table-responsive"> 
						<table class="table table-striped b-t text-small"> 
							<thead>
                              <tr>
                                <th align="center">ID</th>
                                <th align="center">姓名</th>
                                <th align="center">帐号</th>
                                <th align="center">服务中心</th>
                                <th align="center">手机</th>
                                <th align="center">登陆次数</th>
                                <th align="center">最后登陆时间</th>
                                <th align="center">登陆IP</th>
                                <th align="center">注册时间</th>
                                <th align="center">有效截止期</th>
                                <th align="center">组别</th>
                                <th align="center">帐号状态</th>
                                <th align="center">工作状态</th>
                                <th align="center" width="120">操作</th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($rs){
                                foreach($rs as $val){
                            ?>
                              <tr id="list_detail_<?php echo $val['user_id'];?>">
                                <td><?php echo $val['user_id'];?></td>
                                <td><?php echo $val['real_name'];?></td>
                                <td><?php echo $val['username'];?></td>
                                <td><?php echo $val['areaName'];?></td>
                                <td><?php echo $val['phone'];?></td>
                                <td><?php echo $val['logincount'];?></td>
                                <td><?php echo ($val['logintime']?outTime($val['logintime']):'');?></td>
                                <td><?php echo long2ip($val['loginip']);?></td>
                                <td><?php echo ($val['regdateline']?outTime($val['regdateline']):'');?></td>
                                <td><?php echo ($val['effective']?outTime($val['effective'],2):'长期');?></td>
                                <td><?php echo $group[$val['groupid']];?></td>
                                <td><?php echo $status[$val['status']];?></td>
                                <td id="work-status-<?php echo $val['user_id'];?>"><?php echo $setting['work_status'][$val['work_status']];?></td>
                                <td>
                                <div class="btn-group"> 
                                    <button class="btn btn-white btn-xs dropdown-toggle" data-toggle="dropdown">操作<span class="caret"></span></button> 
                                     <ul class="dropdown-menu" style="width:95px">
                                       <li class="dropdown-submenu pull-left"><a href="#" tabindex="-1">状态设置</a> 
                                             <ul aria-labelledby="dropdownMenu" role="menu" class="dropdown-menu" style="margin-left:25px">
                                             <?php
                                             foreach($setting['work_status'] as $k=>$v){
                                             ?>
                                              <li><a href="javascript:;" class="setting-status" act="<?php echo $k;?>" rel="<?php echo $val['user_id'];?>" tabindex="-1"><?php echo $v;?></a></li> 
                                                <?php
                                             }
                                                ?>
                                             </ul>
                                         </li>
                     					<li class="divider"></li>
                                        <li><a href="<?php echo U('user/edit',array('id'=>$val['user_id']));?>">编辑</a></li> 
                                        <li><a href="javascript:;" rel="<?php echo $val['user_id'];?>" class="delete">删除</a></li>
                                    </ul> 
								</div>
                                </td>
                              </tr>
                              <?php
                                }
                            }else{
                              ?>
                             <tr>
                                <td colspan="8" align="center">暂无数据</td>
                              </tr>
                            <?php
                            }
                            ?>
                            </tbody> 
						</table> 
					</div> 
         			<footer class="panel-footer">
                    <div class="row">
                      <div class="col-sm-12 text-right text-center-sm">
                        <?php echo page($total,$p,'',20,'p');?></div>
                    </div>
                  </footer>
				</section> 
			</div> 
			<!--/ table 11 -->

        </div> 
    </section>
    <!--/ main padder -->  
  </section> 
  <!--/ content --> 
<!--主体 结束-->
<script>
$(function(){
	$('.delete').click(function(){
		var id=$(this).attr('rel');
	  $(this).smoothConfirm("确认删除该帐户吗？", {
		  ok: function() {
			  Msg.loading();
			  $.post('<?php echo U('user/delete');?>',{"id":id},function(result){
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
	});
	$(".setting-status").click(function(){
		var id=$(this).attr('rel'),act=$(this).attr('act');
	  	Msg.loading();
		$.post('<?php echo U('user/deal');?>',{"id":id,"act":act},function(result){
			Msg.hide();
			if (result.status==1){
				$("#work-status-"+id).html(result.data);
			}else{
				Msg.error(result.info);
			}
		},'json');
	  $("body").click();
	  return false;
	})
})
</script>
<?php
include getTpl('footer', 'public');
?>