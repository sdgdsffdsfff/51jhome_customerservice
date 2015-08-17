<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'order-index', //页面标示
    'pagename' => '订单详情', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array('content/jquery.smoothConfirm'),
    'head' => true, //加载头部文件
);
include getTpl('header', 'public');
?>
<script charset="utf-8" src="http://map.qq.com/api/js?v=2.exp&key=1aa6a77c877c9d026f2f7640bb722f41"></script>
<style>
.pt7 {
	padding-top:7px;
}
.line-s {
	line-height:24px
}
.form-group {
	margin-bottom:10px
}
.control-label {
	padding-right:0
}
</style>
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
      <div class="col-sm-12">
        <section class="panel">
          <div class="panel-body">
            <form class="form-horizontal" method="get" data-validate="parsley">
              <div class="form-group">
                <label class="col-sm-2 control-label">订单编号</label>
                <div class="col-sm-5 pt7"> <?php echo $rs['order_id'];?> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">订单号</label>
                <div class="col-sm-5 pt7"> <?php echo $rs['order_sn'];?>
                  <?php if($rs['is_spell']){echo '[<span class="red">拼单</span>]';}?>
                  <?php if($rs['is_helpbuy']){echo '[<span class="red">代付</span>]';}?>
                  <?php if($rs['is_give']){echo '[<span class="red">赠单</span>]';}?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">下单时间</label>
                <div class="col-sm-5 pt7"> <?php echo outTime($rs['order_time']);?> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">订单类型</label>
                <div class="col-sm-5 pt7"> <?php echo $setting['order_type'][$rs['order_type']];?> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">客户端</label>
                <div class="col-sm-5 pt7"> <?php echo $setting['order_source'][$rs['order_source']];?> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">订单费用信息</label>
                <div class="col-sm-5 pt7 line-s"> <strong>商品金额</strong>：<span class="red"><?php echo $rs['goods_amount'];?></span> 元<br/>
                  <strong>运费金额</strong>：<span class="red"><?php echo $rs['shipping_fee'];?></span> 元<br/>
                  <strong>采购费</strong>：<span class="red"><?php echo $rs['purchase_fee'];?></span> 元<br/>
                  <strong>结邻币抵扣</strong>：<span class="red"><?php echo $rs['credit_offset'];?></span> 元<br/>
                  <strong>抵价券抵扣</strong>：<span class="red"><?php echo $rs['coupon_offset'];?></span> 元
                  <?php if($rs['status']!=2&&$rs['coupon_offset']>0){?>
                  <a href="javascript:;" id="check-coupon" class="btn btn-info btn-xs">查询抵价券信息</a><br/>
                  <div class="alert alert-warning alert-block" id="show-coupon-info" style="display:none; font-size:13px; margin-top:5px"></div>
                  <?php }else{echo '<br/>';}?>
                  <strong>促销抵扣</strong>：<span class="red"><?php echo $rs['sales_offset'];?></span> 元<br/>
                  <strong>订单金额</strong>：<span class="red"><?php echo $rs['order_amount'];?></span> 元<br/>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">实际支付信息</label>
                <div class="col-sm-5 pt7 line-s"> <strong>支付金额</strong>：<span class="red" id="pay_amount"><?php echo $rs['pay_amount'];?></span> 元<br/>
                  <strong>支付时间</strong>：<span id="pay_time"><?php echo outTime($rs['pay_time']);?></span><br/>
                  <strong>支付方式</strong>：<span id="pay_type"><?php echo $rs['pay_id']?$setting['pay_type'][$rs['pay_type']]:'';?></span><br/>
                  <strong>流水号</strong>：<span id="pay_id"><?php echo $rs['pay_id'];?></span><br/>
                  <?php
                  if (($rs['status']==0||$rs['status']==2||$rs['status']==12)&&!$rs['pay_id']){//检查支付情况
				  ?>
                  <a id="check-pay" class="btn btn-danger" href="javascript:;">检查支付</a> <br/>
                  <span class="gray">* 如用户反馈订单已付款而还是显示“未付款”或“订单过期”请点击按钮进行支付检查</span>
                  <?php }?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">订单状态</label>
                <div class="col-sm-5 pt7"> <?php echo $setting['order_status'][$rs['status']];?>
                  <?php
                  if ($rs['status']==13){//异常订单处理
				  ?>
                  <br/>
                  <span class="gray">* 异常订单是由支付到账不及时造成的，请检查能否按订单时间配送，如不能按时配送请及时与客户联系修改配送时间</span> <br/>
                  <a class="btn btn-info" href="<?php echo U('manage/abnormal',array('id'=>$rs['order_id']));?>" rel="pop">处理异常</a>
                  <?php }?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">服务中心 / 小区</label>
                <div class="col-sm-5 pt7"> <?php echo $rs['serviceName'];?> / <?php echo $rs['villageName'];?> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">下单用户</label>
                <div class="col-sm-5 pt7 line-s"> <strong>UID</strong>：<?php echo $rs['uid'];?><br/>
                  <strong>昵称</strong>：<?php echo $rs['userName'];?><br/>
                  <a class="btn btn-info" href="<?php echo U('member/detail',array('uid'=>$rs['uid']));?>" rel="pop">查看用户信息</a> <a class="btn btn-info" rel="pop" href="<?php echo U('member/msg',array('uid'=>$rs['uid']));?>">发送消息</a> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">收货信息</label>
                <div class="col-sm-5 pt7 line-s"> <strong>配送时间</strong>：<?php echo outTime($rs['arrive_date'],2);?> <?php echo $rs['arrive_time'];?><br/>
                  <strong>收货人</strong>：<?php echo $rs['username'];?><br/>
                  <strong>电话</strong>：<?php echo $rs['phone'];?><br/>
                  <strong>地址</strong>：<?php echo $rs['address'];?><br/>
                  <strong>留言</strong>：<?php echo $rs['desc'];?><br/>
                  <a href="javascript:;" <?php if($isEdit){echo 'rel="pop"';}?> id="edit-order" class="btn btn-info">修改配送信息</a> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">审核人：<br/>
                  订单审核时间：</label>
                <div class="col-sm-5 pt7"> <?php echo $rs['service_user'];?><br/>
                  <?php echo outTime($rs['confirm_time']);?> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">配货员：<br/>
                  配货完成时间：</label>
                <div class="col-sm-5 pt7"> <?php echo $rs['deployment_user'];?>
                <?php if(in_array($rs['status'], array(3, 4, 5, 6))&&$rs['deployment_uid']){?>
                  <a href="<?php echo U('manage/edit_worker',array('order_id'=>$rs['order_id'],'type'=>1));?>" rel="pop" class="btn btn-info btn-xs">更换配货员</a>
                <?php }?>
                  <br/>
                  <?php echo outTime($rs['shipping_time']);?> </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">配送小管家：<br/>
                  配送完成时间：</label>
                <div class="col-sm-5 pt7"> <?php echo $rs['worker_user'];?>
                <?php if(in_array($rs['status'], array(3, 4, 5, 6))&&$rs['worker_uid']){?>
                  <a href="<?php echo U('manage/edit_worker',array('order_id'=>$rs['order_id'],'type'=>2));?>" rel="pop" class="btn btn-info btn-xs">更换小管家</a>
                  <?php }?>
                  <br/>
                  <?php echo outTime($rs['finish_time']);?> </div>
              </div>
              <?php if($rs['status']==10){ ?>
              <div class="form-group">
                <label class="col-sm-2 control-label">退款信息：</label>
                <div class="col-sm-5 pt7 line-s"> <strong>实际退款金额</strong>：<?php echo $orderRefund['actual_refund'];?><br/>
                  <strong>退还结邻币</strong>：<?php echo $orderRefund['score_return'];?><br/>
                  <strong>操作人</strong>：<?php echo $orderRefund['actualUserNmae'];?><br/>
                  <strong>操作流水号</strong>：<?php echo $orderRefund['serial_number'];?><br/>
                  <strong>退款方式</strong>：<?php echo $setting['actual_type'][$orderRefund['actual_type']];?><br/>
                  <strong>操作时间</strong>：<?php echo outTime($orderRefund['act_time']);?><br/>
                </div>
              </div>
              <?php } ?>
              <?php if($rs['status']==11&&$comment){ ?>
              <div class="form-group">
                <label class="col-sm-2 control-label">用户评价：</label>
                <?php
				  if ($comment['stars']){
				  ?>
                <div class="col-sm-5 pt7 line-s"> <strong>服务评分：</strong><span class="red"><?php echo $comment['stars'];?></span> 星<br/>
                  <?php
					if ($comment['complainList']){
					?>
                  <strong>投诉内容：</strong>
                  <?php
					 $complainList=array();
					foreach($comment['complainList'] as $val){
						$complainList[]=$setting['comment_complain'][$val];
					}
					echo implode('、',$complainList);
					?>
                  <br/>
                  <?php
					}
				?>
                <strong>用户留言：</strong><?php echo $comment['content'];?><br/>
                </div>
                <?php
				  }else{
				?>
                <div class="col-sm-5 pt7 line-s"> <strong>服务态度：</strong><?php echo $setting['score_server'][$comment['score_server']];?><br/>
                  <strong>配送速度：</strong><?php echo $setting['score_post'][$comment['score_post']];?><br/>
                  <strong>商品质量：</strong><?php echo $setting['score_goods'][$comment['score_goods']];?><br/>
                  <strong>用户留言：</strong><?php echo $comment['content'];?><br/>
                </div>
                <?php
				  }
				?>
              </div>
              <?php } ?>
              <section class="panel">
                <header class="panel-heading">订单产品</header>
                <div class="table-responsive">
                  <table class="table table-striped m-b-none" data-ride="datatables">
                    <thead>
                      <tr>
                        <th>商品名称</th>
                        <th>店铺</th>
                        <th>规格</th>
                        <th>返结邻币</th>
                        <th>单价</th>
                        <th>数量</th>
                        <th>小计</th>
                        <th>拼单人</th>
                        <th>下单时间</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
				  $goodsNum=0;
				  $creditsNum=0;
                  foreach ($goods as $key => $value) {
				  ?>
                      <tr>
                        <td align="left" valign="middle"><?php echo $value['goods_name'];?></td>
                        <td align="left" valign="middle"><?php echo $value['shopName'];?></td>
                        <td align="left" valign="middle"><?php echo $value['goods_spec'];?></td>
                        <td align="left" valign="middle"><?php echo $value['credits'];?></td>
                        <td align="left" valign="middle"><?php echo $value['goods_price'];?></td>
                        <td align="left" valign="middle"><?php echo $value['goods_counts'];?></td>
                        <td align="left" valign="middle">金额：<span class="red"><?php echo $value['goods_counts'] * $value['goods_price'];?></span> 元<br/>
                          结邻币：<span class="red"><?php echo $value['goods_counts'] * $value['credits'];?></span></td>
                        <td align="left" valign="middle"><?php echo $value['is_spell']?$value['spell_username']:'无';?></td>
                        <td align="left" valign="middle"><?php echo outTime($value['add_time']);?></td>
                      </tr>
                      <?php 
				  	$goodsNum+=$value['goods_counts'] * $value['goods_price'];
					$creditsNum+=$value['goods_counts'] * $value['credits'];
				  } 
				  $downNum=priceFormat($rs['credit_offset']+$rs['coupon_offset']+$rs['sales_offset']);
				  ?>
                      <tr>
                        <td colspan="20">订单总计：商品<span class="red" style="font-size:20px"><?php echo priceFormat($goodsNum);?></span> 元，运费<span class="red" style="font-size:20px"><?php echo $rs['shipping_fee'];?></span> 元，采购费<span class="red" style="font-size:20px"><?php echo $rs['purchase_fee'];?></span> 元，抵扣<span class="red" style="font-size:20px"><?php echo $downNum;?></span> 元，订单金额<span class="red" style="font-size:20px"><?php echo $rs['order_amount'];?></span> 元，返结邻币：<span class="red" style="font-size:20px"><?php echo $creditsNum;?></span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </section>
              <div style="margin-top:10px; margin-bottom:80px"> <a href="<?php echo U('order/index');?>" class="btn btn-default">返回列表</a> <a href="<?php echo U('manage/index',array('id'=>$rs['order_id'],'act'=>1));?>" rel="pop" class="btn btn-success">处理订单</a> <a href="<?php echo U('manage/index',array('id'=>$rs['order_id']));?>" rel="pop" class="btn btn-info">更改状态</a> <a href="<?php echo U('map/order',array('id'=>$rs['order_id']));?>" rel="pop" id="edit-order" class="btn btn-primary">查看地图</a> <a href="<?php echo U('manage/printorder',array('id'=>$rs['order_id']));?>" rel="pop" class="btn btn-primary">打印订单</a> <a href="javascript:;" rel="<?php echo $rs['order_id'];?>" class="btn btn-danger cancel-order">取消订单</a></div>
            </form>
          </div>
        </section>
      </div>
    </div>
  </section>
  <!--/ main padder -->
