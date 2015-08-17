function z(str){
	console.log(str);	
}
$(function(){
	$(document).on("scroll.elasticity",
		function(e) {
		   // e.preventDefault()
		}).on("touchmove.elasticity",
		function(e) {
		   // e.preventDefault()
		})
});
	
(function () {
	var d = document,
		w = window,
		p = parseInt,
		dd = d.documentElement,
		db = d.body,
		dc = d.compatMode == 'CSS1Compat',
		dx = dc ? dd: db,
		ec = encodeURIComponent;
	
	w.CHAT = {
		msgObj:$("#main-content"),
		screenheight: w.innerHeight ? w.innerHeight : dx.clientHeight,
		username: UserName,
		userid: UserId,
		friendname: FriendName,
		friendid: FriendId,
		relation:relation,
		latesttime:latesttime,
		socket: null,
		msgTime: null,
		lastMsgTime: null,
		iscrollContent: null,
		lastHight:0,
		
		//提交聊天消息内容
		submit:function(){
			var content = d.getElementById("content").value;
			if(content != ''){
				var obj = {
					userid: this.userid,
					username: this.username,
					friendid: this.friendid,
					friendname: this.friendname,
					content: content
				};
				this.socket.emit('letter', obj);
				CHAT.updateMsgList(obj);
				d.getElementById("content").value = '';
			}
			$('#show-tools').children().hide();

			return false;
		},
		init: function(){
			
			//连接websocket后端服务器
			this.socket = io.connect(SOCKET_URL);

			//告诉服务器端有用户登录
			this.socket.emit('login', {userid:this.userid, username:this.username,friendid:this.friendid, friendname:this.friendname, relation:this.relation});
			
			//监听新用户登录
			this.socket.on('userLogin', function(o){
				//z(o);
				CHAT.updateSysMsg(o, 'login');
			});
			//监听用户退出
			this.socket.on('userLogout', function(o){
				//CHAT.updateSysMsg(o, 'logout');
			});
			
			//监听消息发送
			this.socket.on('public message', function(obj){
				if(FriendId==obj.userid){
				    CHAT.updateMsgList(obj);
				}
			});
			
			this.socket.on('res',function(res){
				CHAT.getMsgList(res);
				CHAT.iscrollContent.scrollTo(0,-CHAT.iscrollContent.scrollerH);
			});

			this.socket.on('historyres',function(res){
				CHAT.getMsgList(res,1);
			});

			this.socket.on('friendaccept',function(res){
				if(res.fuid==FriendId){
					$(".top-tool").hide();
					$("#content").attr("relation",1);
					$("#content").removeAttr("readonly"); 
					//$(".friendstatus").text("离线");	
				}
			});

		},
		getMsgList: function(data,position){
			var obj = {},
				contHtml  = '<div id="CHAT-0"><a id="scrolltop"></a>',
				className = 'rightd',
				i = 0;
			for(i in data.res){
				obj = data.res[i];
				var isme = (obj.userid == CHAT.userid) ? true : false,
					time = obj.strtime,
					className = 'rightd';
					innerHTML = '<div class="speech right">' + CHAT.setEmotion(obj.content) + '</div>';
				latesttime = data.res[0].infotime;
				// console.log(latesttime);
				
				contHtml += CHAT.showTime(CHAT.msgTime,time);
				CHAT.msgTime = time;
							
				if(!isme){
					className = 'leftd';
					innerHTML = '<div class="user">' + obj.username + '</div><div class="speech left">' + CHAT.setEmotion(obj.content) + '</div>';
				}
				contHtml += '<div class="' + className + '" style="background-image:url('+getAvatar(obj.userid)+')">' + innerHTML + '</div>';
			}
			contHtml += '</div>';
			//z('前面：'+CHAT.getHight());
			//CHAT.lastHight=CHAT.getHight();
			if(position==1){
				CHAT.msgObj.prepend(contHtml);
				CHAT.iscrollContent.refresh();	
			}else{
				CHAT.msgObj.append(contHtml);	
			}
			CHAT.setWipe('CHAT-0');
			if(CHAT.iscrollContent == null){
				 //CHAT.iscrollContent.refresh();
				 CHAT.setiScroll();
				 CHAT.refreshiScroll('bottom');
			}
			CHAT.iscrollContent.refresh();
			CHAT.msgTime = null;
		},
		//更新消息
		updateMsgList: function(obj){
			var isme = (obj.userid == CHAT.userid) ? true : false,
				className = 'rightd',
				innerHTML = '<div class="speech right">' + CHAT.setEmotion(obj.content) + '</div>',
				time_c = new Date(),
				time_f = time_c.format("yyyy-MM-dd hh:mm:ss"),
				time_id = 'CHAT-' + time_c.getTime(),
				contHtml = '<div id="' + time_id +'">';
				
			contHtml += CHAT.showTime(CHAT.lastMsgTime,time_f);
			CHAT.lastMsgTime = time_f;
			if(!isme){
				className = 'leftd';
				innerHTML = '<div class="user">' + obj.username + '</div><div class="speech left">' + CHAT.setEmotion(obj.content) + '</div>';
			}
			contHtml += '<div class="' + className + '" style="background-image:url('+getAvatar(obj.userid)+')">' + innerHTML + '</div></div>';
			CHAT.lastHight=CHAT.getHight();
			CHAT.msgObj.append(contHtml);
			CHAT.setWipe(time_id);
			CHAT.refreshiScroll('bottom');
		},
		//更新系统消息，本例中在用户加入、退出的时候调用
		updateSysMsg:function(o, msg){
			//z(o);
			
			//添加系统消息
			var html = '';
			html += '<div class="sysmsg-box"><div class="sysmsg">';
			html += msg;
			html += '</div></div>';
			
			//console.log(section);
			CHAT.lastHight=CHAT.getHight();
			CHAT.msgObj.append(html);
			CHAT.refreshiScroll('bottom');
		},
		//单击私聊，长按@好友
		setWipe: function(obj){
			$('#' + obj).find(".leftd").swipe({tap:function(event){//单击私聊
				var $that=$(event.target);
				if ($that.hasClass('leftd')){
					var username=$that.find('.user').text();
					// console.log('开始私聊');
				}
				return false;			
			},hold:function(event){//长按@好友
				// console.log('长按');
				var $that=$(event.target);
				if ($that.hasClass('leftd')){
					var content=$("#content").val();
					var username=$that.find('.user').text();
					$("#content").focus().val(content+'@'+username+' ');
				}
			}});
		},
		//下拉刷新消息列表
		pullDownAction: function(){
			//告诉服务器端有用户登录
			//alert(latesttime);
			this.socket.emit('gethistory', {latesttime:latesttime});
			/*
			setTimeout(function () {
				var i,contHtml = '';
		
				for (i=0; i<3; i++) {
					contHtml += '<div class="leftd" style="background-image:url('+getAvatar(1)+')"><div class="user">ye11' + i + '</div><div class="speech left">拉你个锤子。。。</div></div></div>';
				}
				console.log(contHtml);
				CHAT.msgObj.prepend(contHtml);
				CHAT.iscrollContent.refresh();
			}, 1000);
			*/
		},
		//添加好友
		addFriend: function(obj){
			//告诉服务器端有用户登录
			//alert(latesttime);
			this.socket.emit('addfriend', obj);
		},
		//初始化滚动条
		setiScroll: function(){
			var w_height = $(window).height(),
				pullDownEl = d.getElementById('pullDown'),
				pullDownOffset = pullDownEl.offsetHeight;
			$('#iscroll-box').height(w_height - 90);
			CHAT.iscrollContent = new iScroll("iscroll-box",{
				topOffset: pullDownOffset,
				//y: -1000,
				onRefresh: function () {
					if (pullDownEl.className.match('isloading')) {
						pullDownEl.className = '';
					}
				},
				onScrollMove: function () {
					if (this.y > 5 && !pullDownEl.className.match('flip')) {
						pullDownEl.className = 'flip';
						this.minScrollY = 0;
					} else if (this.y < 5 && pullDownEl.className.match('flip')) {
						pullDownEl.className = '';
						this.minScrollY = -pullDownOffset;
					}
				},
				onScrollEnd: function () {
					if (pullDownEl.className.match('flip')) {
						pullDownEl.className = 'isloading';			
						CHAT.pullDownAction();
					}
				}
			});
			document.ontouchmove=function(e){e.preventDefault();}
		},
		getHight:function(){
			return CHAT.msgObj.height();
		},
		//内容更新后，刷新滚动条 p
		refreshiScroll: function(p){

			if(CHAT.iscrollContent == null){
				 CHAT.setiScroll();
				 CHAT.refreshiScroll('bottom');
			}
			CHAT.iscrollContent.refresh();
			
			if(p == 'bottom') {
				var c_height = (CHAT.getHight()-CHAT.lastHight);
				//z('高度：'+c_height);
				if (!CHAT.lastHight){
					c_height*=-1;
					CHAT.iscrollContent.scrollTo(c_height,c_height,100,false);
					//z('初始化：'+c_height);
				}else{
					CHAT.iscrollContent.scrollTo(0,c_height,300,true);
					//z('新内容：'+c_height);
				}
				
			}
		},
		showTime: function(lastTime,thisTime){
			var time = CHAT.lastMsgTime,	
				time_c = null,
				html = '';
				
			time_c = CHAT.jsDateDiff(lastTime,thisTime);
			if(time_c) html = '<div class="sysmsg-box"><div class="sysmsg">' +  time_c + '</div></div>';
			
			return html;
		},
		jsDateDiff: function(lastTime,thisTime){
			var d_minutes,c_year,c_moth,c_day,d,c,t_l,
				isFirst = false,
				t_c = new Date(),
				t_t = new Date(thisTime.replace(/-/g,"/"));
			if(!lastTime){
				isFirst = true;
			}else{
				t_l = new Date(lastTime.replace(/-/g,"/"));
				
				var time_l = parseInt(t_l.getTime()/1000),
					time_t = parseInt(t_t.getTime()/1000);
					
				d = time_t - time_l;
				d_minutes = parseInt(d/60);
				
			}
			
			if(d_minutes > 1 || isFirst){
				c_year = t_c.getFullYear() - t_t.getFullYear();
				c_moth = t_c.getMonth() - t_t.getMonth();
				if(c_year <= 0 && c_moth <= 0){
					var c_day = t_c.getDay() - t_t.getDay();
					if(c_day<=0){
						return t_t.format("hh:mm");
					}else if(c_day==1){
						return "\u6628\u5929 " + t_t.format("hh:mm"); //昨天
					}else if(c_day==2){
						return "\u524d\u5929 " + t_t.format("hh:mm"); //前天
					}
				}else{
					return t_t.format("MM-dd hh:mm");
				}
			}
			return null;
		},
		//解析表情
		setEmotion: function(str){
			var _str = str || '',
				reg = /\[.+?\]/g,
				face = smiley.s1.icon;
			_str = _str.replace(reg,function(a,b){
				return '<img class="expimg" src="'+basePath+'images/' + face[a] + '">';  	
			});
			return _str;
		},
		test: function() {
			this.socket.emit('test', {});
		}
	};
	CHAT.init();
	//通过“回车”提交信息
	d.getElementById("content").onkeydown = function(e) {
		e = e || event;
		if (e.keyCode === 13) {
			CHAT.submit();
		}
	};
})();

Date.prototype.format = function(format) {
		var o = {
			"M+": this.getMonth() + 1, //month 
			"d+": this.getDate(), //day 
			"h+": this.getHours(), //hour 
			"m+": this.getMinutes(), //minute 
			"s+": this.getSeconds(), //second 
			"q+": Math.floor((this.getMonth() + 3) / 3), //quarter 
			"S": this.getMilliseconds() //millisecond 
		}
		if (/(y+)/.test(format)) {
			format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
		}

		for (var k in o) {
			if (new RegExp("(" + k + ")").test(format)) {
				format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
			}
		}
		return format;
}