// JavaScript Document
var commonSteward = {
	//获取数据
	getDataAjax: function(url, data, opts){
		$.ajax({
			type: "post",
			url: url,
			data: data,
			timeout: opts.timeout || 30000,
			dataType: 'json',
			success: function(result) {
				if (typeof opts.success == 'function') {
					opts.success(result);
				}
			},
			error: function () {
				$.DIC.dialog({content: '\u670d\u52a1\u7e41\u5fd9\uff0c\u8bf7\u7a0d\u5019\u518d\u8bd5\uff01', autoClose: true});
			}
		});
	},
	//获取URL参数值
	getUrlParam: function(name){
		var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
		var r = window.location.search.substr(1).match(reg);
		if (r!=null) return unescape(r[2]); 
		return null;
	},
	//获取默认配送地址
	getDefaultAddress: function(id, url){		
		var opts = {
			'success': function(re) {
				var data = re.data,
					html = '';
				//console.log(data);
				html = data.province + data.city + data.county + data.detail;
				
				$('#' + id).html(html).attr('aid', data.id);
			}
		};
		
		commonSteward.getDataAjax(url,null,opts);
	},
	//购物车页面获取默认配送地址
	getCartDefaultAddress: function(url){		
		var opts = {
			'success': function(re) {
				var data = {},
					html = '';
					
				if(!$.DIC.isObjectEmpty(re.data)){
					data['address'] =  re.data;
				} else {
					data['address'] = null;
				}
				
				html = baidu.template('address-cont-template',data);
				$('#address-cont').append(html);
			}
		};
		
		commonSteward.getDataAjax(url,null,opts);
	},
	lazyLoadImg: function(id){
		$('#' + id).find('img').each(function(i, e) {
            var _this = $(e),
				_src = _this.attr('src') || 0,
				_data_original =  _this.attr('data-original') || 0;
			
			if(_src != _data_original){
				_this.lazyload({
					placeholder:"http://static.51jhome.com/statics/default/images/poll/pet/grey.gif"
				});	
			}
        });
	}
}