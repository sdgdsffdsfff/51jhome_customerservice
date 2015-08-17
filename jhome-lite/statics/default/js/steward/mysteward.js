// JavaScript Document
var scrollContent = null, //滚动条
	scrollProduct = null,
	productStorageCurrent = [], //商品存储
	dateDataObj = null,
	productLastNum = 0,
	productLastPrice = 0,
	storageName = 'JieLinGongShe_' + vid, //本地存储name
	storageNameUnite = 'JieLinGongShe_unite_' + vid, //拼单本地存储name
	productsPage = {}, //{'tid': '12', 'p': 1}
	isHasPresell = false, //是否含有预售商品
	last_click_time = new Date().getTime(),
	userAgent = navigator.userAgent,
	isAndroid = userAgent.match(/(Android)\s+([\d.]+)/),
	isUnite = false; //是否是拼单
	
var mysteward = {
	init: function(){
		//商品图片 显示 隐藏
		$('#toggle-product-img').on('click',function(){
			var _this = $(this),
				product_box = $('#product-box');
				
			if(_this.hasClass('hideimg-icon')){
				_this.addClass('showimg-icon').removeClass('hideimg-icon');
				product_box.find('.product-img').hide();
				product_box.find('.tag-sx-t').show();
				isShowImg = false;
			}else{
				_this.addClass('hideimg-icon').removeClass('showimg-icon');
				product_box.find('.product-img').show();
				product_box.find('.tag-sx-t').hide();
				isShowImg = true;
			}
			return false;
		});
		//商品类型切换
		$('.serve-side-tab').on('click','.sst-item',function(){
			var _this = $(this),
				type = _this.data('type'),
				typeObj = $('#' + type),
				typeId = _this.attr('tid'),
				boxObj = $('#product-box'),
				loadObj = $('.product-box .loading-data'),
				pullUp = $('#pullUp');
				
			_this.addClass('sst-item-on').siblings().removeClass('sst-item-on');
			
			//商品类型切换
			var p_ishave = typeObj.size() || 0; //判断页面是否有该节点
			if(p_ishave < 1){
				$.DIC.dialog({content:'\u83b7\u53d6\u6570\u636e\u4e2d\u002e\u002e\u002e',autoClose: false}); //数据加载中...
				var url = getProductUrl + '?tid=' + typeId;
				
				var opts = {
					'success': function(re) {
						var data = re.data,
							html_product = '';
						data['product_type'] = typeId;
						html_product = baidu.template('product-box-template',data);
						boxObj.find('ul').hide();
						$('#product-box').append(html_product);
						$.DIC.dialog({content:'\u83b7\u53d6\u6570\u636e\u4e2d\u002e\u002e\u002e',autoClose: 10}); //数据加载中...
						scrollProduct.refresh();
						$('#' + type).find('li').size() > 0 ? pullUp.show() : pullUp.hide();
						//设置页面上次已选商品数量
						mysteward.setGoodsNum();
					}
				};
				mysteward.getDataAjax(url, null, opts);
			}else{
				typeObj.show().siblings().hide();
				scrollProduct.refresh();
			}
			pullUp.text('\u4e0a\u62c9\u67e5\u770b\u66f4\u591a'); //上拉查看更多
			return false;
		});
		
		//商品 增加 和 减少
		$(document).on('click','#product-box .btn-handle',function(e){
			var _this = $(this),
				id = _this.parents('li').attr('id'),
				type = _this.data('type'),
				click_time = new Date().getTime();

			if(isAndroid && click_time && (click_time - last_click_time) < 500){
				return false;
			}
			
			if('plus' == type) mysteward.addFood(id);
			if('minus' == type) mysteward.reduceFood(id);
			
			last_click_time = click_time;
			return false;
		});
		
		//已选商品 增加 和 减少
		$(document).on('click','#selected-food-list .btn-handle',function(e){
			var _this = $(this),
				parents_li = _this.parents('li'),
				parents_ul = _this.parents('ul'),
				pid = parents_li.attr('pid'), //商品id
				shopid = parents_ul.attr('shop-id'),  //商店id
				typeid = parents_li.attr('type-id'), //商品类型
				storage = parseInt(parents_li.attr('storage')), //库存
				pm_obj = _this.siblings('.pm-num'),
				all_num = parseInt(pm_obj.text()) || 0,
				p_obj = $('#products-' + pid) || false,
				t_obj = $('li[data-type="product-type-'+ typeid +'"]') || false,
				type = _this.data('type'),
				click_time = new Date().getTime();

			if(isAndroid && click_time && (click_time - last_click_time) < 500){
				return false;
			}
			
			if('minus' == type && all_num > 0 ) {
				all_num--;	
				if(all_num <= 0){
					var li_num = parents_ul.find('li').size();
					li_num > 1 ? parents_li.remove(): parents_ul.remove();
					if($('#selected-food-list').find('li').size() <= 0) {
						mysteward.hideCart();
						mysteward.setGoodsNum();
					}
					//商品数量显示改变
					if(p_obj){
						p_obj.removeClass('selected-item');
						p_obj.find('.pm-num').text(0).hide();
						p_obj.find('[data-type="minus"]').hide().removeClass('btn-handle-on');
						p_obj.find('[data-type="plus"]').removeClass('btn-handle-on');
						//类型数量显示改变
						if(t_obj){
							var em = t_obj.find('em');
								t_num = parseInt(em.text()) - 1;
								
							t_num > 0 ? em.text(t_num) : em.text(0).hide();
						}
					}
					scrollContent.refresh(); //商品减少后滚动条刷新
				}
			} else if('plus' == type) {
				if(all_num < storage){
					all_num++;
				}else{
					$.DIC.dialog({content:'\u5bf9\u4e0d\u8d77\uff0c\u5e93\u5b58\u4e0d\u8db3\uff01',autoClose: true}); //对不起，库存不足！
				}
			};
			
			var is_true = mysteward.setProductStorage({'shopid': shopid, 'pid': pid, 'num': all_num});
			if(is_true) pm_obj.text(all_num);
			last_click_time = click_time;
			return false;
		});
		
		//购物车内商品 增加 和 减少
		$(document).on('click','#cart-product-box .btn-handle',function(){
			var _this = $(this),
				parents_li = _this.parents('li'),
				parents_ul = _this.parents('ul'),
				_unite = _this.parents('.unite-item'),
				p_id = parents_li.attr('pid'), //商品id
				type = _this.data('type'),
				pm_obj = _this.siblings('.pm-num'),
				storage = parseInt(parents_li.attr('storage')),
				pm_num = parseInt(pm_obj.text()) || 0,
				shop_id = _this.parents('ul').attr('shop-id'),
				shop_name = _this.parents('ul').attr('shop-name'),
				num_c = 0;
			
			if('plus' == type){
				if(pm_num < storage){
					num_c = pm_num+1;
					if(pm_num == 0) _this.addClass('btn-handle-on').siblings().show();
				}else{
					$.DIC.dialog({content:'\u5bf9\u4e0d\u8d77\uff0c\u5e93\u5b58\u4e0d\u8db3\uff01',autoClose: true}); //对不起，库存不足！
					return;
				}
				setStorage();
			}
			
			if('minus' == type){
				num_c = pm_num-1;
				var cartObj = $('#cart-product-box'),
					li_size = cartObj.find('li').size();
					
				if(num_c <= 0){
					$.DIC.dialog({
						content: "<font style='font-size:18px; display:block; color:#999;'>\u786e\u5b9a\u8981\u5220\u9664\u8be5\u5546\u54c1</font>",
						okValue: '\u786e\u5b9a',
						cancelValue: '\u53d6\u6d88',
						isMask: true,
						ok: function() {
							_setPage(li_size);
						},
						cancel: function() {
							return false;
						}
					});
				}else{
					setStorage();
				}
			}
			
			function _setPage(size){
				if(size <=1){
					localStorage.removeItem(storageName);
					localStorage.removeItem(storageNameUnite);
					window.location.href = PRODUCTLIST_URL;
					return false;
				}else{
					var li_num = parents_ul.find('li').size();
					li_num > 1 ? parents_li.remove(): parents_ul.remove();
					if(isUnite && _unite.find('ul').size() < 1){
						_unite.remove();
					}
					//预售商品删除，重新设置配送日期、时间
					if(isHasPresell && cartObj.find('.tag-sx-y').size() <= 0){
						mysteward.setDeliverdate();
						$.DIC.dialog({content:'\u8bf7\u91cd\u65b0\u9009\u62e9\u914d\u9001\u65f6\u95f4',autoClose: 2000}); //请重新选择配送时间
						$('#presell-tag').hide();
						isHasPresell = false;
					}
				}
				setStorage();
			}
			
			function setStorage(){
				var s_obj = {'shopid': shop_id, 'pid': p_id, 'num': num_c},
				is_true = false;
				if(isUnite){
					var uid = _unite.attr('uid');
					s_obj['uid'] = uid;
					is_true = mysteward.setUniteProductStorage(s_obj);
				}else{
					is_true = mysteward.setProductStorage(s_obj);
				} 
				if (is_true) pm_obj.text(num_c);
			}
			return false;
		});
		
		//查看已选择商品
		$('#look-selected').on('click',function(){
			var mask = $('#big-mask'),
				sf_box = $('#selected-food-box'),
				w_height = $(window).height(),
				dataStorage = productStorageCurrent || '';
		
			if(dataStorage){
				if(mask.css('display') == 'none'){
					var html = "",
						data = {};
						
					data['datas'] = dataStorage;
					html = baidu.template('selected-food-list-template',data);
					$('#selected-food-list').html(html);
					
					mask.css('z-index','98').show();
					sf_box.show();
					
					if(scrollContent != null){
						scrollContent.refresh();
					}else{
						$('#scroll-box').height(w_height*0.5);
						scrollContent = new iScroll('scroll-box',{vScrollbar:false});
					}
					mysteward.touchPrevent();
				}else{
					mask.hide();
					sf_box.hide();
					mysteward.setGoodsNum(); 
					mysteward.touchDefault();
				}
			}
			return false;
		});
		
		//商品详情确认添加
		$(document).on('click','#product-info-box .add-btn-ok',function(){
			$('#big-mask').hide();
			$('#product-info-box').hide();
			mysteward.touchDefault();
			mysteward.setGoodsNum();
			return false;
		});
		
		//关闭商品详情框
		$(document).on('click','#product-info-box .close-item',function(){
			$('#big-mask').hide();
			$('#product-info-box').hide();
			mysteward.touchDefault();
			mysteward.setGoodsNum();
			return false;
		});
		
		//点击黑色遮罩层 关闭所有弹出层			
		$('#big-mask').on('click',function(){
			$(this).hide();
			$('#selected-food-box').hide();
			$('#product-info-box').hide();
			mysteward.touchDefault();
			mysteward.setGoodsNum();
			return false;
		});
	},
	//添加商品
	addFood: function(id){
		var _this = $('#'+id),
			add_btn = _this.find('[data-type="plus"]'),
			reduce_btn = _this.find('[data-type="minus"]'),
			num_obj = _this.find('.pm-num'),
			goodsInfo = _this.find('.product-desc').text(); //商品信息
			p_id = _this.attr('pid'), //商品id
			type = _this.attr('type-id'); // 商品类型
			pm_num = parseInt(num_obj.text()) || 0,
			storage = parseInt(_this.attr('storage')),
			parent_ul_id = _this.parents('ul').attr('id'),
			em = $('li[data-type="'+ parent_ul_id +'"]').find('em'),
			em_num = parseInt(em.text()) || 0,
			shop_id = shopId || false;

			if(pm_num < storage){
				if(pm_num == 0){
					add_btn.addClass('btn-handle-on');
					reduce_btn.addClass('btn-handle-on').show();
					num_obj.show();
					_this.addClass('selected-item');
					em.text(em_num+1).show();
					mysteward.firstProductStorage(goodsInfo,type);
				}else{
					var is_true = mysteward.setProductStorage({'shopid': shop_id, 'pid': p_id, 'num': pm_num+1});
					if(!is_true) return false;
				}
				num_obj.text(pm_num+1);
				return true;
			}else{
				$.DIC.dialog({content:'\u5bf9\u4e0d\u8d77\uff0c\u5e93\u5b58\u4e0d\u8db3\uff01',autoClose: true}); //对不起，库存不足！
			}
	},
	//减少商品
	reduceFood: function(id){
		var _this = $('#'+id),
			add_btn = _this.find('[data-type="plus"]'),
			reduce_btn = _this.find('[data-type="minus"]'),
			num_obj = _this.find('.pm-num'),
			goodsInfo = _this.find('.product-desc').text(); //商品信息
			p_id = _this.attr('pid'), //商品id
			pm_num = parseInt(num_obj.text()) || 0,
			parent_ul_id = _this.parents('ul').attr('id'),
			em = $('li[data-type="'+ parent_ul_id +'"]').find('em'),
			em_num = parseInt(em.text()) || 0,
			shop_id = shopId || false;	
		
		num_obj.text(pm_num-1);
		if(pm_num <= 1){
			reduce_btn.hide().siblings().removeClass('btn-handle-on');
			_this.removeClass('selected-item');
			num_obj.hide();
			em_num > 1 ? em.text(em_num-1) : em.text(0).hide();
		}
		mysteward.setProductStorage({'shopid': shop_id, 'pid': p_id, 'num': pm_num-1});
	},
	//底部购物车显示 obj：'cart'
	getSelectedFood: function(){
		mysteward.setSelectedProduct();
	},
	//获取更多产品
	getMoreProduct: function(){
		var id = $('#serve-side-tab').find('.sst-item-on').attr('tid');
		
		var PageNum = productsPage[id] || 1;
		productsPage[id] = PageNum + 1;
		
		var opts = {
			'success': function(re) {
				var data = re.data,
					html = '';
				
				data['product_type'] = id;
				html = baidu.template('product-more-template',data);
				$('#product-type-' + id).append(html);
				scrollProduct.refresh();
				data.item.length <= 0 ? $('#pullUp').text('\u6ca1\u6709\u66f4\u591a\u5546\u54c1') : $('#pullUp').text('\u4e0a\u62c9\u67e5\u770b\u66f4\u591a'); //没有更多商品, //上拉查看更多
				mysteward.setGoodsNum();
			}
		};
		mysteward.getDataAjax(getProductUrl, {'tid': id, 'p': productsPage[id]}, opts); //tid: 商品类别， p:页数
	
	},
	//初始化商品列表页面
	initProductPage: function(){	
		var opts = {
			'success': function(re) {
				var data = re.data,
					html_product = '',
					html_tab = '',
					w_height = $(window).height(),
					scrollTab = null
					pullUpEl = $('#pullUp'),
					pullUpOffset = pullUpEl.height();
					
				//商品类别
				html_tab = baidu.template('serve-side-tab-template',data);
				$('#serve-side-tab').html(html_tab).height(w_height - 130);
				scrollTab = new iScroll('serve-side-tab',{vScrollbar: false, hideScrollbar: true});
				//产品列表
				data['product_type'] = data.cate[0].id;
				html_product = baidu.template('product-box-template',data);
				$('#product-box').html(html_product);
				pullUpEl.show();
				$('.loading-data').hide();
				//mysteward.imgLazyLoad();
				$('#product-iscroll').height(w_height - 130);
				scrollProduct = new iScroll('product-iscroll',{
					scrollbarClass: "myScrollbar",
					onRefresh: function () {
						if (pullUpEl.hasClass('isloading')) {
							pullUpEl.attr('class','').html('\u4e0a\u62c9\u67e5\u770b\u66f4\u591a'); //上拉查看更多
						}
					},
					onScrollMove: function () {
						if (this.y < (this.maxScrollY - 5) && !pullUpEl.hasClass('flip')) {
							pullUpEl.attr('class','flip').html('\u653e\u5f00\u5237\u65b0'); //放开刷新
							this.maxScrollY = this.maxScrollY;
						} else if (this.y > (this.maxScrollY + 5) && pullUpEl.hasClass('flip')) {
							pullUpEl.attr('class','').html('\u4e0a\u62c9\u67e5\u770b\u66f4\u591a'); //上拉查看更多
							this.maxScrollY = pullUpOffset;
						}
					},
					onScrollEnd: function () {
						if (pullUpEl.hasClass('flip')) {
							pullUpEl.attr('class','isloading').html('<span class="loading"></span>');
							mysteward.getMoreProduct(); //上拉刷新，查看更多产品
						}
					}
				});
				//获取已选商品
				mysteward.getLocalStorage();
				//设置购物车			
				mysteward.setSelectedProduct();
				//设置页面上次已选商品数量
				mysteward.setGoodsNum();
			}
		};
		mysteward.getDataAjax(getProductUrl, {'list':1,'tid':tid}, opts);
		
		//商品详情 操作
		$(document).on('click','#product-info-box .btn-handle',function(){
			var _this = $(this),
				type = _this.data('type'),
				pm_obj = _this.siblings('.pm-num'),
				pm_num = parseInt(pm_obj.text()) || 0,
				storage = parseInt(pm_obj.attr('storage')) || 0,
				for_id = pm_obj.data('for'),
				num_c = 0,
				pi_obj = $('#product-info-box');
			
			if('plus' == type){
				if(pm_num < storage){
					num_c = pm_num+1;
					var is_true = mysteward.addFood(for_id);
					if(is_true) pm_obj.text(num_c);
				}else{
					$.DIC.dialog({content:'\u5bf9\u4e0d\u8d77\uff0c\u5e93\u5b58\u4e0d\u8db3\uff01',autoClose: true}); //对不起，库存不足！
					return;
				}
			}
			
			if('minus' == type){
				num_c = pm_num-1;
				pm_obj.text(num_c);
				if(num_c <= 0){
					pi_obj.find('.product-handle').hide();
					pi_obj.find('.add-btn').show();
					pi_obj.find('.add-btn-ok').hide();
				}
				mysteward.reduceFood(for_id);
			}
			return false;
		});
		
		//查看商品详情
		$(document).on('click','.product-info',function(){	
			var _this = $(this),
				p_li = _this.parents('li'),
				p_id = p_li.attr('id'),
				p_num = p_li.find('.pm-num').text(),
				p_info = p_li.find('.product-desc').text() || '';
				
			var _data = JSON.parse(p_info);
			_data['p_num'] = p_num;
			_data['p_id'] = p_id;
			
			mysteward.showProductInfo(_data,'product-info-box');
			
			return false;
		});
		
		//加入菜单
		$(document).on('click','#product-info-box .add-btn',function(){
			var _this = $(this),
				for_id = _this.data('for');
			_this.hide();
			$('#product-info-box .product-handle').show();
			$('#product-info-box .add-btn-ok').show();
			$('#product-info-box .pm-num').text('1');
			mysteward.addFood(for_id);
			
			return false;
		});
	},
	//初始化搜索页面
	initSearchPage: function(){
		var img_width = 0,
			goodsPage = 0,
			totalsPage = 0;
		//获取已选商品
		mysteward.getLocalStorage();
		//设置购物车			
		mysteward.setSelectedProduct();	
		initPage();
		
		function initPage(){
			var w_width = $(window).width(),
				w_height = $(window).height();
			
			$('#search-text').val('');			
			$('#product-iscroll').height(w_height - 90);
			w_width > 640 ? w_width = 640 : w_width = w_width;
			img_width = parseInt(w_width*0.456) - 20;
			
			var value = mysteward.getUrlParam('q');
			if(value) setPage(value);
		}
		
		function setPage(value){
			var load_obj = $('#loading-data'),
				product_obj = $('#searched-product');
			var opts = {
				'success': function(re) {
					var s_html = '',
						s_data = re.data,
						totalsPage = re.data.totals;
						pullUpEl = $('#pullUp'),
						pullUpOffset = pullUpEl.height();
		
					if(img_width) s_data['height'] = img_width;
					s_html = baidu.template('searched-product-template',s_data);
					product_obj.html(s_html);
					s_data.item.length > 0 ? pullUpEl.show() : pullUpEl.hide();
					load_obj.hide();
					if(scrollProduct == null){
						scrollProduct = new iScroll('product-iscroll',{
							scrollbarClass: "myScrollbar",
							onRefresh: function () {
								if (pullUpEl.hasClass('isloading')) {
									pullUpEl.attr('class','').html('\u4e0a\u62c9\u67e5\u770b\u66f4\u591a'); //上拉查看更多
								}
							},
							onScrollMove: function () {
								if (this.y < (this.maxScrollY - 5) && !pullUpEl.hasClass('flip')) {
									pullUpEl.attr('class','flip').html('\u653e\u5f00\u5237\u65b0'); //放开刷新
									this.maxScrollY = this.maxScrollY;
								} else if (this.y > (this.maxScrollY + 5) && pullUpEl.hasClass('flip')) {
									pullUpEl.attr('class','').html('\u4e0a\u62c9\u67e5\u770b\u66f4\u591a'); //上拉查看更多
									this.maxScrollY = pullUpOffset;
								}
							},
							onScrollEnd: function () {
								if (pullUpEl.hasClass('flip')) {
									pullUpEl.attr('class','isloading').html('<span class="loading"></span>');
									getMoreGoods(value); //上拉刷新，查看更多产品
								}
							}
						});
					}else{
						scrollProduct.refresh();
						scrollProduct.scrollTo(0,0,0,false)
					}
					//还原已选商品数量
					mysteward.setGoodsNum();
					goodsPage = 1;
				}
			};
			load_obj.show();
			product_obj.html('');
			mysteward.getDataAjax(getProductUrl, {'q': value}, opts); //q: 搜索内容
		}
		
		function getMoreGoods(value){
			var url = getProductUrl,
				pull_obj = $('#pullUp');
			
			var PageNum = goodsPage || 1;
			PageNum++;
			
			var opts = {
				'success': function(re) {
					var data = re.data,
						html = '';
					
					if($.DIC.isObjectEmpty(data.item)){
						pull_obj.html('\u6ca1\u6709\u66f4\u591a\u5546\u54c1'); //没有更多商品
					}else{
						if(img_width) data['height'] = img_width;
						html = baidu.template('product-more-template',data);
						$('#searched-list').append(html);
						scrollProduct.refresh();
						pull_obj.html('\u4e0a\u62c9\u67e5\u770b\u66f4\u591a'); //上拉查看更多
						pull_obj.html('\u6ca1\u6709\u66f4\u591a\u5546\u54c1'); //没有更多商品
						mysteward.setGoodsNum();
					}
					goodsPage = PageNum;
				}
			};
			totalsPage = 2;
			if(PageNum <= totalsPage){
				mysteward.getDataAjax(url, {'q': value, 'p': PageNum}, opts); //tid: 商品类别， p:页数	
			}else{
				scrollProduct.refresh();
				pull_obj.text('\u6ca1\u6709\u66f4\u591a\u5546\u54c1'); //没有更多商品
			}

		}
		
		//搜索
		$('#search-btn').on('click',function(){
			var val = $.trim($('#search-text').val());
			
			if(val == ''){
				$.DIC.dialog({content: '\u4eb2\uff0c\u8bf7\u8f93\u5165\u8981\u641c\u7d22\u7684\u5546\u54c1\u540d\u79f0', autoClose: true}); //亲，请输入要搜索的商品名称
				return false;
			}
			setPage(val);
			return false;
		});
		
		//弹出搜索框
		$('#search-text').on('focus',function(){
			$('#search-left').hide();
			$('#search-btn').show();
		});
		
		//弹出商品详情
		$(document).on('click','#searched-product li',function(){
			var _this = $(this),
				p_id = _this.attr('id'),
				p_num = _this.find('.pm-num').text(),
				p_info = _this.find('.product-desc').text() || '';
				
			var _data = JSON.parse(p_info);
			_data['p_num'] = p_num;
			_data['p_id'] = p_id;
			
			mysteward.showProductInfo(_data,'product-info-box');
			
			return false;
		});
		
		//加入购物车
		$(document).on('click','#product-info-box .add-btn',function(){
			var _this = $(this),
				for_id = _this.data('for'),
				g_type = $('#'+for_id). attr('type-id'); //商品类型
			_this.hide();
			$('#product-info-box .product-handle').show();
			$('#product-info-box .add-btn-ok').show();
			$('#product-info-box .pm-num').text('1');
			
			mysteward.firstProductStorage($('#'+for_id).find('.product-desc').text(),g_type);
			return false;
		});
		
		//商品详情 操作
		$(document).on('click','#product-info-box .btn-handle',function(){
			var _this = $(this),
				type = _this.data('type'),
				pm_obj = _this.siblings('.pm-num'),
				pm_num = parseInt(pm_obj.text()) || 0,
				storage = parseInt(pm_obj.attr('storage')) || 0,
				p_id = pm_obj.attr('pid'),
				num_c = 0,
				pi_obj = $('#product-info-box'),
				shopid = pm_obj.attr('shop-id');				
			
			if('plus' == type){
				if(pm_num < storage){
					num_c = pm_num+1;
					var is_true = mysteward.setProductStorage({'shopid': shopid, 'pid': p_id, 'num': num_c});
					if(is_true) pm_obj.text(num_c);
				}else{
					$.DIC.dialog({content:'\u5bf9\u4e0d\u8d77\uff0c\u5e93\u5b58\u4e0d\u8db3\uff01',autoClose: true}); //对不起，库存不足！
					return;
				}
			}
			
			if('minus' == type){
				num_c = pm_num-1;
				pm_obj.text(num_c);
				if(num_c <= 0){
					pi_obj.find('.product-handle').hide();
					pi_obj.find('.add-btn').show();
					pi_obj.find('.add-btn-ok').hide();
					$('#products-' + p_id).find('.pm-num').text(0).hide();
				}
				mysteward.setProductStorage({'shopid': shopid, 'pid': p_id, 'num': num_c});
			}
			return false;
		});
	},
	//初始化下单页面
	initCartPage: function(){
		var unite = mysteward.checkUnite();
		if(!unite) mysteward.getLocalStorage();
		if($.DIC.isObjectEmpty(productStorageCurrent)){
			var _cont = '<div style="text-align:center; padding:15px;">\u8d2d\u7269\u8f66\u7a7a\u4e86</div><a href="' + PRODUCTLIST_URL + '" class="btn-sm">\u6211\u8981\u53bb\u88c5\u6ee1</a>';
			$('#cart-product-box').css({'background':'#fff','padding-bottom':'20px'}).html(_cont); //购物车空了,//我要去装满
			return false;
		}else{
			var goods_data = {},goods_html;
			if(unite){//拼单		
				goods_data['unite'] = productStorageCurrent;
				goods_html = baidu.template('cart-product-unite-template',goods_data);

				mysteward.setUniteProductPrice();
			}else{
				goods_data['product'] = productStorageCurrent;
				goods_html = baidu.template('cart-product-box-template',goods_data);
				//设置购物车			
				mysteward.setSelectedProduct();
			}
			$('#cart-product-box').html(goods_html);
		}
		
		//设置配送时间
		mysteward.setDeliverdate();
		
		//绑定配送日期、时间事件
		$("#arrive_date").on('change',function(){
			var _this = $(this),
				_val = _this.val(),
				times = null,
				data = dateDataObj || false;
				
			if(_val == ''){
				$('#arrive_time').html('<option value=""> -- </option>');
				return false;
			}
			
			if(data){
				for(var i in data){
					var dateItem = data[i];
					if(dateItem.show == _val){
						var times = dateItem.times,
							html = '';
							
						for(var j in times){
							html += '<option value="' + times[j] + '">' + times[j] + '</option>';
						}
						$('#arrive_time').html(html);
						return;
					}
				}
			}
		});	
	},
	//初始化购物车页面
	initMyCart: function(){
		mysteward.getLocalStorage();
		if($.DIC.isObjectEmpty(productStorageCurrent)){
			var _cont = '<div style="text-align:center; padding:15px;">\u8d2d\u7269\u8f66\u7a7a\u4e86</div><a href="' + PRODUCTLIST_URL + '" class="btn-sm">\u6211\u8981\u53bb\u88c5\u6ee1</a>';
			$('#cart-product-box').css({'background':'#fff','padding-bottom':'20px'}).html(_cont); //购物车空了,//我要去装满
			return false;
		}else{
			var goods_data = {},goods_html;
					
			goods_data['product'] = productStorageCurrent;
			goods_html = baidu.template('cart-product-box-template',goods_data);
			//设置购物车			
			mysteward.setSelectedProduct();
			$('#cart-product-box').html(goods_html);
		}
	},
	//显示商品详情data:商品信息json数据,id: 显示的容器id
	showProductInfo: function(data,id){
		if(data && id){
			var product_box = $('#' + id);
			var html = baidu.template('product-info-template',data);
			product_box.html(html);
				
			var	w_width = $(window).width(),
				w_height = $(window).height(),
				imgObj = product_box.find('img'),
				max_height = w_height-200;
				
			w_width > 640 ? product_box.css({'width': 560 + 'px','margin-left':'-280px','left':'50%'}):product_box.css({'width': '80%','left':'10%','margin-left':'0'});
			imgObj.css({'max-height': max_height + 'px'});
							
			$('#big-mask').css('z-index','1000').show();
			product_box.show();
			mysteward.loadImg(imgObj);

			$('#scroll-cont').height(80);
			var scrollContent_1 = new iScroll('scroll-cont',{vScrollbar:false});
			mysteward.touchPrevent();
		}
	},
	//获取数据
	getDataAjax: function(url, data, opts){
		$.ajax({
			type: opts.type || "GET",
			url: url,
			data: data,
			timeout: opts.timeout || 30000,
			dataType: 'json',
			success: function(result) {
				if(result.status==1){
					if (typeof opts.success == 'function') {
						opts.success(result);
					}
				}else{
					$.DIC.dialog({content: result.info, autoClose: true});
				}
			},
			error: function () {
				$.DIC.dialog({content: '\u670d\u52a1\u7e41\u5fd9\uff0c\u8bf7\u7a0d\u5019\u518d\u8bd5\uff01', autoClose: true}); //服务繁忙，请稍候再试！
			}
		});
	},
	//商品列表页底部已选商品数量改变后 改变本地存储
	setProductStorage: function(obj){
		var _shopid = obj.shopid || false,
			_pid = obj.pid || false,
			_num = obj.num || 0;
			
		if(window.localStorage && _shopid && _pid){
			var storageCurrent = productStorageCurrent;
				
			for(var i in storageCurrent){
				var shopObj = storageCurrent[i];					
				if(shopObj && shopObj['shop_id'] == _shopid){
					var goods = shopObj['goods'];
					if(goods.length > 0){
						for(var j in goods){
							var goods_item = goods[j];
							if(goods_item.gid == _pid){
								
								var limit = goods_item.limit_counts || 0;
								if(limit != 0 && _num > limit){
									$.DIC.dialog({content: '\u8be5\u5546\u54c1\u9650\u8d2d' + limit + '\u4efd', autoClose: true});
									return false;
								}
								
								goods_item.num = _num;
								_num <= 0 ? goods.splice(j,1) : goods[j] = goods_item;
								if(!$.DIC.isObjectEmpty(goods)) {
									shopObj['goods'] = goods;
									storageCurrent[i] = shopObj;
								}else{
									storageCurrent.splice(i,1);
								}
								break;
							}
						}
					}
					break;
				}
			}
			//重新存储
			localStorage.setItem(storageName, JSON.stringify(storageCurrent));
			productStorageCurrent = storageCurrent;
			mysteward.getSelectedFood();
			return true;
		}
	},
	//拼单 商品数量改变后 改变本地存储
	setUniteProductStorage: function(obj){
		var _uid = obj.uid || false,
			_shopid = obj.shopid || false,
			_pid = obj.pid || false,
			_num = obj.num || 0;
			
		if(window.localStorage && _uid && _shopid && _pid){
			var storageCurrent = productStorageCurrent;
			
			for(var k in storageCurrent){
				var _unite = storageCurrent[k],
					_cart = _unite['cart'];
				if(_unite && _cart && _unite['uid'] == _uid){
					for(var i in _cart){
						var shopObj = _cart[i];					
						if(shopObj && shopObj['shop_id'] == _shopid){
							var goods = shopObj['goods'];
							if(goods.length > 0){
								for(var j in goods){
									var goods_item = goods[j];
									if(goods_item.gid == _pid){
										
										var limit = goods_item.limit_counts || 0;
										if(limit != 0 && _num > limit){
											$.DIC.dialog({content: '\u8be5\u5546\u54c1\u9650\u8d2d' + limit + '\u4efd', autoClose: true});
											return false;
										}
										
										goods_item.num = _num;
										_num <= 0 ? goods.splice(j,1) : goods[j] = goods_item;
										if(!$.DIC.isObjectEmpty(goods)) {
											shopObj['goods'] = goods;
											_cart[i] = shopObj;
											_unite['cart'] = _cart;
											storageCurrent[k] = _unite;
										}else{
											_cart.splice(i,1);
											_unite['cart'] = _cart;
											if(!$.DIC.isObjectEmpty(_unite['cart'])){
												storageCurrent[k] = _unite;
											}else{
												storageCurrent.splice(k,1);;
											}
										}
										break;
									}
								}
							}
							break;
						}
					}
					break;
				}
			}
			//重新存储
			localStorage.setItem(storageNameUnite, JSON.stringify(storageCurrent));
			productStorageCurrent = storageCurrent;
			mysteward.setUniteProductPrice();
			return true;
		}
	},
	//第一次添加商品 存储obj: 商品信息, g_type: 商品类型
	firstProductStorage: function(obj,g_type){
		var goodsObj = JSON.parse(obj) || false,
			goods_type = g_type || false,
			shop_id = shopId || false,
			shop_name = shopName || false;
		
		if(goodsObj.shop_name){
			shop_id = goodsObj.shop_id || false;
			shop_name = goodsObj.shop_name || false;
		}
		
		if(window.localStorage && goodsObj && shop_id && shop_name && goods_type){
			var storageCurrent = productStorageCurrent || false, //获取当前的存储对象
				isHave = false;
				goodsArry = [],
				shopItem = {};

			goodsObj['num'] = 1;
			goodsObj['type'] = goods_type;
			if(storageCurrent){
				for(var i in storageCurrent){
					var _item = storageCurrent[i];					
					if(_item && _item['shop_id'] == shop_id){ //是否该商店
						var goods = _item['goods']; //获取该商店商品
						goods.push(goodsObj); //新增商品信息
						_item['goods'] = goods; //改变该商店商品
						storageCurrent[i] = _item;
						isHave = true;
						break;
					}
				}
			}
			if(!isHave) { // storageCurrent 为空
				goodsArry.push(goodsObj);
				shopItem['shop_id'] = shop_id;
				shopItem['shop_name'] = shop_name;
				shopItem['goods'] = goodsArry;
				storageCurrent.push(shopItem)
			}
			//重新存储
			localStorage.setItem(storageName, JSON.stringify(storageCurrent));
			productStorageCurrent = storageCurrent;
			mysteward.getSelectedFood();
			return true;
		}
	},
	//获取上次 已选商品
	getLocalStorage: function(){
		if(window.localStorage){
			var data = JSON.parse(localStorage.getItem(storageName));
			if(!$.DIC.isObjectEmpty(data)){
				productStorageCurrent = data;
			}
		}
	},
	//检查是否拼单
	checkUnite:function(){
		if(window.localStorage){
			var expire = localStorage.getItem('unite_expire') || false; //拼单有效期
			var s_data = JSON.parse(localStorage.getItem(storageNameUnite)) || false; //拼单数据
			if(expire && s_data){
				var time = parseInt(new Date().getTime()/1000);
				if(time < expire){
					isUnite = true;
					productStorageCurrent = s_data;
					$('#unite-btn').html('\u53bb\u62fc\u5355\u9875<span class="go-right"></span>');
					return true;
				}else{
					localStorage.removeItem('unite_expire');
					localStorage.removeItem(storageNameUnite);
					return false;
				}
			}
			return false;
		}
	},
	//还原上次已选商品, 放入购物车
	setSelectedProduct: function(){
		var dataStorage = productStorageCurrent || '';
		
		if(dataStorage){
			var productNum = 0,
				productPrice = 0,
				html = '';
			for(var i in dataStorage){
				var dataItem = dataStorage[i];
				var goods = dataItem.goods;
				for(var j in goods){
					var goods_item = goods[j],
						num = parseInt(goods_item.num) || 0,
						price = parseFloat(goods_item.price) || 0;
						
					productNum += num;
					productPrice += price * num;
				}
			}
			if(productNum > 0){
				productLastNum = productNum;
				productLastPrice = productPrice.toFixed(2);
				$('#food-num').text(productLastNum);
				$('#money-total-c').text(productLastPrice);
				$('.order-info').show();
			}else{
				mysteward.hideCart();
			}
		}
	},
	//拼单 购物车商品总价格(初始化页面时)
	setUniteProductPrice: function(){
		var dataStorage = productStorageCurrent || '';
		
		if(dataStorage){
			var productNum = 0,
				productPrice = 0;
				
			for(var i in dataStorage){
				var dataItem = dataStorage[i].cart;
				for(var k in dataItem){
					var goods = dataItem[k].goods;
					for(var j in goods){
						var goods_item = goods[j],
							num = parseInt(goods_item.num) || 0,
							price = parseFloat(goods_item.price) || 0;
							
						productNum += num;
						productPrice += price * num;
					}
				}
			}
			
			if(productNum > 0){
				var last_price = productPrice.toFixed(2);
				$('#money-total-c').text(last_price);
			}
		}
	},
	//设置上次已选商品数量
	setGoodsNum: function(){
		var dataStorage = productStorageCurrent || '',
			shopStorage = '';
			
		if(dataStorage){
			for(var i in dataStorage){
				var _item = dataStorage[i];
				var _goods = _item.goods,
					typeArry = {};
				for(var j in _goods){
					var goods_item = _goods[j],
						gid = goods_item.gid,
						num = goods_item.num,
						type = goods_item.type;
							
					var obj_li = $('#products-' + gid);
					obj_li.addClass('selected-item');
					obj_li.find('.btn-handle').addClass('btn-handle-on').show();
					obj_li.find('.pm-num').text(num).show();
					
					if (typeArry[type] === undefined) {
						typeArry[type] = 1;
					} else {
						typeArry[type]++;
					}
				}
				for(var r in typeArry){
					$('li[data-type="product-type-'+ r +'"]').find('em').text(typeArry[r]).show();
				}		
			}	
		}
	},
	//设置配送时间
	setDeliverdate: function(){	
		if($.DIC.isObjectEmpty(dateDataObj)){
			var url = GETDATE_URL || false;
			var opts = {
				'success': function(re) {
					var data = re.data;
					
					dateDataObj = data;
					_setDate(data);
				}
			};
			
			if(url) mysteward.getDataAjax(url,null,opts);
		}else{
			_setDate(dateDataObj);
		}
		
		function _setDate(obj){
			var data = obj || false,
				date_html = '',times_html = '';
			
			if(data){	
				if($('#cart-product-box').find('.tag-sx-y').size() > 0 && data.length >= 3){ //含有预售商品				
					for(var i in data){
						if( i > 0 ){
							date_html += '<option value="' + data[i].show + '">' + data[i].desc + '&nbsp;&nbsp;' + data[i].show + '</option>';
						}
					}
					var times = data[1].times;
					for(var j in times){
						times_html += '<option value="' + times[j] + '">' + times[j] + '</option>';
					}
					isHasPresell = true; //含有预售商品
				}else{ //没有预售商品
					for(var i in data){
						date_html += '<option value="' + data[i].show + '">' + data[i].desc + '&nbsp;&nbsp;' + data[i].show + '</option>';
					}
					var times = data[0].times;
					for(var j in times){
						times_html += '<option value="' + times[j] + '">' + times[j] + '</option>';
					}
				}
				$('#arrive_date').html(date_html);
				$('#arrive_time').html(times_html);
			}
		}
	},
	//隐藏购物车
	hideCart: function(){
		$('.order-info').hide();
		$('#big-mask').hide();
		$('#selected-food-box').hide();
		$('#product-info-box').hide();
		mysteward.touchDefault();
	},
	loadImg: function(obj){
		var url = obj.attr('data-src');
		var img = new Image();
		img.src = url;
		if(img.complete)
		{
			obj.attr('src',url).css('height','auto');
			return;
		}
		img.onload =function(){
			obj.attr('src',url).css('height','auto');
		}
	},
	touchPrevent: function(){
		document.ontouchmove = function(e) {
			e.preventDefault();
		}
	},
	touchDefault: function(){
		document.ontouchmove = function(e) {
			return true;
		}
	},
	//获取URL参数值
	getUrlParam: function(name){
		var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
		var r = window.location.search.substr(1).match(reg);
		if (r!=null) return unescape(r[2]); 
		return null;
	}
}
mysteward.init();