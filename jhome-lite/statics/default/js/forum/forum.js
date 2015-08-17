// JavaScript Document

var jieLinforum = {
	init: function(){
		//显示、隐藏举报、删除、置顶框
		$(document).on('click','.icon-expand-more',function(){
			var $this = $(this),
				$handle = $this.parents('.thread-item').find('.per-handle');
			if($handle.css('display') === 'none'){
				$handle.fadeIn();
			}else{
				$handle.fadeOut();
			}
			return false;
		});
		
		//举报、删除、置顶操作
		$(document).on('click','.per-type',function(){
			var $this = $(this),
				type = $this.data('type'), //report: 举报, delete: 删除, top: 置顶, down: 取消置顶
				url = actionUrl || false,
				fid = $this.parent().attr('fid'),
				cont = '';
			
			if (type == 'delete') {
				cont = '\u786e\u5b9a\u8981\u5220\u9664\uff1f'; //确定要删除？
                               // actionUrl=delThreadUrl;
			} else if (type == "top") {
				cont = '\u786e\u5b9a\u8981\u7f6e\u9876\uff1f'; //确定要置顶？
			} else if (type == "down") {
				cont = '\u786e\u5b9a\u8981\u4e0b\u6c89\uff1f'; //确定要下沉？
			} else if (type == "report") {
				cont = '\u786e\u5b9a\u8981\u4e3e\u62a5\uff1f'; //确定要举报？
			}
			$.DIC.dialog({
				content: cont,
				okValue: '\u786e\u5b9a',
				cancelValue: '\u53d6\u6d88',
				isMask: true,
				ok: function() {
					$.post(actionUrl, {"fid": fid,"act": type}, function(result) {
						if (!result.status) {
							$.DIC.dialog({content: result.info,autoClose: true});
						} else if (type == "delete") {
							document.location = indexUrl;
						} else {
							$.DIC.dialog({content: '\u64cd\u4f5c\u6210\u529f',autoClose: true}); //操作成功
							$this.parents('.person-data').find('.per-handle').hide();
						}
					}, 'json');
                                        //});
				},
				cancel: function() {
					$this.parents('.person-data').find('.per-handle').hide();
				}
			});	
			//console.log(fid + '; type = ' + type + '; ' + cont);
			return false;
		});
		
		//显示、隐藏回到顶部
		setInterval(function() {
			if (window.pageYOffset > 500 && !window.isNoShowToTop) {
				$('#go-top').show();
			} else {
				$('#go-top').hide();
			}
		}, 200);
		
		//回到顶部
		$('#go-top').on('click', function() {
			$(this).hide();
			scroll(0, 0);
		});
	},
	//初始化帖子列表页
	initList: function(){
		jieLinforum.init();
		
		//赞
		$(document).on('click','.like',function(){
			var $this = $(this),
				status = $this.data('status'),
				url = null;
			
			status == 'clicked' ? url = delPraiseUrl : url = praiseUrl;
			var obj = {'tid': $this.parent().attr('fid'), 'element': $this, 'confirm': 'icon-like-2', 'cancel': 'icon-like-1'};
			jieLinforum.setLike(obj,url);
			return false;
		});
		
		//踩
		$(document).on('click','.no-like',function(){
			var $this = $(this),
				status = $this.data('status'),
				url = null;
			
			status == 'clicked' ? url = delStepUrl : url = stepUrl;
			var obj = {'tid':$this.parent().attr('fid'), 'element': $this, 'confirm': 'icon-step-2', 'cancel': 'icon-step-1'};
			jieLinforum.setLike(obj,url);
			return false;
		});
	},
	//初始化帖子详情页
	initDetail: function(){
		jieLinforum.init();
		
		//显示分享
		$('#share-t, #share-b').on('click',function(){
			//console.log('分享');
			$('#jielin_share').fadeIn();
			jieLinforum.touchPrevent();
			return false;
		});
		
		//隐藏
		$('#jielin_share').on('click',function(){
			$(this).fadeOut();
			jieLinforum.touchDefault();
			return false;
		});
		
		//点赞
		$('#like-b').on('click',function(){
			var $this = $(this),
				status = $this.data('status'),
				url = null;
				
			status == 'clicked' ? url = delPraiseUrl : url = praiseUrl;
                        //console.log($this);
			var obj = {'tid': $this.parent().attr('fid'), 'element': $this, 'confirm': 'icon-like-2', 'cancel': 'icon-like-1'};
			  // console.log(obj);
			jieLinforum.setDetailLike(obj,url);
			return false;
		});
		
		//踩
		$('#step-b').on('click',function(){
			var $this = $(this),
				status = $this.data('status'),
				url = null;
			
			status == 'clicked' ? url = delStepUrl : url = stepUrl;
			var obj = {'tid': $this.parent().attr('fid'), 'element': $this, 'confirm': 'icon-step-2', 'cancel': 'icon-step-1'};
			jieLinforum.setDetailLike(obj,url);
			return false;
		});
		
		//评论楼主
		$('#reply-b').on('click',function(){
			var $this = $(this),
				url = replyUrl || false;
			
			jieLinforum.touchPrevent();
			$('#big-mask').css('height',$(document.body).height()).show();
			$('#dialog-reply').show();
			//console.log('评论楼主');
			return false;
		});
		
		//回复评论
		$(document).on('click','.reply-cont',function(){
			var $this = $(this),
				parent = $this.parents('.reply-item'),
				author = parent.attr('author'),
				uid = parent.attr('uid'),
				fid = parent.attr('fid'),
				pid = parent.attr('pid');
			
			if(uid == UserId){
				$.DIC.dialog({
					content: "\u786e\u5b9a\u5220\u9664\u56de\u590d\uff1f", //确定删除回复？
					okValue: '\u786e\u5b9a',
					cancelValue: '\u53d6\u6d88',
					isMask: true,
					ok: function() {
						$this.parents('.reply-item').fadeOut();
						$.post(delPostUrl, {"pid": pid,"fid": fid}, function(result) {
							if (result.status == 1) {
								var $reply = $("#reply-num");
								$reply.text($reply.text() - 1);
							} else {
								$.DIC.dialog({content: result.info,autoClose: true});
							}
						}, 'json');
						return;
					},
					cancel: function() {}
				});
			}else{
				jieLinforum.touchPrevent();
				$('#big-mask').css('height',$(document.body).height()).show();
				$('#content').attr('placeholder','\u56de\u590d\u0020' + author + '：'); //回复 
				$("#replyForm input[name=fid]").val(fid);
				$("#replyForm input[name=fuid]").val(uid);
				$("#replyForm input[name=parent_id]").val(pid);
				$('#dialog-reply').show();
			}
			
			//console.log('回复评论' + uid + " - " + author);
			return false;
		});
		
		//紧对方可见
		$('#user-see').on('click',function(){
			var $this = $(this),
				p = $('#is-private');
			if(p.val() == '1'){
				$(this).find('.icon-circle-check').css('color','#999');
				p.val('0');
			}else{
				$(this).find('.icon-circle-check').css('color','#00d9b4');
				p.val('1');
			}
		});
		
		//表情显示、隐藏
		$(".expreSelect").on("click", function() {
			if ($('.expreList').css('display') != 'none') {
				smileyObj.hide();
			} else {
				smileyObj.show();
			}
		});
		
		//取消发帖
		$("#sendCancel").on('click', function() {
			jieLinforum.hideReply();
			return false;
		});
		
		$('#big-mask').on('click',function(){
			jieLinforum.hideReply();
			return false;
		});
		
		var timer = setInterval(function() {
			$.DIC.strLenCalc($('#content')[0], 'pText', 280);
		}, 500);
		
		//查看原图
		jieLinforum.previewImage('thread-detail','.img-item');
	},
	//帖子列表页点赞 obj{'tid': 帖子id, element': click对象, 'confirm': 点赞后class, 'cancel': 点赞前class}, url: post url
	setLike: function(obj,url){
		var $element = obj.element,
			status = $element.data('status'),
			like_i = $element.find('i'),
			like_num = $element.find('span');
		
		var num = parseInt(like_num.text());
		if(status != 'clicked'){	
			like_i.removeClass(obj.cancel).addClass(obj.confirm).addClass('fadeIn');
			isNaN(num) || num <= 0 ? num = 1 : num += 1;
			like_num.html(num);
			$element.data('status','clicked');
                        //console.log(url);
                        //console.log(obj);
			$.post(url, {"fid": obj.tid},function(result){
                            console.log(result);  
                        });
		}else{
			like_i.removeClass(obj.confirm).removeClass('fadeIn').addClass(obj.cancel);
			isNaN(num) || num <= 0 ? num = 0 : num -= 1;
			like_num.html(num);
			$element.data('status','');
			$.post(url, {"fid": obj.tid});
		}
	},
	//帖子详情页点赞 obj{'tid': 帖子id, element': click对象, 'confirm': 点赞后class, 'cancel': 点赞前class}, url: post url
	setDetailLike: function(obj,url){
		var $element = obj.element,
			status = $element.data('status'),
			like_i = $element.find('i'),
			like_num = $('#' + $element.data('for'));
			
		var num = parseInt(like_num.text());
		//console.log(num);
		if(status != 'clicked'){	
			like_i.removeClass(obj.cancel).addClass(obj.confirm).addClass('fadeIn');
			isNaN(num) || num <= 0 ? num = 1 : num += 1;
			like_num.html(num);
			$element.data('status','clicked').addClass('chose-on');
			$.post(url, {"fid": obj.tid});
		}else{
			like_i.removeClass(obj.confirm).removeClass('fadeIn').addClass(obj.cancel);
			isNaN(num) || num <= 0 ? num = 0 : num -= 1;
			like_num.html(num);
			$element.data('status','').removeClass('chose-on');
			$.post(url, {"fid": obj.tid});
		}
	},
        
	//查看原图id: 含有img的容器id; pic: img的class
	previewImage: function(id,pic){
		$('#' + id).find(pic).on('click',function(){
			var $this = $(this),urls = [];
			var parent = $this.parent();
			parent.find(pic).each(function(i, e) {
                urls.push($(e).attr('data-original'));
            });
			wx.previewImage({
				current: $this.attr('src'), // 当前显示的图片链接
				urls: urls // 需要预览的图片链接列表
			});
		});
	},
	//隐藏回帖
	hideReply: function(){
		$('#replyForm')[0].reset();
		$('#dialog-reply').hide();
		$('#big-mask').hide();
		smileyObj.hide();
		jieLinforum.touchDefault();
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
	}
};