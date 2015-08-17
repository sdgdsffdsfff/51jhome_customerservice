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


function getAvatar($uid){
	return '/images/1.png';
    if (!$uid){
        $uid=1;
    }
    if ($uid>30664){
        $path='http://wx.51jhome.com/upload/';
    }else{
        $path='http://img.51jhome.com/yun/';
    }
    $uid=pad($uid,9);
    $dir1 = $uid.substr(0, 3);
    $dir2 = $uid.substr(3, 2);
    $dir3 = $uid.substr(5, 2);
    //z($uid+'|'+$uid.substr(-2));
    return $path+'avatar/' + $dir1 + '/' + $dir2 + '/' + $dir3 + '/' +$uid.substr(-2) + '_132.jpg';
}
	
(function () {
	var d = document,
		w = window,
		p = parseInt,
		dd = d.documentElement,
		db = d.body,
		dc = d.compatMode == 'CSS1Compat',
		dx = dc ? dd: db,
		ec = encodeURIComponent,
		reconnectTimes=3,
		reconnectFailCount=0;
	
	w.CHAT = {
		msgObj:$("#main-content"),
		screenheight: w.innerHeight ? w.innerHeight : dx.clientHeight,
		username: UserName,
		userid: UserId,
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
					content: content
				};
				this.socket.emit('message', obj);
				CHAT.updateMsgList(obj);
				d.getElementById("content").value = '';
			}
			$('#show-tools').children().hide()
			return false;
		},
		init: function(){
			
			//连接websocket后端服务器
			this.socket = io.connect(SOCKET_URL);
			
			var socket = this.socket,
				t = this;

			socket.on('connect', function(){
				console.log('connect');
				//告诉服务器端有用户登录
				socket.emit('customer', {uid:t.userid, username:t.username});

				socket.on('connret', function(data){
					var serverd = false,
						server = {};
                    // data.s == 0 需要等待
                    console.log(data);
                    if(data.s == 0){
                        CHAT.appendSysMsg('客服正忙，请耐心等待');
                    }else{
                        //CHAT.appendSysMsg(data.m);
                    }

                    socket.on('server_in', function(data){
                        var serverid = data.serverid;
                        server.servername = data.servername;
                        server.serverid = data.serverid;
                        CHAT.appendSysMsg('客服' + data.servername + '为您服务');

                        if(serverd == false){
                        	// 收到从服务器发来的消息，也就是客服消息
                            socket.on('service_msg', function(data){
                            	// var img_pattern = "/^<img\s*/i";
                            	// if(!img_pattern.test(data.msg)){
                            	// 	data.msg = data.msg.rep
                            	// }
                            	var obj = {
                            		userid: server.serverid,
                            		content: data.msg,
                            		username: server.servername
                            	};
                            	CHAT.updateMsgList(obj);
                            });
                            serverd = true;
                        }

                        socket.on('server_disconnect', function(){
                            CHAT.appendSysMsg('客服' + data.servername + '已经离开');
                        });
                    });                                       
                });
			});
			
			socket.on('offline', function(data){
				CHAT.appendSysMsg(data.msg);
				socket.disconnect();
			});
			
			//监听新用户登录
			this.socket.on('userLogin', function(o){
				//z(o);
				CHAT.updateSysMsg(o, 'login');	
			});
			
			//监听用户退出
			this.socket.on('userLogout', function(o){
				CHAT.updateSysMsg(o, 'logout');
			});
			
			//监听消息发送
			this.socket.on('public message', function(obj){
				CHAT.updateMsgList(obj);
			});
			
			this.socket.on('res',function(res){
				//z('接受到最新的聊天记录');
				//console.log(res);
				CHAT.getMsgList(res);
			});
			
			//连接失败
            this.socket.on('connect_failed', function(o) {
            	socket.disconnect();
                console.log("connect_failed to Server");
				CHAT.updateSysMsg(o, 'connect_failed',"无法连接服务器...",'red');
            });
		//连接错误
            this.socket.on('error', function(o) {
            	socket.disconnect();
                console.log("error");
				CHAT.updateSysMsg(o, 'error',"服务器连接错误...",'red');
            });
		//尝试新的连接
            this.socket.on('reconnecting', function (o) {
            	socket.disconnect();
                console.log("reconnecting："+reconnectFailCount);
				reconnectFailCount++;
				if (reconnectFailCount >= reconnectTimes) {
					CHAT.updateSysMsg(o, 'reconnecting',"与服务器通讯失败，请检查网络...",'red');
				}
            });
		//尝试连接成功
            this.socket.on('reconnect', function (o) {
            	socket.disconnect();
                console.log("reconnect");
				CHAT.updateSysMsg(o, 'reconnect',"欢迎再次回来",'white');
                reconnectFailCount--;
            });
		//断开连接
            this.socket.on('disconnect', function (o) {
                console.log("disconnect");
				CHAT.updateSysMsg(o, 'disconnect',"连接失败，请检查网络...",'red');
            });

		},
		//获取消息列表，用户进入时
		getMsgList: function(data){
			var obj = {},
				contHtml = '<div id="CHAT-0">',
				className = 'rightd',
				i = 0;
			for(i in data.res){
				obj = data.res[i];
				var isme = (obj.userid == CHAT.userid) ? true : false,
					time = obj.infotime,
					className = 'rightd';
					innerHTML = '<div class="right-avatar"><img rel="'+obj.username+'" src="'+getAvatar(obj.userid)+'" onerror="avtLoadErr(this)"/></div><div class="right-speech">' + CHAT.setEmotion(obj.content) + '</div>';
				contHtml += CHAT.showTime(CHAT.msgTime,time);
				CHAT.msgTime = time;
							
				if(!isme){
					className = 'leftd';
					innerHTML = '<div class="left-user">' + obj.username + '</div><div class="chat-line"><div class="left-avatar"><img rel="'+obj.username+'" src="'+getAvatar(obj.userid)+'" onerror="avtLoadErr(this)"/></div><div class="left-speech">' + CHAT.setEmotion(obj.content) + '</div></div>';
				}
				contHtml += '<div class="' + className + '">' + innerHTML + '</div>';
			}
			contHtml += '</div>';
			//z('前面：'+CHAT.getHight());
			CHAT.lastHight=CHAT.getHight();
			CHAT.msgObj.append(contHtml);
			CHAT.setWipe('CHAT-0');
			if(CHAT.iscrollContent == null){
				 CHAT.setiScroll();
				 CHAT.refreshiScroll('bottom');
			}
			CHAT.msgTime = null;
		},
		//更新消息
		updateMsgList: function(obj){
			var isme = (obj.userid == CHAT.userid) ? true : false,
				className = 'rightd',
				innerHTML = '<div class="right-avatar"><img rel="'+obj.username+'" src="'+getAvatar(obj.userid)+'" onerror="avtLoadErr(this)"/></div><div class="right-speech">' + CHAT.setEmotion(obj.content) + '</div>',
				time_c = new Date(),
				time_f = time_c.format("yyyy-MM-dd hh:mm:ss"),
				time_id = 'CHAT-' + time_c.getTime(),
				contHtml = '<div id="' + time_id +'">';
				
			contHtml += CHAT.showTime(CHAT.lastMsgTime,time_f);
			CHAT.lastMsgTime = time_f;
			if(!isme){
				className = 'leftd';
				innerHTML = '<div class="left-user">' + obj.username + '</div><div class="chat-line"><div class="left-avatar"><img rel="'+obj.username+'" src="'+getAvatar(obj.userid)+'" onerror="avtLoadErr(this)"/></div><div class="left-speech">' + CHAT.setEmotion(obj.content) + '</div></div>';
			}
			contHtml += '<div class="' + className + '">' + innerHTML + '</div></div>';
			CHAT.lastHight=CHAT.getHight();
			CHAT.msgObj.append(contHtml);
			CHAT.setWipe(time_id);
			CHAT.refreshiScroll('bottom');
		},
		//更新系统消息，本例中在用户加入、退出的时候调用
		updateSysMsg:function(o, action,msg,className){
			msg=msg||'';
			className=className||'';
			//z(o);
			if (o){
			//当前在线用户列表
			var onlineUsers = o.onlineUsers||[];
			//当前在线人数
			var onlineCount = o.onlineCount||null;
			//新加入用户的信息
			var user = o.user||null;
				
			//更新在线人数
			//var userhtml = '';
//			var separator = '';
//			for(key in onlineUsers) {
//		        if(onlineUsers.hasOwnProperty(key)){
//					userhtml += separator+onlineUsers[key];
//					separator = '、';
//				}
//		    }
//			d.getElementById("onlinecount").innerHTML = '当前共有 '+onlineCount+' 人在线，在线列表：'+userhtml;
			}
			//添加系统消息
			var html = '';
			html += '<div class="sysmsg-box"><div class="sysmsg '+className+'">';
			switch(action){
				case 'login':
				case 'logout':
					html += user.username;
					html += (action == 'login') ? ' \u52a0\u5165\u4e86\u804a\u5929\u5ba4' : ' \u9000\u51fa\u4e86\u804a\u5929\u5ba4'; //加入了聊天室,//退出了聊天室
				break;
				default:
					html +=msg;
				break;
			}
			html += '</div></div>';
			
			//console.log(section);
			CHAT.lastHight=CHAT.getHight();
			CHAT.msgObj.append(html);
			CHAT.refreshiScroll('bottom');
			$("#pullDown").hide();
		},
		appendSysMsg: function(msg, style){
			var html = '';
			msg = msg || '';
			html += '<div class="sysmsg-box"><div class="sysmsg">'+ msg + '</div></div>';

			CHAT.lastHight=CHAT.getHight();
			style ? CHAT.msgObj.append($(html).find('.sysmsg').css(style)) : CHAT.msgObj.append(html);
			CHAT.refreshiScroll('bottom');
			$("#pullDown").hide();
		},
		//单击私聊，长按@好友
		setWipe: function(obj){
			$('#' + obj).find(".left-avatar").swipe({tap:function(event){//单击私聊
				var $that=$(event.target);
				var username=$that.text();
					console.log('\u5f00\u59cb\u79c1\u804a'); //开始私聊
				return false;			
			},hold:function(event){//长按@好友
				console.log('\u957f\u6309'); //长按
				var $that=$(event.target);
				var content=$("#content").val();
				var username=$that.attr('rel');
				$("#content").focus().val(content+'@'+username+' ');
			}});
		},
		//下拉刷新消息列表
		pullDownAction: function(){
			setTimeout(function () {
				var i,contHtml = '';
		
				for (i=0; i<3; i++) {
					contHtml += '<div class="leftd"><div class="left-user">ye11' + i + '</div><div class="chat-line"><div class="left-avatar"><img rel="ye11'+i+'" src="'+getAvatar(1)+'" onerror="avtLoadErr(this)"/></div><div class="left-speech">拉你个锤子...</div></div></div>';
				}
				//console.log(contHtml);
				CHAT.msgObj.prepend(contHtml);
				CHAT.iscrollContent.refresh();
			}, 1000);
		},
		//初始化滚动条
		setiScroll: function(){
			var w_height = $(window).height(),
				pullDownEl = d.getElementById('pullDown'),
				pullDownOffset = pullDownEl.offsetHeight;
			$('#iscroll-box').height(w_height - 40);
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
			if (CHAT.iscrollContent){
				CHAT.iscrollContent.refresh();
			}else{
				return false;	
			}
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