<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'home-edit', //页面标示
    'pagename' => '小管家设置', //当前页面名称
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
                <label class="col-sm-1 control-label">运费设置</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="shipping_fee" id="shipping_fee" placeholder="纯数字，单位：元" class="form-control" value="<?php echo $rs['shipping_fee'];?>" />
                  <span class="help-block">全局运费设置，纯数字，单位：元</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">运费满减起始额度</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="shipping_fee_offset_sum" id="shipping_fee_offset_sum" placeholder="纯数字" class="form-control" value="<?php echo $rs['shipping_fee_offset_sum'];?>" />
                  <span class="help-block">运费满减起始额度，0为不设置，纯数字，单位：元</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">运费满减金额</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="shipping_fee_offset" id="shipping_fee_offset" placeholder="纯数字" class="form-control" value="<?php echo $rs['shipping_fee_offset'];?>" />
                  <span class="help-block">订单金额超过设置额度即可扣减的运费金额，纯数字，单位：元</span></div>
              </div>
              
<div style="display:none">
              
              <hr style="border-bottom:0; border-top:1px #CCCCCC solid"/>
              <div class="form-group">
                <label class="col-sm-1 control-label">特价商品起始金额</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="limit_fee" id="limit_fee" placeholder="纯数字" class="form-control" value="<?php echo $rs['limit_fee'];?>" />
                  <span class="help-block">订单金额超过设置额度即可购买特价商品，纯数字，单位：元</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">特价商品数量</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="limit_fee_sum" id="limit_fee_sum" placeholder="纯数字" class="form-control" value="<?php echo $rs['limit_fee_sum'];?>" />
                  <span class="help-block">每单特价商品可购买数量，纯数字，单位：元</span></div>
              </div>
              
              <hr style="border-bottom:0; border-top:1px #CCCCCC solid"/>
              <div class="form-group">
                <label class="col-sm-1 control-label">订单满减起始额度</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="sales_offset_sum" id="sales_offset_sum" placeholder="纯数字" class="form-control" value="<?php echo $rs['sales_offset_sum'];?>" />
                  <span class="help-block">订单满减起始额度，0为不设置，纯数字，单位：元</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">订单满减金额</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="sales_offset" id="sales_offset" placeholder="纯数字" class="form-control" value="<?php echo $rs['sales_offset'];?>" />
                  <span class="help-block">订单金额超过设置额度即可扣减的金额，纯数字，单位：元</span></div>
              </div>
              
              
              <hr style="border-bottom:0; border-top:1px #CCCCCC solid"/>
              <div class="form-group">
                <label class="col-sm-1 control-label">订单商家过多加收服务费金额</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="too_much_shop_fee" id="too_much_shop_fee" placeholder="纯数字" class="form-control" value="<?php echo $rs['too_much_shop_fee'];?>" />
                  <span class="help-block">订单商家过多收取的服务费，纯数字，单位：元</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">订单商家过多加收服务费起始店数</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="too_much_shop_num" id="too_much_shop_num" placeholder="纯数字" class="form-control" value="<?php echo $rs['too_much_shop_num'];?>" />
                  <span class="help-block">订单商家超过此设置需加收服务费，纯数字，单位：家</span></div>
              </div>
</div>
              <hr style="border-bottom:0; border-top:1px #CCCCCC solid"/>
              <div class="form-group">
                <label class="col-sm-1 control-label">第三方商家加收服务费金额</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="shop_fee" id="shop_fee" placeholder="纯数字" class="form-control" value="<?php echo $rs['shop_fee'];?>" />
                  <span class="help-block">第三方商家收取的服务费，纯数字，单位：元</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">收取服务费商家上限</label>
                <div class="col-sm-3">
                  <input type="text" style="width:80px;" name="max_shop_counts" id="max_shop_counts" placeholder="纯数字" class="form-control" value="<?php echo $rs['max_shop_counts'];?>" />
                  <span class="help-block">订单商家超过此设置不再累计服务费，纯数字，单位：家</span></div>
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
		  $.post('<?php echo U('home/save');?>',$(this).serialize(),function(result){
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
