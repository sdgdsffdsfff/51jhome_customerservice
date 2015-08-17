var Timer,
	LeftTimer,
	AllTimer; 
var _config = {
	grid:4, //4*4宫格
	countTime:2, //记忆时间
	gameTime:10, //单局游戏时间
	diamond_score:20000, //钻石积分
	gold_score:5000 //没关积分
}
var MyStorage = storage('RobGold');
var ArryNum = []; //0,金币 1,钻石 2,炸弹 3,问号钻石
var ClickIndex = 0,IsClick = false,Level = 0,AllScore = 0,L_AllScore = 0,Num = 0,
	$obj={
		c_box: $("#container"),
		b_box: $("#box"),
		l_box: $("#load-data"),
		ct_box: $("#c_time"),
		lt_box: $("#l_time"),
		c_time: $("#count-down"),
		l_time: $("#left-time"),
		g_num: $("#gold-num"),
		g_result: $("#game-result")
	},
	shareData = {  
		"imgUrl": gameShareImg, 
		"timeLineLink": gameShareUrl,
		"tTitle": "日进斗金不是梦，测测你的抢钱指数！",
		"tContent": "别说你不爱钱！想做有钱的土豪还是炸惨的小黑？使劲戳吧！"
	};

function init(){
	var c_width = $obj.c_box.width();
	$obj.b_box.css({width:c_width+'px',height:c_width+'px'});
	$(window).resize(function(){
		c_width = $obj.c_box.width();
		$obj.b_box.css({width:c_width+'px',height:c_width+'px'});
	});
	var grid = _config.grid*_config.grid, html = "", i=0;
	for(i=0;i<grid;i++){
		html += "<span class='item'><i class='i-logo'></i></span>";
	}
	$obj.b_box.html(html);
}

//获取数据,进入下一关
function next(){
	$obj.l_box.show();
	ArryNum = getArry(Level);
	
	if(Level<=15){
		_config.countTime=2; //记忆时间
		_config.gameTime=10; //单局游戏时间
	}else if(Level>15&&Level<=25){
		_config.gameTime=7;
	}else if(Level>25&&Level<=35){
		_config.countTime=1;
		_config.gameTime=5;
	}else{
		_config.gameTime=3;
	}

	if(ArryNum){
		$obj.l_box.hide();
		openALlcard();
		ClickIndex = 0;
		window.clearInterval(Timer);
		var time = _config.countTime ;
		$obj.ct_box.show();
		$obj.lt_box.hide();
		$obj.c_time.text(time);
		
		Timer = window.setInterval(function(){
			time--;
			$obj.c_time.text(time);
			if(time == 0){
				closeALlcard();
				window.clearInterval(Timer);
			}
		},1000);
		$obj.g_num.text(AllScore);
	}
}

//翻开剩下所有卡片
function openLeftcard(){
	window.clearInterval(LeftTimer);		 
	$obj.b_box.find('span').each(function(index){
		var _this = $(this);
		if(_this.hasClass('clicked')){
			return;	
		}
		_this.text('');
		_this.css({'background':'#fff'}).html('<em class="gold_'+ArryNum[index]+'"></em>');
	});
	IsClick = false;
}

//翻开所有卡片
function openALlcard(){
	window.clearInterval(LeftTimer);
	$obj.l_time.text(_config.gameTime);
	$obj.b_box.find('span').each(function(index){
		var _this = $(this);
		_this.css({'background':'#fff'}).html('<em class="gold_'+ArryNum[index]+'"></em>');
	});
	IsClick = false;
	Num = $obj.b_box.find('span').size() - $obj.b_box.find('.gold_2').size();
}

//关闭所有卡片
function closeALlcard(){
	$obj.b_box.find('span').each(function(index){
		var _this = $(this);
		if(_this.hasClass('clicked')){
			_this.removeClass('clicked');
		}
		_this.html("<i class='i-logo'></i>");
	});
	IsClick = true;
	window.clearInterval(LeftTimer);
	var gameTime = _config.gameTime;
	$obj.ct_box.hide();
	$obj.lt_box.show();
	LeftTimer = window.setInterval(function(){
		gameTime--;
		$obj.l_time.text(gameTime);
		if(gameTime == 0){
			openALlcard();
			window.setTimeout(function(){gameOver();},500);
		}
	},1000);
}