</section>
<!--/ content -->
<!--主体 结束-->
<script>
var isEdit=<?php echo $isEdit?1:0;?>,orderId=<?php echo $rs['order_id'];?>,orderSn='<?php echo $rs['order_sn'];?>',uid=<?php echo $rs['uid'];?>,isCheckOk=false;
$(function(){
	$("#check-coupon").click(function(){
		if (isCheckOk){
			return false;	
		}
		Msg.loading();
		$.post('<?php echo U('manage/check_coupon');?>',{"order_sn":orderSn,"uid":uid},function(result){
			Msg.hide();
			if (result.status==1){
				var cateNameList=result.data['cateNameList'];
				var useShopList=result.data['useShopList'];
				var strHtml='<strong>编号</strong>：'+result.data['id']+'<br/>'+
				'<strong>券码</strong>：'+result.data['coupon_code']+'<br/>'+
				'<strong>类型</strong>：'+result.data['type']+'<br/>'+
				'<strong>标题</strong>：'+result.data['coupon_title']+'<br/>'+
				'<strong>抵扣金额</strong>：'+result.data['coupon_money']+' 元<br/>'+
				'<strong>起始金额</strong>：'+result.data['start_amount']+' 元<br/>'+
				'<strong>开始使用时间</strong>：'+result.data['start_time']+'<br/>'+
				'<strong>截止时间</strong>：'+result.data['end_time']+'<br/>'+
				'<strong>生成时间</strong>：'+result.data['info_time']+'<br/>'+
				'<strong>客户端</strong>：'+result.data['use_client']+'<br/>'+
				'<strong>限用类目</strong>：'+cateNameList.join('、')+'<br/>'+
				'<strong>限用店铺</strong>：'+useShopList.join('、')+'<br/>';
				$("#show-coupon-info").show().html(strHtml);
				isCheckOk=true;
			 }else{
				Msg.error(result.info);
			}
		},'json');
	})
	$("#edit-order").click(function(){
		if (isEdit){
			$(this).attr('href','<?php echo U('manage/edit',array('id'=>$rs['order_id']));?>');
			return true;	
		}else{
			Msg.alert('该订单不可编辑');
			return false;	
		}
	})
	$("#check-pay").click(function(){
		Msg.loading();
		$.post('<?php echo U('manage/check_pay');?>',{"order_id":orderId},function(result){
			Msg.hide();
			if (result.status==1){
				Msg.ok('确认已支付，即将刷新页面...',function(){document.location.reload();},3000);
			 }else{
				Msg.error(result.info);
			}
		},'json');
	})
	$('.cancel-order').click(function(){
	  var id=$(this).attr('rel');
	  $(this).smoothConfirm("确认取消该订单吗？", {
		  ok: function() {
			  Msg.loading();
			  $.post('<?php echo U('manage/cancel');?>',{"order_id":id},function(result){
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
})
</script>
<?php
include getTpl('footer', 'public');
?>
