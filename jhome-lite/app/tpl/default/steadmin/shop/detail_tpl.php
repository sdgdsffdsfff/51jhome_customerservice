<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
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
<header class="panel-heading">店铺详细信息</header> 
<ul class="list-group"> 
<li class="list-group-item" style="text-align:center"><img src="<?php echo getImgUrl($rs['pic_url']);?>" width="150" height="150"/></li>
<li class="list-group-item"><strong>编号</strong>：<?php echo $rs['shop_id'];?></li> 
<li class="list-group-item"><strong>店铺名称</strong>：<?php echo $rs['shop_name'];?></li>
<li class="list-group-item"><strong>分店名称</strong>：<?php echo $rs['shop_alt_name'];?></li>
<li class="list-group-item"><strong>所属区域</strong>：<?php echo $area[$rs['area_id']];?></li>
<li class="list-group-item"><strong>所属服务中心</strong>：<?php echo $service[$rs['service_id']];?></li>
<li class="list-group-item"><strong>店铺类型</strong>：<?php echo $setting['shop_type'][$rs['shop_type']];?></li>
<li class="list-group-item"><strong>正在参与的活动</strong>：<?php foreach ($rs['tips_list'] as $val){ echo $val?$setting['shop_tips'][$val].'，':'';}?></li>
<li class="list-group-item"><strong>资金结算方式</strong>：<?php echo $adminData['settlement'][$rs['settlement']];?></li>
<li class="list-group-item"><strong>店铺等级</strong>：<?php echo $setting['shop_power'][$rs['shop_power']];?></li>
<li class="list-group-item"><strong>电话</strong>：<?php echo $rs['tel'];?></li>
<li class="list-group-item"><strong>人均消费</strong>：<?php echo $rs['avg_price'].'元';?></li>
<li class="list-group-item"><strong>综合评分</strong>：<?php echo $rs['score_total'];?></li>
<li class="list-group-item"><strong>口味评分</strong>：<?php echo $rs['score_flavour'];?></li>
<li class="list-group-item"><strong>服务评分</strong>：<?php echo $rs['score_service'];?></li>
<li class="list-group-item"><strong>准点率</strong>：<?php echo $rs['ontime_point'];?></li>
<li class="list-group-item"><strong>营业时间</strong>：<?php echo $rs['stime'];?></li>
<li class="list-group-item"><strong>打样时间</strong>：<?php echo $rs['etime'];?></li>
<li class="list-group-item"><strong>延时配送</strong>：<?php echo $rs['is_delay']?'是':'否';?></li>
<li class="list-group-item"><strong>热卖商品</strong>：<?php foreach ($rs['hot_goods'] as $val){ echo $val?$val.'，':'';}?></li>
<li class="list-group-item"><strong>地址</strong>：<?php echo $rs['address'];?></li>
<li class="list-group-item"><strong>店铺描述</strong>：<?php echo $rs['content'];?></li>
<li class="list-group-item"><strong>经纬度</strong>：<?php echo 'lat：'.$rs['lat'].'；lng：'.$rs['lng'];?></li>
</ul> 
</section> 
</div>
<script>
$(function(){
	$("#facebox").click();	
})
</script>