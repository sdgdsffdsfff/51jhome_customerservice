<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'shop-index', //页面标示
    'pagename' => '店铺列表', //当前页面名称
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
<style>
.show-pop-content {
	height:200px;
	overflow-y:scroll;
	overflow-x:hidden;
	table-layout: fixed;
	word-wrap:break-word;
	word-break:break-all;
}
</style>
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
              <a class="btn btn-default" href="<?php echo U('shop/add');?>">添加店铺</a>
               <div data-resize="auto" class="select btn-group" style="margin-left:10px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">城区</span> <span class="caret"></span></button> 
                <ul class="dropdown-menu">
                <li data-value="0" <?php if(!$area_id){echo 'class="active"';}?>><a href="<?php echo U('shop/index');?>">全部</a></li>
                <?php
                foreach($area as $val){
                ?>
                 <li data-value="<?php echo $val['aid'];?>" <?php if($area_id==$val['aid']){echo 'class="active"';}?>><a href="<?php echo U('shop/index',array('area_id'=>$val['aid']));?>"><?php echo $val['name'];?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
                <div data-resize="auto" class="select btn-group" style="margin-left:10px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">服务中心</span> <span class="caret"></span></button> 
                <ul class="dropdown-menu show-pop-content">
                <li data-value="0" <?php if(!$service_id){echo 'class="active"';}?>><a href="<?php echo U('shop/index');?>">全部</a></li>
                <?php
                foreach($service as $val){
                ?>
                 <li data-value="<?php echo $val['sid'];?>" <?php if($service_id==$val['sid']){echo 'class="active"';}?>><a href="<?php echo U('shop/index',array('service_id'=>$val['sid']));?>"><?php echo $val['stitle'];?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
                <div data-resize="auto" class="select btn-group" style="margin-left:10px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">店铺类型</span> <span class="caret"></span></button> 
                <ul class="dropdown-menu">
                <li data-value="0" <?php if(!$shop_type){echo 'class="active"';}?>><a href="<?php echo U('shop/index');?>">全部</a></li>
                <?php
                foreach($setting['shop_type'] as $key=>$val){
                ?>
                 <li data-value="<?php echo ($key+1);?>" <?php if($shop_type==($key+1)){echo 'class="active"';}?>><a href="<?php echo U('shop/index',array('shop_type'=>($key+1)));?>"><?php echo $val;?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
                <div data-resize="auto" class="select btn-group" style="margin-left:10px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">参与活动</span> <span class="caret"></span></button> 
                <ul class="dropdown-menu">
                <li data-value="0" <?php if(!$tips_id){echo 'class="active"';}?>><a href="<?php echo U('shop/index');?>">全部</a></li>
                <?php
                foreach($setting['shop_tips'] as $key=>$val){
                ?>
                 <li data-value="<?php echo ($key+1);?>" <?php if($tips_id==($key+1)){echo 'class="active"';}?>><a href="<?php echo U('shop/index',array('tips'=>($key+1)));?>"><?php echo $val;?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
                <div data-resize="auto" class="select btn-group" style="margin-left:10px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">店铺状态</span> <span class="caret"></span></button> 
                <ul class="dropdown-menu">
                <li data-value="0" <?php if(!$status){echo 'class="active"';}?>><a href="<?php echo U('shop/index');?>">全部</a></li>
                <?php
                foreach($setting['shop_status'] as $key=>$val){
                ?>
                 <li data-value="<?php echo ($key+1);?>" <?php if($status==($key+1)){echo 'class="active"';}?>><a href="<?php echo U('shop/index',array('status'=>($key+1)));?>"><?php echo $val;?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
              </div>
              <div class="col-sm-4">
                <form action="<?php echo U(ACTION_NAME.'/index');?>" method="get" name="form1" target="_self">
                  <div class="input-group">
                    <input type="text" name="q" class="input-sm form-control" placeholder="请输入店铺关键字..." />
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
                                <th align="center">店铺名</th>
                                <th align="center">参与活动</th>
                                <th align="center">创建者</th>
                                <th align="center">类型</th>
                                <th align="center">城区</th>
                                <th align="center">服务中心</th>
                                <th align="center">营业时间</th>
                                <th align="center">电话</th>
                                <th align="center">结算方式</th>
                                <th align="center">延时配送</th>
                                <th align="center">状态</th>
                                <th align="center" width="120">操作</th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($rs){
                                foreach($rs as $val){
                            ?>
                              <tr id="list_detail_<?php echo $val['shop_id'];?>">
                                <td><?php echo $val['shop_id'];?></td>
                                <td><a href="<?php echo U('shop/detail',array('id'=>$val['shop_id']));?>" rel="pop"><?php echo $val['shop_name'].($val['shop_alt_name']?'['.$val['shop_alt_name'].']':'');?></a></td>
                                <td><?php echo $val['tipsList'];?></td>
                                <td><?php echo $val['userName'];?></td>
                                <td><?php echo $setting['shop_type'][$val['shop_type']];?></td>
                                <td><?php echo $val['areaName'];?></td>
                                <td><?php echo $val['serviceName'];?></td>
                                <td><?php echo $val['stime'].'-'.$val['etime'];?></td>
                                <td><?php echo $val['tel'];?></td>
                                <td><?php echo $adminData['settlement'][$val['settlement']];?></td>
                                <td><?php echo $setting['status'][$val['is_delay']];?></td>
                                <td id="shop-status-<?php echo $val['shop_id'];?>"><?php echo $setting['shop_status'][$val['status']];?></td>
                                <td>
                                <div class="btn-group"> 
                                    <button class="btn btn-white btn-xs dropdown-toggle" data-toggle="dropdown">操作<span class="caret"></span></button> 
                                     <ul class="dropdown-menu" style="width:95px">
                                       <li class="dropdown-submenu pull-left"><a href="#" tabindex="-1">状态设置</a> 
                                             <ul aria-labelledby="dropdownMenu" role="menu" class="dropdown-menu" style="margin-left:25px">
                                             <?php
                                             foreach($setting['shop_status'] as $k=>$v){
                                             ?>
                                              <li><a href="javascript:;" class="setting-status" act="<?php echo $k;?>" rel="<?php echo $val['shop_id'];?>" tabindex="-1"><?php echo $v;?></a></li> 
                                                <?php
                                             }
                                                ?>
                                             </ul>
                                         </li>
                                         <li><a href="<?php echo U('goods/index',array('shop_id'=>$val['shop_id']));?>">在售商品</a></li>
                                         <li><a href="<?php echo U('goods/goodscopy',array('shop_id'=>$val['shop_id']));?>" rel="pop">复制商品</a></li>
                                         <li><a href="javascript:;" rel="<?php echo $val['shop_id'];?>" class="do-act" act="up">批量上架</a></li>
                                         <li><a href="javascript:;" rel="<?php echo $val['shop_id'];?>" class="do-act" act="down">批量下架</a></li>
                                         <li class="divider"></li>
                                         <li><a href="<?php echo U('print/index',array('shop_id'=>$val['shop_id']));?>">店铺打印机</a></li>
                                         <li><a href="<?php echo U('goods/out',array('shop_id'=>$val['shop_id']));?>">导出商品</a></li>
                     					<li class="divider"></li>
                                        <li><a href="<?php echo U('shop/edit',array('id'=>$val['shop_id']));?>">编辑</a></li>
                                        <li><a href="javascript:;" rel="<?php echo $val['shop_id'];?>" class="delete">删除</a></li>
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
	  $(this).smoothConfirm("确认删除该店铺吗？", {
		  ok: function() {
			  Msg.loading();
			  $.post('<?php echo U('shop/delete');?>',{"id":id},function(result){
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
	$('.do-act').click(function(){
		var id=$(this).attr('rel');
		var act=$(this).attr('act');
	  $(this).smoothConfirm("确认操作吗？", {
		  ok: function() {
			  Msg.loading();
			  $.post('<?php echo U('shop/deal');?>',{"id":id,"action":act},function(result){
				  Msg.hide();
				  if (result.status==1){
					  Msg.ok('操作成功');
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
		$.post('<?php echo U('shop/deal');?>',{"id":id,"act":act},function(result){
			Msg.hide();
			if (result.status==1){
				$("#shop-status-"+id).html(result.data);
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