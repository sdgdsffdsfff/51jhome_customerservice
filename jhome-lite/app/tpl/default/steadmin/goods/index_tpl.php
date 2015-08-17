<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'goods-index', //页面标示
    'pagename' => '商品列表', //当前页面名称
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
<!-- content -->
<section id="content">
  <!-- main padder -->
  <section class="main padder">
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_PATH; ?>jquery-ui/css/smoothness/jquery-ui-1.10.0.custom.min.css"/>
    <script type="text/javascript" src="<?php echo PUBLIC_PATH; ?>jquery-ui/js/jquery-ui-1.10.0.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo PUBLIC_PATH; ?>jquery-ui/js/jquery-ui-timepicker-addon.js"></script>
    
    <div class="clearfix">
      <h4><i class="fa fa-table"></i><?php echo trim($Document['pagename'],'_');?></h4>
    </div>
    <div class="row"> 
      <!-- table 11 -->
      <div class="col-lg-12"> 
        <!---->
        <div class="panel-body">
          <div class="row text-small">
            <div class="col-sm-6"> <a class="btn btn-default" href="<?php echo U('goods/add');?>">添加商品</a> <a class="btn btn-default" href="<?php echo U('goods/index');?>">全部商品</a>
              <div data-resize="auto" class="select btn-group" style="margin-left:5px">
                <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label"> <?php echo $act&&isset($actlist[$act])?$actlist[$act]:'属性类型';?></span> <span class="caret"></span></button>
                <ul class="dropdown-menu">
                  <li data-value="0" <?php if(!$act){echo 'class="active"';}?>><a href="<?php echo U('goods/index',getSearchUrl(array('act'=>null)));?>">全部</a></li>
                  <?php
                foreach($actlist as $key=>$val){
                ?>
                  <li data-value="<?php echo $key;?>" <?php if($act==$key){echo 'class="active"';}?>><a href="<?php echo U('goods/index',getSearchUrl(array('act'=>$key)));?>"><?php echo $val;?></a></li>
                  <?php
                }
                ?>
                </ul>
              </div>
              <div data-resize="auto" class="select btn-group" style="margin-left:5px">
                <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label"> <?php echo $tips_id&&isset($setting['goods_tips'][$tips_id])?$setting['goods_tips'][$tips_id]:'活动类型';?></span> <span class="caret"></span></button>
                <ul class="dropdown-menu">
                  <li data-value="0" <?php if(!$tips_id){echo 'class="active"';}?>><a href="<?php echo U('goods/index',getSearchUrl(array('tips'=>null)));?>">全部</a></li>
                  <?php
                foreach($setting['goods_tips'] as $key=>$val){
                ?>
                  <li data-value="<?php echo $key;?>" <?php if($tips_id==$key){echo 'class="active"';}?>><a href="<?php echo U('goods/index',getSearchUrl(array('tips'=>$key)));?>"><?php echo $val;?></a></li>
                  <?php
                }
                ?>
                </ul>
              </div>
              <div data-resize="auto" class="select btn-group" style="margin-left:5px">
                <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label"> <?php echo $orderby?$orderType[$orderby]:'排序';?></span> <span class="caret"></span></button>
                <ul class="dropdown-menu">
                  <?php
                foreach($orderType as $key=>$val){
                ?>
                  <li data-value="<?php echo $key;?>" <?php if($orderby==$key){echo 'class="active"';}?>><a href="<?php echo U('goods/index',getSearchUrl(array('orderby'=>$key)));?>"><?php echo $val;?></a></li>
                  <?php
                }
                ?>
                </ul>
              </div>
              <div data-resize="auto" class="select btn-group" style="margin-left:5px">
                <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label"> <?php echo $status&&isset($setting['goods_status'][($status-1)])?$setting['goods_status'][($status-1)]:'商品状态';?></span> <span class="caret"></span></button>
                <ul class="dropdown-menu">
                  <li data-value="0" <?php if(!$status){echo 'class="active"';}?>><a href="<?php echo U('goods/index',getSearchUrl(array('status'=>null)));?>">全部</a></li>
                  <?php
                foreach($setting['goods_status'] as $key=>$val){
                ?>
                  <li data-value="<?php echo ($key+1);?>" <?php if($status==($key+1)){echo 'class="active"';}?>><a href="<?php echo U('goods/index',getSearchUrl(array('status'=>($key+1))));?>"><?php echo $val;?></a></li>
                  <?php
                }
                ?>
                </ul>
              </div>
              <div data-resize="auto" class="select btn-group" style="margin-left:5px">
                <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label"> <?php echo $pageShow&&isset($pageShowList[$pageShow])?$pageShowList[$pageShow].'条/页':'每页数量';?></span> <span class="caret"></span></button>
                <ul class="dropdown-menu">
                  <li data-value="0" <?php if(!$pageShow){echo 'class="active"';}?>><a href="<?php echo U('goods/index',getSearchUrl(array('pageshow'=>20,'p'=>1)));?>">默认</a></li>
                  <?php
                foreach($pageShowList as $key=>$val){
                ?>
                  <li data-value="<?php echo $key;?>" <?php if($pageShow==$key){echo 'class="active"';}?>><a href="<?php echo U('goods/index',getSearchUrl(array('pageshow'=>$key,'p'=>1)));?>"><?php echo $val;?>条/页</a></li>
                  <?php
                }
                ?>
                </ul>
              </div>
            </div>
            <div class="col-sm-6">
              <form action="<?php echo U(ACTION_NAME.'/index');?>" method="get" name="form1" target="_self">
                <div class="col-sm-7 m-b-mini">
                  <div class="form-group">
                    <div class="col-sm-6">
                      <input type="text" readonly="" onclick="new Calendar().show(this);" value="<?php echo $stime;?>" class="form-control" placeholder="开始时间" name="stime">
                    </div>
                    <div class="col-sm-6">
                      <input type="text" readonly="" onclick="new Calendar().show(this);" value="<?php echo $etime;?>" class="form-control" placeholder="结束时间" name="etime">
                    </div>
                  </div>
                </div>
                <div class="col-sm-5 input-group">
                  <div class="input-group-btn">
                    <button data-toggle="dropdown" class="btn btn-white btn-sm dropdown-toggle"> <span class="dropdown-label">商品名称</span><span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-select pull-right">
                      <li class="active"><a href="#">
                        <input type="radio" checked="checked" name="st" value="name">
                        商品名称</a></li>
                      <li class=""><a href="#">
                        <input type="radio" name="st" value="sn">
                        商品货号</a></li>
                      <li class=""><a href="#">
                        <input type="radio" name="st" value="subtitle">
                        副名称</a></li>
                      <li class=""><a href="#">
                        <input type="radio" name="st" value="bar">
                        商品条形码</a></li>
                      <li class=""><a href="#">
                        <input type="radio" name="st" value="number">
                        商家货号</a></li>
                    </ul>
                  </div>
                  <input type="text" name="q" class="input-sm form-control" placeholder="请输入商品关键字..." />
                  <input name="g" type="hidden" value="<?php echo GROUP_NAME;?>" />
                  <input name="g" type="hidden" value="<?php echo GROUP_NAME;?>" />
                  <input name="c" type="hidden" value="<?php echo ACTION_NAME;?>" />
                  <input name="status" type="hidden" value="3" />
                  <input name="p" type="hidden" value="1" />
                  <span class="input-group-btn">
                  <button class="btn btn-sm btn-white" type="submit">搜索</button>
                  </span> </div>
              </form>
            </div>
          </div>
        </div>
        <!---->
        <form class="form-horizontal" method="post" id="bacthForm">
          <section class="panel">
            <div class="table-responsive">
              <table class="table table-striped b-t text-small">
                <thead>
                  <tr>
                    <th align="center"><input type="checkbox" name=""/></th>
                    <th align="center">ID</th>
                    <th align="center">货号</th>
                    <th align="center">名称</th>
                    <th align="center">店铺</th>
                    <th align="center">类别</th>
                    <th align="center">规格</th>
                    <th align="center">价格</th>
                    <th align="center">销售</th>
                    <th align="center">库存</th>
                    <th align="center">浏览</th>
                    <th align="center">配送时间</th>
                    <th align="center">排序值</th>
                    <th align="center">销售状态</th>
                    <th align="center">最后更新</th>
                    <th align="center" width="100">操作</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                            if ($rs){
                                foreach($rs as $val){
                            ?>
                  <tr id="list_detail_<?php echo $val['gid'];?>">
                    <td><input name="gid[]" type="checkbox" value="<?php echo $val['gid'];?>" /></td>
                    <td><?php echo $val['gid'];?></td>
                    <td><?php echo $val['goods_sn'];?></td>
                    <td><a href="<?php echo U('goods/detail',array('id'=>$val['gid']));?>" rel="pop"><?php echo $val['goods_name'];?>
                      <?php if($val['is_realtime']){echo '[<span class="red">预</span>]';}?>
                      </a></td>
                    <td><a href="<?php echo U('goods/index',getSearchUrl(array('shop_id'=>$val['shop_id'],'p'=>1)));?>"><?php echo $val['shopName'];?></a></td>
                    <td><?php if($val['cateData']){echo implode('->',$val['cateData']),'->';}?>
                      <a href="<?php echo U('goods/index',getSearchUrl(array('cate_id'=>$val['cate_id'],'p'=>1)));?>"><?php echo $val['cateName'];?></a></td>
                    <td><?php echo $val['goods_spec'];?></td>
                    <td><?php echo $val['price'];?></td>
                    <td><?php echo $val['sale_counts'];?></td>
                    <td><?php echo $val['storage_counts']>$minStorage?$val['storage_counts']:'<span class="red">'.$val['storage_counts'].'</span>';?></td>
                    <td><?php echo $val['hits_counts'];?></td>
                    <td><?php echo $setting['expTime'][$val['booked_time']]['name'];?></td>
                    <td><?php echo $val['px'];?></td>
                    <td id="goods-status-<?php echo $val['gid'];?>"><?php echo $val['saleStatus'];?></td>
                    <td><?php echo outTime($val['refresh_time']);?></td>
                    <td><div class="btn-group">
                        <button class="btn btn-white btn-xs dropdown-toggle" data-toggle="dropdown">操作<span class="caret"></span></button>
                        <ul class="dropdown-menu" style="width:95px">
                          <li><a href="<?php echo U('goods/detail',array('id'=>$val['gid']));?>" rel="pop">详细</a></li>
                          <li class="divider"></li>
                          <li class="dropdown-submenu pull-left"><a href="#" tabindex="-1">状态</a>
                            <ul aria-labelledby="dropdownMenu" role="menu" class="dropdown-menu" style="margin-left:5px">
                              <?php
                                             foreach($setting['goods_status'] as $k=>$v){
                                             ?>
                              <li><a href="javascript:;" class="setting-status" act="<?php echo $k;?>" rel="<?php echo $val['gid'];?>" tabindex="-1"><?php echo $v;?></a></li>
                              <?php
                                             }
                                                ?>
                            </ul>
                          </li>
                          <li><a href="<?php echo U('goods/edit',array('id'=>$val['gid']));?>">编辑</a></li>
                          <li><a href="javascript:;" rel="<?php echo $val['gid'];?>" class="delete">删除</a></li>
                        </ul>
                      </div></td>
                  </tr>
                  <?php
                                }
                            }else{
                              ?>
                  <tr>
                    <td colspan="20" align="center">暂无数据</td>
                  </tr>
                  <?php
                            }
                            ?>
                </tbody>
              </table>
            </div>
            <footer class="panel-footer">
              <div class="row" style="margin-left:10px"><a id="batch-action" class="btn btn-default" href="javascript:;">批量处理</a></div>
              <div id="show-batch-list" class="row" style="margin-top:20px; display:none">
                <div class="alert alert-warning alert-block" style="font-size:14px; margin:0 20px 20px 20px">
                  <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
                  <h4><i class="fa fa-bell-o"></i>操作提示</h4>
                  <p>不修改的选项请保持为空</p>
                </div>
                <div class="form-group"> 
                    <label class="col-sm-1 control-label">商品类别</label> 
                    <div class="col-sm-2"> 
                      <select name="select-cateid" id="select-cateid" class="form-control">
                      <option value="0">选择类别</option>
                      <?php
                      foreach($cate as $val){
                      ?>
                       <option value="<?php echo $val['id']?>"><?php echo $val['name'];?></option>
                      <?php
                      }
                      ?>
                      </select>
                    </div>
                    <div class="col-sm-2"> 
                      <select name="cate_id" id="cate_id" class="form-control">
                      <option value="0">请先选择大类</option>
                      </select>
                    </div>
                    <div class="col-sm-3" id="show-menu" style="line-height:30px"></div>
                 </div>
                <div class="form-group">
                  <label class="col-sm-1 control-label">原价</label>
                  <div class="col-sm-1" style=" margin-right:20px">
                    <input type="text" name="original_price" id="original_price" style="width:120px" placeholder="优惠前的价格" class="form-control" value="" />
                  </div>
                  <div class="col-sm-1" style="line-height:35px">元</div>
                </div>
                <div class="form-group">
                  <label class="col-sm-1 control-label">销售价格</label>
                  <div class="col-sm-1" style="margin-right:20px">
                    <input type="text" name="price_pre" id="price_pre" style="width:120px" placeholder="选填，如：尝鲜价" class="form-control" value="" />
                  </div>
                  <div class="col-sm-1" style="margin-right:35px">
                    <input type="text" name="price" id="price" placeholder="必填" style="width:130px" class="form-control" value="" />
                  </div>
                  <div class="col-sm-1" style="line-height:35px">元</div>
                </div>
                <div class="form-group">
                  <label class="col-sm-1 control-label">商品库存</label>
                  <div class="col-sm-1" style="margin-right:20px">
                    <input type="text" name="storage_counts" id="storage_counts" style="width:120px" placeholder="必填" class="form-control" value="" />
                    <span class="help-block"></span></div>
                  <div class="col-sm-1" style="line-height:35px">件</div>
                </div>
                <div class="form-group">
                <label class="col-sm-1 control-label">前台展示销量</label>
                <div class="col-sm-1" style="margin-right:20px">
                  <input type="text" name="order_counts" id="order_counts" style="width:120px" placeholder="" class="form-control" value="" />
                  </div>
                <div class="col-sm-3" style="line-height:35px">件，前台展示量是此值的3倍</div>
              </div>
                <div class="form-group">
                  <label class="col-sm-1 control-label">排序</label>
                  <div class="col-sm-1" style=" margin-right:20px">
                    <input type="text" name="px" id="px" style="width:100px" placeholder="商品排序值" class="form-control" value="" />
                  </div>
                  <div class="col-sm-1" style="line-height:35px"></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-1 control-label">上架时间</label>
                  <div class="col-sm-3">
                    <input style="width:225px" type="text" name="start_times" id="start_times" placeholder="必填" class="form-control" value="" />
                    <span class="help-block"></span></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-1 control-label">下架时间</label>
                  <div class="col-sm-3">
                    <input style="width:225px" type="text" name="end_times" id="end_times" placeholder="必填" class="form-control" value="" />
                    <span class="help-block"></span></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-9 col-lg-offset-1">
                    <div class="col-sm-1"><!--data-loading-text="正在提交..." -->
                      <button type="submit" id="sub-ok" class="btn btn-primary">保存</button>
                    </div>
                    <div class="col-md-offset-2">
                      <button id="hide-batch-list" type="button" class="btn btn-white">取消</button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12 text-right text-center-sm"> <?php echo page($total,$p,'',20,'p');?></div>
              </div>
            </footer>
          </section>
        </form>
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
	$('#start_times').datetimepicker();
    $('#end_times').datetimepicker();
	$("#cate_id").change(function(){
		selectCate();	
	})
	function selectCate(){
		var menu=$.trim($("#cate_id").find("option:selected").text().replace('&emsp;',''));
		$("#show-menu").html('当前类别：<strong class="red">'+menu+'</strong>');	
	}
	$("#select-cateid").change(function(){
		var id=$("#select-cateid option:selected").val();
		if (id=='0'){
			$("#cate_id").html('<option value="0">请先选择大类</option>');
			$("#show-menu").html('');	
			return false;
		}
		Msg.loading();
		$.getJSON('<?php echo U('goods/cate');?>',{"cid":id,"cateid":0},function(res){
			Msg.hide();
			if (res.status==1){
				$("#cate_id").html(res.data);
				selectCate();
			}else{
				Msg.error(res.info);
			}	
		})
	})
	$('.delete').click(function(){
		var id=$(this).attr('rel');
	  $(this).smoothConfirm("确认删除该商品吗？", {
		  ok: function() {
			  Msg.loading();
			  $.post('<?php echo U('goods/delete');?>',{"id":id},function(result){
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
		$.post('<?php echo U('goods/deal');?>',{"id":id,"act":act},function(result){
			Msg.hide();
			if (result.status==1){
				$("#goods-status-"+id).html(result.data);
			}else{
				Msg.error(result.info);
			}
		},'json');
	  $("body").click();
	  return false;
	})
	$("#batch-action").click(function(){
		var gids =[];    
		  $('input[name="gid[]"]:checked').each(function(){    
		   gids.push($(this).val());    
		  });  
		console.log(gids);
		if (gids.length == 0){
			Msg.error('请先选择需要操作的商品');
			return false;
		}
		$("#show-batch-list").show();
		return true;
	});
	$("#bacthForm").submit(function(){
		var cate_id=$('#cate_id').val();
		var original_price=$.trim($("#original_price").val());
		var price_pre=$.trim($("#price_pre").val());
		var price=$.trim($("#price").val());
		var storage_counts=$.trim($("#storage_counts").val());
		var start_times=$('#start_times').val();
		var end_times=$('#end_times').val();
		var order_counts=$('#order_counts').val();
		var px=$('#px').val();
		var isOk=true;
		if (start_times&&end_times&&!checkTime(start_times,end_times)){
			Msg.error('上下架时间不正确');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		if (cate_id=='0'&&!original_price&&!price_pre&&!price&&!storage_counts&&!start_times&&!end_times&&!px&&!order_counts){
			Msg.error('必须存在修改项');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		Msg.loading();
		  $.post('<?php echo U('goods/batch');?>',$(this).serialize(),function(result){
			  Msg.hide();
			  resetSubmit("#sub-ok",'保存');
			  if (result.status==1){
				Msg.ok('操作成功',function(){
					document.location.reload();	
				},1000);  
			}else{
				Msg.error(result.info);
			}
		  },'json');
		  return false;
	})
	$("#hide-batch-list").click(function(){
		$("#show-batch-list").fadeOut();
	})
})
</script>
<?php
include getTpl('footer', 'public');
?>
