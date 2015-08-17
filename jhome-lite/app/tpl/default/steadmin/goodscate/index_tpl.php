<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'goodscate-index', //页面标示
    'pagename' => '商品分类管理', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	//'footerjs'=>array('content/highcharts', 'content/exporting',),
    'footerjs'=>array('content/fuelux/fuelux','content/combodate/moment.min','content/jquery.smoothConfirm'),
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
     <section class="main padder">
        <div class="clearfix"> 
          <h4><i class="fa fa-table"></i>分类管理</h4> 
        </div>
        <div class="row">
      
      <!-- table 11 -->
      <div class="col-lg-12"> 
        <section class="panel"> 
          <header class="panel-heading">
          商品分类
          </header> 
          <div class="panel-body"> 
            <div class="row text-small"> 
              <div class="col-sm-8 m-b-mini">
                <a href="<?php echo U('goodscate/add'); ?>" class="btn btn-sm btn-white">添加</a>
                <a href="<?php echo U('goodscate/index'); ?>" class="btn btn-sm btn-white">列表</a>
                <a href="<?php echo U('goodscate/index',array('pid'=>1));?>"  class="btn btn-sm btn-white">顶级分类</a>
              </div>

              <!--  -->
               <div class="panel-body"> 
                <div class="row text-small"> 
                <form action="<?php echo U('goodscate/index'); ?>" method="get">
                
                 <div class="col-sm-4">
                  <div class="input-group"> 
                   <input type="text" class="input-sm form-control" placeholder="Search" name="keyword" id="keyword" value="<?php echo $keyword; ?>"> 
                   <span class="input-group-btn"> <button class="btn btn-sm btn-white" type="submit">搜索</button> </span> 
                  </div> 
                 </div> 
                 </form>
                </div> 
               </div> 
              <!--  -->
            </div> 
          </div>
          <style>.table th,.table td,.table input{text-align:center;} .show-and-hide{ font-size:13px; cursor:pointer;}</style>
          <div class="table-responsive" id="show-menu-list"> 
            <table class="table table-striped b-t text-small"> 
              <thead> 
                <tr>
                  <th>ID</th> 
                  <th>分类名称</th> 
                  <th>父分类</th> 
                  <th>是否显示</th>
                  <th>深度</th>
                  <th>排序</th>
                  <th width="220">操作</th> 
                </tr> 
              </thead> 
              <tbody>
              <?php 
			  if ($list){
			  foreach ($list as $key => $value) { 
			  	if ($value['depth']>3){
					$show=0;
				}else{
					$show=1;
				}
				if ($value['depth']==4){
					$color='color:#CD2626';
				}else{
					$color='';
				}
			  ?>
                <tr class="list_detail_<?php echo $value['pid']; ?>" show="<?php echo $show;?>" <?php if(!$show){echo 'style="display:none"';}?> > 
                  <td><?php echo $value['id']; ?></td> 
                  <td align="left" style="text-align:left; font-size:16px"><a href="<?php echo U('goodscate/index',array('pid'=>$value['id']));?>"><?php echo str_pad('|-',($value['count']-1)*5,'------');?>
				  <span style="<?php echo $color;?>"><?php echo $value['name']; ?></span>
                  </a>
                  <?php
                  if ($value['depth']>2){
				  ?>
                  <span class="show-and-hide" rel="<?php echo $value['id']; ?>">[展开↓]</span>
                  <?php
				  }
				  ?>
                  </td> 
                  <td>
                  <?php
                  if (!$value['pid']){
				  ?>
                  <a href="<?php echo U('goodscate/index',array('pid'=>1));?>">顶级分类</a>
                  <?php }else{?>
                  <a href="<?php echo U('goodscate/index',array('pid'=>$value['pid']));?>"><?php echo $group[$value['pid']]['name'];?></a>
                  <?php }?>
                  </td> 
                  <td>
                  <?php echo $value['is_show'] == 1?'显示':'隐藏';?>
                  </td>
                  <td><?php echo $value['depth']; ?></td> 
                  <td><?php echo $value['sort']; ?></td> 
                  <td>
                    <div class="btn-group"> 
                      <button class="btn btn-white btn-xs dropdown-toggle" data-toggle="dropdown">操作<span class="caret"></span></button> 
                      <ul class="dropdown-menu">
                     	  <li><a href="<?php echo U('goods/index',array('cate_id'=>$value['id']));?>">上架商品</a></li>
                      	  <li><a href="<?php echo U('goodscate/index',array('pid'=>$value['id']));?>">下级栏目</a></li>
                          <li><a href="<?php echo U('shop/report',array('cid'=>$value['id']));?>">查看预定信息</a></li>
                          <li><a href="<?php echo U('goodscate/edit',array('id'=>$value['id']));?>">编辑</a></li>
                          <li><a href="javascript:;" class="delete" rel="<?php echo $value['id'];?>">删除</a></li>
                      </ul>  
                    </div>
                  </td> 
                </tr>
              <?php
			   } 
			  }else{?>
              <tr>
                  <td colspan="6" align="center">没有记录</td>
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
                <?php echo page($total,$page,'',$pageShow,'p');?>  
              </div> 
            </div> 
          </footer> 
        </section> 
      </div>
        </div> 
    </section>
  </section> 
  <!--/ content --> 
<!--主体 结束-->
<script>
$(function(){
	$(".show-and-hide").click(function(){
		var pid=$(this).attr('rel');
		var show=$(".list_detail_"+pid).attr('show');
		if (show=='1'){
			$(".list_detail_"+pid).attr('show',0);
			$(".list_detail_"+pid).hide();
			$(this).text('[展开↓]');
		 }else{
			 $(".list_detail_"+pid).attr('show',1);
			$(".list_detail_"+pid).show();
			$(this).text('[收起↑]');
		}
	})
$('.delete').click(function(){
	alert('删除类别将对应删除该类别下所有子类及商品！！');
	  var id=$(this).attr('rel');
	  $(this).smoothConfirm("确认删除该类别吗？", {
		  ok: function() {
			  Msg.loading();
			  $.post('<?php echo U('goodscate/delete');?>',{"id":id},function(result){
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
})
</script>
<?php
include getTpl('footer', 'public');
?>