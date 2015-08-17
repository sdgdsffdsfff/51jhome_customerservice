//ajax全局对象
var ajaxData   = {};
//通用参数
var ajaxParams = {
	type: 'POST',
	dataType: 'json',
};
ajaxParams.error = function() {
	alertTips("网络异常", 2000);
};

//收藏商品
ajaxData.fav = ajaxParams;
ajaxData.fav.error = ajaxParams.error;
//立即购买
ajaxData.buy = ajaxParams;
ajaxData.buy.error = ajaxParams.error;
ajaxData.buy.success = function(json) {
	if(!json.err) {
		self.location.href = json.msg;
	}else {
		alertTips(json.msg, 2000);
	}
}
