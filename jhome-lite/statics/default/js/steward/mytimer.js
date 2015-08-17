//计时器
(function($){
	$.fn.mytimer=function(options){
		var settings={countDown: false};
		if(options){$.extend(settings,options);}
		var data="";
		var _DOM=null;
		var TIMER;
		createdom =function(dom){
			_DOM = dom;
			data = $(dom).attr("data");
			data = data.replace(/-/g,"/");
			data = Math.round((new Date(data)).getTime()/1000);
			$(_DOM).html("<span class='mytimerday'></span> : <span class='mytimerhour'></span> : <span class='mytimermin'></span> : <span class='mytimersec'></span>")
			reflash();
		};
		reflash=function(){
			var	range  	= data - Math.round((new Date()).getTime()/1000),
				secday  = 86400,
				sechour = 3600,
				days 	= parseInt(range/secday),
				hours	= parseInt((range%secday)/sechour),
				min		= parseInt(((range%secday)%sechour)/60),
				sec		= ((range%secday)%sechour)%60;
				
			$(_DOM).find(".mytimerday").html(nol(days));
			$(_DOM).find(".mytimerhour").html(nol(hours));
			$(_DOM).find(".mytimermin").html(nol(min));
			$(_DOM).find(".mytimersec").html(nol(sec));
	
		};
		TIMER = setInterval(reflash,1000);
		nol = function(h){
			if(settings.countDown && h < 0){
				h = 0;
			}
			h = Math.abs(h);
			return h > 9 ? h : '0' + h;
		}
		return this.each(function(){
			var $box = $(this);
			createdom($box);
		});
	}
})(jQuery);