<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'serverol-serverpage', //页面标示
    'pagename' => '在线客服', //当前页面名称
    'mycss' => array('serverol/layim', 'serverol/qqFace', 'serverol/serverol'), //加载的css样式表
    'myjs' => array(), //加载的js脚本
    'footerjs'=>array('serverol/jquery.migrate', 'serverol/jquery.qqFace', 'serverol/socket.io-1.3.5', 'serverol/lay/layer/layer.min', 'serverol/lay/layim', 'serverol/jquery.iframe-transport'),
    'head' => true, //加载头部文件
);    

include getTpl('header', 'public');
?>

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
            <li><a href="#"><i class="glyphicon glyphicon-user"></i> 客服 <?php echo $uname;?></a></li>
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

    <div class="main" style="width: 100%;height: 800px;">
        
    </div>

 <!-- footer --> 
  <footer id="footer">
   <div class="text-center padder clearfix"> 
    <p> <small>&copy; 2014 结邻公社</small> </p> 
   </div> 
  </footer> 
  <!-- / footer --> 
  <!-- Bootstrap --> 
  <!-- app --> 
    
    <script type="text/javascript">
        var basePath = '<?php echo PUBLIC_PATH;?>';
        var BASE_URL = '<?php echo BASE_URL;?>';
        var SERVERNAME = '<?php echo $uname;?>';
        var SERVERFACE = '<?php echo PUBLIC_PATH."images/serviceol/1.png";?>';
        var SERVERID = '<?php echo $uid;?>';
    </script>

<?php
    getJs(array('content/global','content/facebox','content/msgbox'));//,'content/slider/bootstrap-slider'
 if (isset($Document['footerjs'])&&$Document['footerjs']){
    getJs($Document['footerjs']);
 }
?>



        <script type="text/javascript">
        function showMsg(msg){
            if(typeof msg !== "string"){
                msg = msg.toString();
            }
            $('#msg').append('<p>' + msg + '</p>');
        }   
        $(window).load(function(){
            var id = "<?php echo $uid;?>",
                name = "<?php echo $uname;?>";
            console.log('connect');
            var socket = io.connect("127.0.0.1:8083");

            socket.on('connect_error', function(){
                showMsg('connect_error');
                socket.disconnect();
            });

            socket.on('connect', function(){
                socket.emit('service', {uid: id, servername: name});
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
                    var index = $(e.target).parent().index();
                    switch(index){
                        case 0:
                            break;
                        case 1:
                            console.log("聊天记录");
                            $('#frame_history').attr("src", "<?php echo BASE_URL;?>steadmin/serverol/chathistory?"+"serverid="+id+"&clientid="+xxim.nowchat.id);
                            break;
                        case 2:
                            console.log('转接');
                            socket.emit('getservers');
                            break;
                        default:
                            break;
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