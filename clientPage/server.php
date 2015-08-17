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
    </style>
</head>
<body>
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <p id="msg"></p>
            <p>状态<span id="server_status">在线</span></p>
            <div class="dropdown" id="status_switch">
                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                切换状态
                <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                    <li><a href="#" id="status-leave">我要离开</a></li>
                    <li><a href="#" id="status-live">我回来了</a></li>
                </ul>
            </div>
        </div>
    </div>
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

            console.log('connect');
            var socket = io.connect("127.0.0.1:8083");

            socket.on('connect_error', function(){
                showMsg('connect_error');
                socket.disconnect();
            });

            socket.on('connect', function(){
                socket.emit('service', {uid: 1, servername: "客服一号"});
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
                    $('#server_status').html('离开');
                    socket.emit("status", {status: 0});
                    return false;
                });
                //我要回来按钮
                $('#status-live').click(function(){
                    $('#server_status').html('在线');
                    socket.emit("status", {status: 1});
                    return false;
                });
            });

            socket.on('disconnect', function(){
                showMsg('disconnect');
                socket.disconnect();
            });
        });
    </script>
</body>
</html>