//游戏结束后弹出框
function gameOver(){
	//showTips("Game Over");
	$("#advert-bottom").fadeIn(300);
	shareData.tTitle = "日进斗金不是梦，测测你的抢钱指数！";
	if (0 == shareData.tTitle) {
		shareData.tContent = shareData.tTitle;
	} else {
		var b = parseInt(Math.sqrt(10000 * AllScore / 1000000));
		99 < b && (b = "99.9");
		shareData.tTitle = "我每分钟赚了" + AllScore + "桶金！击败" + b + "%的人，明天我就是" + (100000 > AllScore ? "犀利哥" : 20000 > AllScore ? "包租婆" : 300000 > AllScore ? "宋卫平" : 400000 > AllScore ? "刘永好" : 550000 > AllScore ? "宗庆后": 700000 > AllScore ? "王健林": 850000 > AllScore ? "李嘉诚": 1000000 > AllScore ? "沃伦•巴菲特" : '比尔盖茨') + "！";
		shareData.tContent = "亲，快跟我一起来抢金币吧！"
	}
	$("#game-result").text(shareData.tTitle);
	$(".restart-box").show();
	
	//Game Over clear storage
	MyStorage.set('level','');
	MyStorage.set('score','');
}

$(function(){
	var onClick = "ontouchstart" in document.documentElement ? "touchstart" : "click";
	$obj.b_box.on(onClick,'span',function(){
		if(IsClick){
			var _this = $(this);
			var index = _this.index();
			if(_this.hasClass('clicked')){
				return;	
			}
			if(ArryNum[index] == "2"){
				IsClick = false;
				_this.css({'background':'#fff'}).html('<em class="boom"></em>').addClass("clicked");
				openLeftcard();
				window.setTimeout(function(){gameOver();},500);
			}else{
				var score_change = 0;
				if(ArryNum[index] == "0"){
					_this.css({'background':'#fff'}).html('<em class="gold_'+ArryNum[index]+'"></em>').addClass("clicked");
					AllScore += score_change = _config.gold_score;
				}else if(ArryNum[index] == "1"){
					_this.css({'background':'#fff'}).html('<em class="gold_'+ArryNum[index]+'"></em>').addClass("clicked");
					AllScore += score_change = _config.diamond_score;
				}else if(ArryNum[index] == "3"){
					var unknown = probability();
					_this.css({'background':'#fff'}).html('<em class="status_' + unknown.num +'"></em>').addClass("clicked");
					if('boom' == unknown.deal){
						IsClick = false;
						openLeftcard();
						window.setTimeout(function(){gameOver();},500);
					}else if('no' == unknown.deal){
						
					}else{
						//console.log(unknown.deal+"---");
						AllScore += score_change = unknown.deal;
					}
					
				}
				
				if(score_change<0){ score_change = score_change}else{ score_change = '+' + score_change}
				$.tipsBox({obj:$obj.g_num,str: score_change});
				ClickIndex++;
				if(Num == ClickIndex){
					IsClick = false;
					openLeftcard();
					Level++;
					window.setTimeout(function(){$("#next-level").click();},500);
					L_AllScore = AllScore;
					MyStorage.set('level',Level);
					MyStorage.set('score',AllScore);					
				}
			}
		}
	});
	
	$(window).load(function(){
		$("#status").fadeOut(); 
		$("#loading").delay(350).fadeOut("slow");
		
		//初始化游戏
		init();
	});
	
	//下一关
	$("#next-level").click(function(){
		$(this).hide();
		next();
	});
	//开始
	$('.start-game').click(function(){
		Level = 1,AllScore = 0;L_AllScore = 0;
		if(MyStorage.support){
			var level_last = MyStorage.get('level');
			var score_last = MyStorage.get('score');
			if(level_last){
				Level  = parseInt(level_last);
				AllScore = parseInt(score_last);
			}
		}
		$(".alert-box").hide();
		next();
		$("#advert-bottom").fadeOut(200);
	});
	//分享
	$(".share").click(function(){
		$("#share-box").fadeIn(200);
	});
	//隐藏分享弹出层
	$("#share-box").click(function(){
		$("#share-box").fadeOut(200);
	});
});

document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
	WeixinJSBridge.call('hideToolbar');
	WeixinJSBridge.on('menu:share:appmessage', function (argv) {
		WeixinJSBridge.invoke('sendAppMessage', { 
			"img_url": shareData.imgUrl,
			"img_width": "640",
			"img_height": "640",
			"link": shareData.timeLineLink,
			"desc": shareData.tContent,
			"title": shareData.tTitle
		}, function(res){(shareData.callback)();});
	});
	WeixinJSBridge.on('menu:share:timeline', function (argv) {
		WeixinJSBridge.invoke('shareTimeline', {
			"img_url": shareData.imgUrl,
			"img_width": "640",
			"img_height": "640",
			"link": shareData.timeLineLink,
			"desc": shareData.tContent,
			"title": shareData.tTitle
		}, function(res){(shareData.callback)();});
	});
}, false);

