$(function(){
		
	//选择套餐
	$("#detail_sendto .option").click(function(){
	    $(this).addClass('option_selected').siblings().removeClass('option_selected');
	});
	
	//改变商品数量 --
	$("#skuCont .minus").click(function(){
		var b_num = $("#buyNum").val();
		if(b_num > 1){
			$("#buyNum").val(b_num-1);
		}
	});
	
	//改变商品数量 ++
	$("#skuCont .plus").click(function(){
		var b_num = parseInt($("#buyNum").val());
		if(b_num < 100){
			$("#buyNum").val(b_num+1);
		}
	});
	
	//监听购买数量 1-200间
	$("#buyNum").bind('input propertychange', function() {
		var $this= $(this);
		var b_num = $this.val();
		var reg = new RegExp("^[0-9]*$");
		//console.log(reg.test(b_num));
		if(reg.test(b_num)){
			b_num = parseInt($this.val());
			if(b_num > 100){
				$this.val("100");
				alertTips("单款最多购买100件","2000");
			}
			if(b_num < 1){
				$this.val("1");
			}
		}else{
			alertTips("输入格式错误","2000");
			$this.val("1");
		}
	});
	
	
	//商品列表 喜欢、不喜欢
	$("#product-list .pl-like").click(function(){
		var $this = $(this);
		var num = parseInt($this.find("i").text());
		
		if($this.hasClass("like")){
			$this.removeClass("like");
			$this.find("i").text(num-1); //取消喜欢
		}else{
			$this.addClass("like");
			$this.find("i").text(num+1); //喜欢
		}
	});
	
	
});