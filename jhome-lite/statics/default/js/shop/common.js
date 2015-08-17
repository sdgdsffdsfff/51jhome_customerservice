// JavaScript Document

$(window).load(function() { 
	$("#status").fadeOut(); 
	$("#loading").delay(350).fadeOut("slow"); 
})

$(function(){

	//右下角的菜单弹出、隐藏
	$("#bottom-nav .bn-menu").click(function(){
		var u_obj = $("#user-center");
		if(u_obj.css("display") == "none"){
			u_obj.show();
		}else{
			u_obj.hide();
		}
	});
});

//消息弹出框
function alertTips(text,time){
	var obj = $("#tips");
	
	if(obj.css("display") == "none"){
		obj.text(text);
		obj.show();
		var stime = window.setTimeout(function(){
			obj.hide();
		},time);
	}
}

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