//提示框
function showTips(txt, time) {
	var htmlCon = '';
	if (txt != '') {
		htmlCon = '<div class="tipsBox" style="width:120px; overflow:hidden; position:fixed; left:50%; margin-left:-60px; top:50%; text-align:center; color:#FFF; padding:5px 15px; font-size:12px; background:rgba(0,0,0,.8); box-shadow:0 0 5px rgba(0,0,0,.8); z-index:100;">' + txt + '</div>';
		$('body').prepend(htmlCon);
		if (time == '' || time == undefined) {
			time = 1500;
		}
		setTimeout(function() {
			$('.tipsBox').remove();
		}, time);
	}
}

//问号钻石的随机事件
function probability(){
	var p_num = Math.floor(Math.random()*100),
		r_num = {num:0,deal:0};
		
	if(p_num >= 0 && p_num < 25){
		r_num.num = 0; //获得一个钻石 .25
		r_num.deal = _config.diamond_score;
	}else if(p_num >= 25 && p_num < 40){
		r_num.num = 1;  //两个钻石 .15
		r_num.deal = _config.diamond_score*2;
	}else if(p_num >= 40 && p_num < 45){
		r_num.num = 2; //炸死 .05
		r_num.deal = 'boom';
	}else if(p_num >= 45 && p_num < 70){
		r_num.num = 3; //剪掉一个钻石 .25
		r_num.deal = (-1)*_config.diamond_score;
	}else if(p_num >= 70 && p_num < 75){
		r_num.num = 4; //总分翻倍 .05
		r_num.deal = L_AllScore;
	}else if(p_num >= 75 && p_num < 80){
		r_num.num = 5; //总分减半 .05
		r_num.deal = L_AllScore*(-.5);
	}else{
		r_num.num = 6; //不变 .20
		r_num.deal = 'no';
	}
	return r_num;
}
//随机生成对应关数的数组
function getArry(l){
	var pl_num = Math.floor(Math.random()*100),
		max_num = 0,
		min_num = 0,
		p_num = 0, //随机数
		_arry = [];
		//console.log(pl_num);
	if(l<=10){
		max_num = 5, min_num = 1;
		if(pl_num<20) _arry.push('1');
	}else if(l>10 && l <=15){
		max_num = 7, min_num = 4;
		if(pl_num<30) _arry.push('1');
	}else if(l>15 && l <=25){
		max_num = 8, min_num = 5;
		if(pl_num<50) _arry.push('1');
	}else{
		max_num = 13, min_num = 8;
		if(pl_num<60) _arry.push('1');
	}
	p_num = Math.floor(min_num+Math.random()*(max_num-min_num));
	
	while(p_num > 0){_arry.push('0');p_num--;}
	if(l%5 == 0) _arry.push('3');
	var _length = _arry.length,length =  _config.grid*_config.grid;
	//console.log(_length + "= _length  length = "+ length + " " + _arry);
	while(_length < length){_arry.push('2');_length = _arry.length;}
	_arry.sort(function(){ return 0.5 - Math.random()});
	//console.log(l + "= LL  p_num  "+ p_num + ";--- " + _arry + "; _arry.length="+_arry.length);
	return _arry;
}
;(function($) {
	$.extend({
		tipsBox: function(options) {
			options = $.extend({
				obj: null,  //jq对象，要在那个html标签上显示
				str: "+1",  //字符串，要显示的内容;也可以传一段html，如: "<b style='font-family:Microsoft YaHei;'>+1</b>"
				startSize: "12px",  //动画开始的文字大小
				endSize: "20px",    //动画结束的文字大小
				interval: 600,  //动画时间间隔
				color: "#fff",    //文字颜色
				callback: function() {}    //回调函数
			}, options);
			$("body").append("<span class='num'>"+ options.str +"</span>");
			var box = $(".num");
			var left = options.obj.offset().left + options.obj.width() / 2;
			var top = options.obj.offset().top - options.obj.height();
			box.css({
				"position": "absolute",
				"left": left + "px",
				"top": top + "px",
				"z-index": 9999,
				"font-size": options.startSize,
				"line-height": options.endSize,
				"color": options.color
			});
			box.animate({
				"font-size": options.endSize,
				"opacity": "0",
				"top": top - parseInt(options.endSize) + "px"
			}, options.interval , function() {
				box.remove();
				options.callback();
			});
		}
	});
})(jQuery);
