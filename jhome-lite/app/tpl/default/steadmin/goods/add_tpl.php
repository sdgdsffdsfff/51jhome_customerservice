<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'goods-add', //页面标示
    'pagename' => '添加商品', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array('../ueditor/editor.min'), //加载的js脚本
	'footerjs'=>array('content/fuelux/fuelux','content/combodate/moment.min','content/combodate/combodate','../ueditor/editor_config'),
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
  <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_PATH; ?>jquery-ui/css/smoothness/jquery-ui-1.10.0.custom.min.css"/>
  <script type="text/javascript" src="<?php echo PUBLIC_PATH; ?>jquery-ui/js/jquery-ui-1.10.0.custom.min.js"></script>
  <script type="text/javascript" src="<?php echo PUBLIC_PATH; ?>jquery-ui/js/jquery-ui-timepicker-addon.js"></script>
  <section class="main padder">
    <div class="clearfix">
      <h4><i class="fa fa-edit"></i><?php echo trim($Document['pagename'],'_');?></h4>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <section class="panel">
          <div class="panel-body">
            <form class="form-horizontal" method="post" id="form1">
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
                <label class="col-sm-1 control-label">商品名称</label>
                <div class="col-sm-3">
                  <input type="text" name="goods_name" id="goods_name" placeholder="" class="form-control" value="" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">小标题</label>
                <div class="col-sm-3">
                  <input type="text" name="goods_subtitle" id="goods_subtitle" placeholder="" class="form-control" value="" />
                  <span class="help-block">对商品名的补充说明</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">商品规格</label>
                <div class="col-sm-3">
                  <input type="text" name="goods_spec" id="goods_spec" style="width:80px" placeholder="" class="form-control" value="" />
                  <span class="help-block">商品单位，如200ml、1kg</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">商品小图</label>
                <div class="col-sm-3">
                  <input type="text" name="goods_pic" id="goods_pic" placeholder="" class="form-control" value="" />
                  <span class="help-block">请上传您的LOGO图标，标准尺寸150px*150px</span></div>
                <div class="col-sm-3">
                  <iframe width="280" height="24" src="<?php echo U('upload/index',array('id'=>'goods_pic')); ?>" scrolling="no" frameborder="0"></iframe>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">商品进价</label>
                <div class="col-sm-1" style=" margin-right:20px">
                  <input type="text" name="purc_price" id="purc_price" style="width:120px" placeholder="商品货源进价" class="form-control" value="" />
                </div>
                <div class="col-sm-1" style="line-height:35px">元</div>
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
                  <input type="text" name="price" id="price" placeholder="" style="width:130px" class="form-control" value="" />
                </div>
                <div class="col-sm-1" style="line-height:35px">元</div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">商品库存</label>
                <div class="col-sm-1" style="margin-right:20px">
                  <input type="text" name="storage_counts" id="storage_counts" style="width:120px" placeholder="" class="form-control" value="" />
                  <span class="help-block"></span></div>
                <div class="col-sm-1" style="line-height:35px">件</div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">前台展示销量</label>
                <div class="col-sm-1" style="margin-right:20px">
                  <input type="text" name="order_counts" id="order_counts" style="width:120px" placeholder="" class="form-control" value="0" />
                  <span class="help-block">前台展示量是此值的3倍</span></div>
                <div class="col-sm-1" style="line-height:35px">件</div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">商品条形码</label>
                <div class="col-sm-2" style="margin-right:20px">
                  <input type="text" name="bar_code" id="bar_code" placeholder="" class="form-control" value="" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">商家货号</label>
                <div class="col-sm-2" style="margin-right:20px">
                  <input type="text" name="goods_number" id="goods_number" placeholder="" class="form-control" value="" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">是否预约商品</label>
                <div class="col-sm-7">
                  <?php
				  $i=1;
				  foreach($setting['status'] as $key=>$val){
				  ?>
						  <div class="col-xs-2 radio m-l-large">
							<label class="radio-custom" id="selec-is-realtime-<?php echo $i;?>">
							  <input type="radio" name="is_realtime" <?php if(!$key){echo 'checked="checked"';}?> value="<?php echo $key;?>">
							  <i class="fa fa-circle-o <?php if(!$key){echo 'checked';}?>"></i><?php echo $val;?></label>
						  </div>
				<?php 
				 $i+=1;
				 }?>
                  <span class="help-block" style="clear:both">预约商品当天不可送达</span> </div>
              </div>
              <div class="form-group" id="show-booked_time" style="display:none">
                <label class="col-sm-1 control-label">预定配送时间</label>
                <div class="col-sm-2" style="margin-right:20px">
                  <select name="booked_time" id="booked_time" class="form-control">
                    <?php
					 foreach($setting['expTime'] as $key=>$val){
					 ?>
							<option value="<?php echo $key;?>"><?php echo $val['name'];?></option>
					<?php
					 }
					?>
                  </select>
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">商品标签</label>
                <div class="col-sm-10">
                  <div class="row">
                    <?php
						foreach($setting['goods_tips'] as $key=>$val){
					?>
                    <div class="col-xs-1 radio m-l-large select_no_result_type">
                      <label class="checkbox-custom">
                        <input type="checkbox" name="goods_tips[]" value="<?php echo $key;?>" <?php if (!$key){echo 'checked';}?>/>
                        <i class="fa fa-check-square-o <?php if (!$key){echo 'checked';};?>"></i><?php echo $val;?></label>
                    </div>
                    <?php
						}
					?>
                  </div>
                  <p class="help-block"></p>
                </div>
              </div>
              <div class="form-group" id="show-model-list">
                <label class="col-sm-1 control-label">商品参数
                  <button id="add-model" class="btn btn-primary" type="button">添加</button>
                </label>
                <div id="rs-line" class="col-sm-10">
                  <div class="col-sm-12 row" style="margin:3px 0">
                    <div class="col-md-3">
                      <input type="text" name="goods_parameter[n][]" placeholder="属性名称，如产地、保质期" class="form-control" value="" />
                    </div>
                    <div class="col-md-2">
                      <input type="text" name="goods_parameter[v][]" placeholder="属性值" class="form-control" value="" />
                    </div>
                    <div class="col-md-3"></div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">商品描述</label>
                <div class="col-sm-6"> <span class="maroon"></span><span class="help-inline">(不能超过20000个字)</span>
                  <div class="controls"> 
                    <script type="text/plain" id="editor"></script> 
                  </div>
                  <textarea name="goods_desc" id="goods_desc" style="display:none"></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">单件商品赠送积分</label>
                <div class="col-sm-3">
                  <input type="text" name="credits" id="credits" style="width:80px" placeholder="" class="form-control" value="0" />
                  <span class="help-block">单件商品购买后赠送的消费积分数量</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">是否热门商品</label>
                <div class="col-sm-7">
                  <?php
          foreach($setting['status'] as $key=>$val){
		  ?>
                  <div class="col-xs-2 radio m-l-large">
                    <label class="radio-custom">
                      <input type="radio" name="is_hot" <?php if(!$key){echo 'checked="checked"';}?> value="<?php echo $key;?>">
                      <i class="fa fa-circle-o <?php if(!$key){echo 'checked';}?>"></i><?php echo $val;?></label>
                  </div>
                  <?php }?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">是否新品</label>
                <div class="col-sm-7">
                  <?php
          foreach($setting['status'] as $key=>$val){
		  ?>
                  <div class="col-xs-2 radio m-l-large">
                    <label class="radio-custom">
                      <input type="radio" name="is_new" <?php if(!$key){echo 'checked="checked"';}?> value="<?php echo $key;?>">
                      <i class="fa fa-circle-o <?php if(!$key){echo 'checked';}?>"></i><?php echo $val;?></label>
                  </div>
                  <?php }?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">是否推荐商品</label>
                <div class="col-sm-7">
                  <?php
          foreach($setting['status'] as $key=>$val){
		  ?>
                  <div class="col-xs-2 radio m-l-large">
                    <label class="radio-custom">
                      <input type="radio" name="is_recommend" <?php if(!$key){echo 'checked="checked"';}?> value="<?php echo $key;?>">
                      <i class="fa fa-circle-o <?php if(!$key){echo 'checked';}?>"></i><?php echo $val;?></label>
                  </div>
                  <?php }?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">是否特价商品</label>
                <div class="col-sm-7">
                  <?php
          foreach($setting['status'] as $key=>$val){
		  ?>
                  <div class="col-xs-2 radio m-l-large">
                    <label class="radio-custom">
                      <input type="radio" name="is_limited" <?php if(!$key){echo 'checked="checked"';}?> value="<?php echo $key;?>">
                      <i class="fa fa-circle-o <?php if(!$key){echo 'checked';}?>"></i><?php echo $val;?></label>
                  </div>
                  <?php }?>
                  <span style="clear:both" class="help-block">特价商品必须是订单达到指定金额才可购买</span> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">每人限购数量</label>
                <div class="col-sm-4">
                  <input type="text" style="width:80px" name="limit_counts" id="limit_counts" placeholder="" class="form-control" value="0" />
                  <span class="help-block">限量商品，每人只能购买指定数量，0为不限制</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">商品排序值</label>
                <div class="col-sm-4">
                  <input type="text" style="width:80px" name="px" id="px" placeholder="" class="form-control" value="0" />
                  <span class="help-block">商品显示顺序，0-255之间正整数，数字越大越靠前</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">上架时间</label>
                <div class="col-sm-3">
                  <input style="width:225px" type="text" name="start_times" id="start_times" placeholder="" class="form-control" value="" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">下架时间</label>
                <div class="col-sm-3">
                  <input style="width:225px" type="text" name="end_times" id="end_times" placeholder="" class="form-control" value="" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">发布到店铺</label>
                <div class="col-sm-10">
                  <div class="row">
                    <?php
						foreach($shop as $key=>$val){
					?>
                    <div class="checkbox">
                      <label class="checkbox-custom">
                        <input type="checkbox" name="shops[]" value="<?php echo $val['shop_id'];?>" <?php if (!$key){echo 'checked';}?>/>
                        <i class="fa fa-check-square-o <?php if (!$key){echo 'checked';};?>"></i><?php echo $val['shop_name'].($val['shop_alt_name']?'['.$val['shop_alt_name'].']':'');?></label>
                    </div>
                    <?php
						}
					?>
                  </div>
                  <p class="help-block"></p>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-9 col-lg-offset-1">
                  <div class="col-sm-1">
                    <button type="submit" id="sub-ok" data-loading-text="正在提交..." class="btn btn-primary">保存</button>
                  </div>
                  <div class="col-md-offset-2">
                    <button type="button" class="btn btn-white">取消</button>
                  </div>
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
var UEDITOR_HOME_URL = '<?php echo LOCAL_PUBLIC_PATH;?>ueditor/';
var fixedImagePath='';
var uploadUrl='<?php echo U('upload/save',array('p'=>'goods'));?>';
$(function(){
	$('#start_times').datetimepicker();
    $('#end_times').datetimepicker();
	$("#selec-is-realtime-1").click(function(){
		$("#show-booked_time").hide();
		$("#booked_time").val(0);
	})
	$("#selec-is-realtime-2").click(function(){
		$("#show-booked_time").show();
	})
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
	$('#form1').submit(function(){
		var cate_id=$("#cate_id option:selected").val();
		if (cate_id=='0'){
			Msg.error('请先选择商品类别');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		var shop_name=$.trim($("#goods_name").val());
		var isOk=true;
		if (!shop_name){
			Msg.error('商品名称不能为空');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		if (!checkTime($('#start_times').val(),$('#end_times').val())){
			Msg.error('上下架时间不正确');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		$("#goods_desc").val(window.msg_editor.getContent());
		Msg.loading();
		  $.post('<?php echo U('goods/post');?>',$(this).serialize(),function(result){
			  Msg.hide();
			  resetSubmit("#sub-ok",'保存');
			  if (result.status==1){
				Msg.ok('添加成功',function(){
					document.location='<?php echo U('goods/index');?>';	
				},1000);  
			}else{
				Msg.error(result.info);
			}
		  },'json');
		  return false;
	});
	$("#add-model").click(function(){
		var tpl='<div class="col-sm-12 row" style="margin:3px 0"><div class="col-md-3"><input type="text" name="goods_parameter[n][]" placeholder="属性名称，如产地、保质期" class="form-control" value="" /></div>'+
                  '<div class="col-md-2"><input type="text" name="goods_parameter[v][]" placeholder="属性值" class="form-control" value="" /></div>'+
                  '<div class="col-md-3"><button class="btn btn-danger del-model" type="button">删除</button></div></div>';
		$("#rs-line").append(tpl);
	})
	$("#rs-line").delegate(".del-model",'click',function(){
		var count=$("#rs-line").find(".row").length;
		//console.log(count);
		if (count==1){
			Msg.error('必须至少保留一个属性');
			return false;
		}
		$(this).parent().parent().fadeOut().remove();
	});
	window.msg_editor = new UE.ui.Editor({
        initialFrameWidth:800
    });
    window.msg_editor.render("editor");
})
</script>
<?php
include getTpl('footer', 'public');
?>
