<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>服务端</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link href="css/layim.css" type="text/css" rel="stylesheet"/>
    <link rel="stylesheet" href="./css/qqFace.css">
    <style>
        .expimg{
            display: inline-block;
            width: 24px;
            height: 24px;
        }
        #upload-img{
            overflow: hidden;
            position: relative;
            display: inline-block;
        }
        #input_file{
            position: absolute;
            top: 0;
            right: 0;
            margin: 0;
            opacity: 0;
            -ms-filter: 'alpha(opacity=0)';
            font-size: 200px;
            direction: ltr;
            cursor: pointer;
        }
        .chat-img{
            display: inline-block;
            max-width: 50%;
        }
        .layim_tabs .nav>li>a{
        	padding: 10px 8px;
        }
        #layim_shortwords{
        	overflow: hidden;
        	height: 411px;
        }
        #layim_shortwords li{
        	border-bottom: 1px solid #e3e3e3;
        }
        #layim_shortwords li:hover{
        	cursor: pointer;
        }
        #layim_shortwords:hover{
        	overflow-y: auto;
        }
        .layim_tabs .nav-tabs>li>a{
        	border-radius: 0px;
        }
        .layim-trans-btn:hover{
            cursor: pointer;
        }
    </style>
</head>
<body>
	<nav class="navbar navbar-default">
	  <div class="container-fluid">
	    <!-- Brand and toggle get grouped for better mobile display -->
	    <div class="navbar-header">
	      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
	        <span class="sr-only">Toggle navigation</span>
	        <span class="icon-bar"></span>
	        <span class="icon-bar"></span>
	        <span class="icon-bar"></span>
	      </button>
	      <a class="navbar-brand" href="#">腾房科技客服系统</a>
	    </div>

	    <!-- Collect the nav links, forms, and other content for toggling -->
	    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
	      <ul class="nav navbar-nav navbar-right">
	        <li class="active"><a href="#">工作台</a></li>
	        <li><a href="#">管理后台</a></li>
            <li><a href="#">历史访客</a></li>
	        <li><a href="#"><i class="glyphicon glyphicon-user"></i> 客服露露</a></li>
	        <li class="dropdown" id="server-status">
	          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" id="status-text"><span>在线</span> <span class="caret"></span></a>
	          <ul class="dropdown-menu">
	            <li><a href="#" id="status-leave">我要离开</a></li>
                <li><a href="#" id="status-live">我回来了</a></li>
	          </ul>
	        </li>
	      </ul>
	    </div><!-- /.navbar-collapse -->
	  </div><!-- /.container-fluid -->
	</nav>
   
    <script type="text/javascript">
        var basePath = '/';
    </script>
    <script type="text/javascript" src="./js/alertify.min.js"></script>
    <script type="text/javascript" src="./js/jquery.min.js"></script>
    <script type="text/javascript" src="./js/jquery.migrate.js"></script>
    <script type="text/javascript" src="./js/jquery.qqFace.js"></script>
    <script type="text/javascript" src="./js/bootstrap.min.js"></script>
    <script type="text/javascript" src="./js/socket.io-1.3.5.js"></script>
    <script type="text/javascript" src="./lay/layer/layer.min.js"></script>
    <script type="text/javascript" src="./lay/layim.js"></script>
    <script type="text/javascript" src="./js/jquery.iframe-transport.js"></script>
    <script type="text/javascript">
        // layimapi({
        //     data_id: 112,
        //     type: 'one',
        //     name: 'textworld',
        //     face: 'http://tp1.sinaimg.cn/1571889140/180/40030060651/1'
        // });
        // layimapi({
        //     data_id: 1122,
        //     type: 'one',
        //     name: 'textssssworld',
        //     face: 'http://tp1.sinaimg.cn/1571889140/180/40030060651/1'
        // });
        function showMsg(msg){
            if(typeof msg !== "string"){
                msg = msg.toString();
            }
            $('#msg').append('<p>' + msg + '</p>');
        }   
        $(window).load(function(){
            // qqFace 
            /*$('.layim_addface').qqFace({
                id : 'layim_addface', 
                assign:'layim_write', 
                path:'arclist/' //表情存放的路径
            });*/
            
            var id = "<?php echo $_GET['id'];?>",
                name = "<?php echo $_GET['name'];?>";
            console.log('connect');
            var socket = io.connect("127.0.0.1:8083");

            socket.on('connect_error', function(){
                showMsg('connect_error');
                socket.disconnect();
            });

            socket.on('connect', function(){
                socket.emit('service', {uid: id, servername: name});
                showMsg('欢迎您：客服一号');
                xxim.setSocket(socket);

                socket.on('client_in', function(data){
                    console.log(data);
                    console.log("当前人数："+data.cur_n);
                    xxim.popchatboxapi({
                            type: 'one',
                            name: data.username,
                            data_id: data.uid,
                            face: 'http://tp1.sinaimg.cn/1571889140/180/40030060651/1'
                    });
                });

                $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function(e){
                	console.log('tab show');
                	if($(e.target).text() == "转接"){
                		console.log('转接');
                		socket.emit('getservers');
                	}
                });

                socket.on('getserversret', function(servers){
                	console.log('servers');
                	console.log(servers);
                	var tab_trans = $('#layim_trans');
                	tab_trans.empty();
                	if(servers.length == 0){
                		tab_trans.append('暂时没有其他客服');
                	}else{
                		tab_trans.append('<ul></ul>');
	                	for(var i = 0; i < servers.length; i++){
	                		$('#layim_trans>ul').append('<li>'+servers[i].servername+'    <a data-serverid="'+servers[i].serverid+'" class="layim-trans-btn" id="layim-trans-'+servers[i].serverid+'">转接</a></li>');
	                	}
                	}                	
                });

                socket.on('server_online', function(server){
                    var ul = $('#layim_trans>ul');
                    if(ul.length == 0){
                        $('#layim_trans').empty().append('<ul></ul>');
                    }
                    $('#layim_trans>ul').append('<li>'+server.servername+'    <a data-serverid="'+server.serverid+'" class="layim-trans-btn" id="layim-trans-'+server.serverid+'">转接</a></li>');
                });

                socket.on('server_offline', function(server){
                    console.log('offline');
                    $('#layim-trans-'+server.serverid).parent().remove();
                    if($('#layim_trans').find('li').length == 0){
                        $('#layim_trans').html('暂时没有其他客服');
                    }
                });

                socket.on('client_msg', function(data){
                    console.log("receive client_msg");
                    console.log(data);
                    xxim.appendMsg({id: data.clientid, content: data.msg, type: 'one'})
                });
                // 用户掉线之类的
                socket.on('client_disconnect', function(data){
                    console.log('client_disconnect');
                    console.log(data);
                    xxim.closeChatWindow({uid: data.clientid, type: 'one'});
                });
                //我要离开按钮
                $('#status-leave').click(function(){
                    $('#status-text>span').first().html('离开');
                    socket.emit("status", {status: 0});
                    //$('#server-status').dropdown();
                    return false;
                });
                //我要回来按钮
                $('#status-live').click(function(){
                    $('#status-text>span').first().html('在线');
                    socket.emit("status", {status: 1});
                    // $('#server-status').dropdown();
                    return false;
                });
            });

            //连接失败
            socket.on('connect_failed', function(o) {
                socket.disconnect();
                console.log("connect_failed to Server");
                alert('connect_failed');
            });

            socket.on('disconnect', function(){
                //showMsg('disconnect');
                socket.disconnect();
            });
        });
    </script>
</body>
</html>