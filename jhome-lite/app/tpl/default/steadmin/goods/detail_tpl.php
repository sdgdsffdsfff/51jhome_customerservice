<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
getJs('global/jquery.qrcode.min');
?>
<style>
.pictureList ul {
	margin:0;
	padding:0;
}
.pictureList li {
	list-style-type:none;
	float:left;
	margin:3px
}
.show-pop-content {
	overflow-y:scroll;
	overflow-x:hidden;
	table-layout: fixed;
	word-wrap:break-word;
	word-break:break-all;
}
</style>
<div class="table-responsive show-pop-content" style="width:650px; height:400px;">
<section class="panel portlet-item"> 
<header class="panel-heading">商品详细信息</header> 
<ul class="list-group"> 
<li class="list-group-item" style="text-align:center"><img src="<?php echo getImgUrl($rs['goods_pic']);?>" width="150" height="150"/></li>
<li class="list-group-item"><strong>编号</strong>：<?php echo $rs['gid'];?></li> 
<li class="list-group-item"><strong>货号</strong>：<?php echo $rs['goods_sn'];?></li>
<li class="list-group-item"><strong>类别</strong>：<?php echo $rs['cateName'];?></li>
<li class="list-group-item"><strong>店铺</strong>：<?php echo $rs['shopName'];?></li>
<li class="list-group-item"><strong>发布者</strong>：<?php echo $rs['userName'];?></li>
<li class="list-group-item"><strong>商品名称</strong>：<?php echo $rs['goods_name'];?></li>
<li class="list-group-item"><strong>预约商品</strong>：<?php echo $setting['status'][$rs['is_realtime']];?> <?php if($rs['is_realtime']){?>【<?php echo $setting['expTime'][$rs['booked_time']]['name'];?>】<?php }?></li>
<li class="list-group-item"><strong>副标题</strong>：<?php echo $rs['goods_subtitle'];?></li>
<li class="list-group-item"><strong>规格</strong>：<?php echo $rs['goods_spec'];?></li>
<li class="list-group-item"><strong>商品标签</strong>：<?php echo implode(",",$rs['tipsName']);?></li>
<li class="list-group-item"><strong>进价</strong>：<?php echo $rs['purc_price'];?></li>
<li class="list-group-item"><strong>原价</strong>：<?php echo $rs['original_price'];?></li>
<li class="list-group-item"><strong>价格前缀</strong>：<?php echo $rs['price_pre'];?></li>
<li class="list-group-item"><strong>售价</strong>：<?php echo $rs['price'];?></li>
<li class="list-group-item"><strong>商品条形码</strong>：<?php echo $rs['bar_code'];?></li>
<li class="list-group-item"><strong>商家货号</strong>：<?php echo $rs['goods_number'];?></li>
<li class="list-group-item"><strong>单件赠送积分</strong>：<?php echo $rs['credits'];?></li>
<li class="list-group-item"><strong>商品参数</strong>：<?php
$arr=array();
foreach($rs['goods_parameter'] as $val){
	$arr[]=implode('：',$val);
}
echo implode('、',$arr);
?></li>
<li class="list-group-item"><strong>下单数</strong>：<?php echo $rs['order_counts'];?></li>
<li class="list-group-item"><strong>销售数</strong>：<?php echo $rs['sale_counts'];?></li>
<li class="list-group-item"><strong>库存</strong>：<?php echo $rs['storage_counts'];?></li>
<li class="list-group-item"><strong>查看数</strong>：<?php echo $rs['hits_counts'];?></li>
<li class="list-group-item"><strong>点赞数</strong>：<?php echo $rs['love_counts'];?></li>
<li class="list-group-item"><strong>热门产品</strong>：<?php echo $setting['status'][$rs['is_hot']];?></li>
<li class="list-group-item"><strong>新品</strong>：<?php echo $setting['status'][$rs['is_new']];?></li>
<li class="list-group-item"><strong>推荐产品</strong>：<?php echo $setting['status'][$rs['is_recommend']];?></li>
<li class="list-group-item"><strong>上架时间</strong>：<?php echo outTime($rs['start_times']);?></li>
<li class="list-group-item"><strong>下架时间</strong>：<?php echo outTime($rs['end_times']);?></li>
<li class="list-group-item"><strong>特价商品</strong>：<?php echo $setting['status'][$rs['is_limited']];?></li>
<li class="list-group-item"><strong>每人限购量</strong>：<?php echo $rs['limit_counts'];?></li>
<li class="list-group-item"><strong>排序值</strong>：<?php echo $rs['px'];?></li>
<li class="list-group-item"><strong>发布时间</strong>：<?php echo outTime($rs['info_time']);?></li>
<li class="list-group-item"><strong>最后更新</strong>：<?php echo outTime($rs['refresh_time']);?></li>
<li class="list-group-item"><strong>状态</strong>：<?php echo $setting['goods_status'][$rs['status']];?></li>
<li class="list-group-item"><strong>商品描述</strong>：<?php echo $rs['goods_desc'];?></li>
</ul> 
</section>
<div id="data-matrix" style="margin:20px auto; width:230px; height:230px"></div>
</div>
<script>
$(function(){
	$("#facebox").click();
	$('#data-matrix').qrcode({width:230,height:230,text:'<?php echo U('steadmin/goods/preview',array('id'=>$rs['gid']));?>'}).show();	
})
</script>