<?php
if (!defined('IN_XLP')) {
exit('Access Denied');
}
$Document = array(
'pageid' => 'goods-detail', //页面标示
'pagename' => '结邻公社', //当前页面名称
'mycss' => array('global/style-3.0|20150328','steward/steward-3.0|20150629'), //加载的css样式表
'myjs' => array('global/jquery-1.9.0.min','global/global','global/art-template'), //加载的js脚本
'footerjs'=>array(),
'head'=>true,//是否加载头部文件
'wxjsapi'=>true,//是否需要微信js接口
);
?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<meta name="format-detection" content="telephone=no"/>
<meta charset="utf-8">
<title><?php echo $Document['pagename']; ?></title>
<?php
  if ($Document['mycss']) {
	  getCss($Document['mycss'],true);
  }
  if ($Document['myjs']) {
	  getJs($Document['myjs'],true);
  }
?>
<script>
var MAIN_PATH='<?php echo WEB_URL; ?>';
var PUBLIC_URL='<?php echo PUBLIC_PATH; ?>';
</script>
</head>
<body>
<!-- header -->
<header id="header" class="header pos-fix" style="top:0; left:0;">
    <a href="javascript:;"><i class="icon-chevron-left"></i></a>
    <span><?php echo $rs['goods_name'];?></span>
    <a href="javascript:;" class="header-r"><i class="icon-jcart lh-3"><em id="food-num" class="r-tag" style="display:none;">0</em></i></a>
</header>
<!-- 商品详情 B-->
<div id="goods-detail" class="goods-detail"></div>
<!-- 商品详情 E-->
<!--商品详情详细 模板 B-->
<script id="goods-detail-template" type="text/html">	
    <div class="detail-center">
        <div class="detail-item">
            <div class="img-box">
                <img width="100%" src="<%=orig_pic%>">
            </div>
            <div class="detail-cont">
                <h3 class="goods-name"><%=goods_name%></h3>
                <p class="d-info">
					<span class="c-price">￥<%=price%></span>
					<%if (original_price != '0.00') { %><span class="b-price">￥<%=original_price%></span><% } %>
				</p>
                <p class="d-info">
                    <span class="d-item">库存：<%=storage_counts%></span>
                    <span class="d-item">销量：<%=order_counts%></span>
                    <span class="d-item">规格：<%=goods_spec%></span>
                </p>
            </div>
        </div>
		<%if (goods_desc != '') { %>
        <div class="detail-item">
            <p class="d-tit">产品详情</p>
            <div class="d-cont"><%=#goods_desc %></div>
        </div>
		<% } %>
    </div>
	
    <div class="detail-bottom">
    	<span class="d-handle">
        	<a href="javascript:;" id="minus" data-type="minus" class="h-btn"><i class="icon-minus"></i></a>
            <span id="g-num" class="g-num" storage="<%=storage_counts%>" limit="<%=limit_counts%>" old-num="<%=p_num %>">
				<%if (p_num >= 1) { %><%=p_num %><% } else {%>1<% } %>
			</span>
			<a href="javascript:;" id="plus" data-type="plus" class="h-btn"><i class="icon-plus"></i></a>
        </span>
		<% if (storage_counts > 0 ) { %>
			<%if (sale_status == 1 && p_num == 0) { %>
				<a href="javascript:;" id="quick-buy" class="btn-ok" shop-id="<%=shop_id %>" gid="<%=gid%>" data-text="添加" data-first="first" data-for="<%=p_id%>">添加到购物车</a>
			<% } else if(sale_status == 1 && p_num > 0) {%>
				<a href="javascript:;" id="quick-buy" class="btn-ok" shop-id="<%=shop_id %>" gid="<%=gid%>" data-text="" data-first="" data-for="<%=p_id%>" style="background:#73e2dd;">在购物车中</a>
			<% } else {%>
				<a href="javascript:;" id="quick-buy" class="btn-ok" shop-id="<%=shop_id %>" gid="<%=gid%>" data-text="" data-first="<% if(p_num == 0) {%>first<%}%>" data-for="<%=p_id%>" style="background:#73e2dd;"><% if (sale_status == 2) { %>未开售<% } else { %>已下架<% } %></a>
			<% } %>
		<% } else { %>
			<a href="javascript:;" class="btn-ok" style="background:#73e2dd;">已售罄</a>
		<% } %>
    </div>
</script>
<!--商品详细 模板 E-->
<script>
var public_url = null,
	vid = <?php echo $vid;?>, //小区id
	shopId = null, //商店Id
	shopName = null, //商店名称
	gotoUrl='<?php echo U('steward/goods/my_cart');?>',
	goodsData = <?php echo $rs['goodsData'];?>, //获取商品数据
	saleStatus = <?php echo $rs['sale_status']; ?>, //销售状态
	saleTime = '<?php echo $rs['sale_time']; ?>';

var dataForWeixin = {
	appId: "",
	imgUrl: "<?php echo getImgUrl('statics/default/images/steward/share_2.jpg');?>",
	path: "intro",
	title: "下午茶，净菜，超市，每天有特价。快使唤小管家，享大优惠~",
	desc: "时光短暂，不要辜负美好食光哦~", 
	shareUrl: '<?php echo U("weixin/index/index");?>',
	fakeid: "",
	callback: null
};
$(function(){
	getGoods();
	$(document).on('click', '#quick-buy', function() {
		$.DIC.dialog({content: '对不起，不可添加', autoClose: true });
		return false;
	});
	$(document).on('click', '#plus,#minus', function() {
		$.DIC.dialog({content: '对不起，不可添加', autoClose: true });
		return false;
	});
	function getGoods(url) {
		var data = goodsData || false,
			s_status = saleStatus || 0,
			s_time = saleTime || 0;
		var $detail = $('#goods-detail');
		if (data) {
			var html_goods = '';
			data['p_num'] = 0;
			data['sale_status'] = s_status;
			data['sale_time'] = s_time;
			html_goods = template('goods-detail-template', data);
			$detail.html(html_goods);
			goodsInfo = data;
		} else {
			$detail.html('<p style="font-size:1.2rem; text-align:center; line-height:3rem;">暂无商品</p>');
		}
	}
});
</script>

</body>
</html>