<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>客户端页面</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/chat.css" media="all" />
    <link rel="stylesheet" href="./css/tools.css" media="all" />
</head>
<body>
    
    <div id="iscroll-box" style="overflow:hidden">
        <div id="scroller">
            <div id="pullDown">
                <span class="loading"></span>
            </div>
            <section id="main-content" class="clear" style="padding-bottom:20px;">
            <!---->
              <!--<div align="center"><div class="sysmsg">XXX 加入聊天室</div></div>
              <div align="center"><div class="msgtime">昨天 12:20</div></div>-->
            <!---->
            </section>
        </div>
    </div>
        
    <footer id="input-box">
    <!---->
    <div id="user-input-message">
        <div class="am-input-group">
            <div class="am-input-group-label">
                <div class="add-item add-icon" id="add-tool"></div>
                <div class="add-item emo-icon" id="add-emo"></div>
            </div>
            <input type="text" id="content" class="am-form-field">
            <div id="send-msg" class="am-input-group-label pd10" onclick="CHAT.submit();">发送</div>
        </div>
    </div>
    <div id="show-tools" class="tipLayer">
        <div id="tools-list" style="display:none">
            <div class="tools-item add-photo"></div>
        </div>
    </div>
    <!---->
    <div style="clear:both; display:none"></div>
    </footer>
    <script type="text/javascript">
        var SOCKET_URL = "192.168.8.20:8083",
            UserName = '<?php echo $_GET["username"];?>',
            UserId  = '<?php echo $_GET["userid"];?>',
            basePath='http://static.51jhome.com/statics/default/';
    </script>
    <script type="text/javascript" src="./js/alertify.min.js"></script>
    <script type="text/javascript" src="./js/jquery.min.js"></script>
    <script type="text/javascript" src="./js/bootstrap.min.js"></script>
    <script type="text/javascript" src="./js/socket.io-1.3.5.js"></script>
    <script src="./js/client.js"></script>
    <script src="./js/jquery.touch.swipe.min.js"></script>
    <script src="./js/global.js"></script>
    <script src="./js/template.min.js"></script>
    <script src="./js/emotion.js"></script>
    <script src="./js/scroll.js"></script>
    <script src="./js/iscroll.js"></script>
    <script src="http://static.51jhome.com/statics/default/js/global/share.js"></script>
    <script id="tmpl_expreBox" type="text/html">
        <div class="expreList" style="display:none;">
            <% var def = null %>
            <% for (var i in cate) {%>
            <% if (def === null) { def = i }%>
            <% }%>
        <%var pageNum = []%>
        <div class="expreBox">
            <% for (var i in emo) {%>
            <ul class="expreCon <%=emo[i].ulClass%>" id="exp_emo<%=i%>" style="<%if(i != def) {%>display:none;<%}%>">
                <% var emoNum = 0%>
                <% var page = 0%>
            <% for (var j in emo[i].icon) {%>
                <% ++emoNum%>
                <% if (emoNum % emo[i].perPage == 1) { %>
                    <% ++page%>
                    <% pageNum[i] = page%>
                    <li class="<%=emo[i].liClass%><%=page%>">
                <% } %>
                    <a href="javascript:;" title="<%=j%>"></a>
                <% if (emoNum % emo[i].perPage == 0) { %>
                    <%if (emo[i].delBtn) {%><a href="javascript:;"></a><%}%></li>
                <% } %>
            <% }%></ul>
            <% }%></div>
        <% for (var k in pageNum) {%>
        <p class="pNumCon" id="exp_emo<%=k%>_page"<% if (def != k) {%> style="display:none;"<%}%>>
            <% for(var i = 1; i <= pageNum[k]; i++) { %>
            <a href="javascript:;" class="<% if(i == 1) { %> pNumOn <% } %>pNum db"></a>
            <%}%></p>
        <% }%>
        <div class="expressionTab">
            <% for (var i in cate) {%>
            <a href="javascript:;"<% if (def == i) {%> class="on"<%}%> id="emo<%=i%>" title="<%=cate[i]%>"><%=cate[i]%></a>
            <% }%>
        </div>
        </div>
    </script>


    <script>
    var iscrollContent = null;
    $(function(){
        var enabledSmiley = '1';
        smileyObj.init();   
        
        $('#add-tool').on('click',function(){
            var tool_box = $('#tools-list');
            if(tool_box.css('display') == 'none'){
                tool_box.show().siblings().hide();
            }else{
                tool_box.hide();
            }
        });
        
        $('#main-content').on('click',function(){
            var tool_box = $('#show-tools');
            tool_box.children().hide();
        });
        
        //添加表情
        $('#add-emo').on('click',function(){
            var  expre = $('.expreList');
            if(expre.css('display') == 'none'){
                expre.show().siblings().hide();
            }else{
                expre.hide();
            }
        });
        
        $("#content").on('focus',function(){
            $('#input-box').addClass('pos-rel');
            $('#show-tools').children().hide();
        }).on('focusout', function () {
            $('#input-box').removeClass('pos-rel');
        });
        
    })
    </script>

    <div class="row hidden">
        <div class="col-md-6 col-md-offset-3">
            <div class="row" id="login_div">
                <div class="col-md-12">
                    <input class="form-control" id="input_uid" placeholder="userid"/><br>
                    <button class="btn btn-default" id="btn_login">登陆</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <p id="status"></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="message"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <textarea id="words" class="form-control"></textarea>
                    </div>
                    <button class="btn btn-default" id="send">发送</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        
        function showMsg(msg){
            if(typeof msg !== "string"){
                msg = msg.toString();
            }
            $('#status').append('<p>' + msg + '</p>');
        }

        function talking(msg, type){
            if(typeof type === "number" && type === 1){
                $('#message').append('<p><span>' + '我：' + '</span>' + msg + '</p>');
            }else{
                $('#message').append('<p><span>' + '客服：' + '</span>' + msg + '</p>');
            }
        }

        $(window).load(function(){
            return false;
            $('#btn_login').click(function(){
                var usernames = ['张飞', '李逵', '赵云', '关于', '宋江', '习近平', '毛泽东'];
                var userid = $('#input_uid').val();
                var username = usernames[parseInt(Math.random() * usernames.length)];

                var serverd = false;

                $('#login_div').css('display', 'none');

                var socket = io.connect("192.168.8.20:8083");

                socket.on('connect_error', function(){
                    showMsg("connect_error");
                    socket.disconnect();
                });

                socket.on('connect', function(){
                    socket.emit('customer', {uid: userid, username: username});
                    socket.on('connret', function(data){
                        // data.s == 0 需要等待
                        console.log(data);
                        if(data.s == 0){
                            showMsg(data.m);
                        }else{
                            showMsg(data.m);
                        }
                        socket.on('server_in', function(data){
                            var serverid = data.serverid;
                            console.log(data);
                            showMsg('客服' + data.servername + '为您服务');

                            if(serverd == false){
                                socket.on('service_msg', function(data){
                                    talking(data.msg);
                                });
                                serverd = true;
                            }

                            $('#send').click(function(){
                                var msg = $('#words').val();
                                socket.emit('msg', {msg: msg});
                                talking(msg, 1);
                                $('#words').val('').focus();
                            });

                            socket.on('server_disconnect', function(){
                                showMsg('客服掉线了');
                            });
                        });                                       
                    });
                });

                socket.on('disconnect', function(){
                    showMsg('disconnect');
                    socket.disconnect();
                });

                return false;
            });

            
        });
    </script>
</body>
</html>