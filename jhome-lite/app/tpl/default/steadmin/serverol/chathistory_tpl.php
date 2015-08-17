<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>聊天记录</title>
    <link rel="stylesheet" href="<?php echo CSS_PATH;?>content/global.css">
    <style>
        *{
            margin: 0px;
            padding: 0px;
        }
        li{
            list-style: none;
        }
        .layim_zero{position:absolute; width:0; height:0; border-style:dashed; border-color:transparent; overflow:hidden;}
        .layim_chatthis{display:block;}
        .layim_chatuser{float:left; padding:5px 15px; font-size:0;}
        .layim_chatuser *{display:inline-block; *display:inline; *zoom:1; vertical-align:top; line-height:30px; font-size:12px; padding-right:10px;}
        .layim_chatuser img{width:30px; height:30px;}
        .layim_chatuser .layim_chatname{max-width:230px; overflow:hidden; text-overflow: ellipsis; white-space:nowrap;}
        .layim_chatuser .layim_chattime{color:#999; padding-left:10px;}
        .layim_chatsay{position:relative; float:left; margin:0 15px; padding:10px; line-height:20px; background-color:#F3F3F3; border-radius:3px; clear:both;}
        .layim_chatsay .layim_zero{left:5px; top:-8px; border-width:8px; border-right-style:solid; border-right-color:#F3F3F3;}
        .layim_chateme .layim_chatuser{float:right;}
        .layim_chateme .layim_chatuser *{padding-right:0; padding-left:10px;}
        .layim_chateme .layim_chatsay .layim_zero{left:auto; right:10px;}
        .layim_chateme .layim_chatuser .layim_chattime{padding-left:0; padding-right:10px;}
        .layim_chateme .layim_chatsay{float:right; background-color:#EBFBE3}
        .layim_chateme .layim_zero{border-right-color:#EBFBE3;} 
        .layim_chatview li {margin-bottom: 10px;  clear: both;}
        .layim_chatview .layim_chatme{float: right;}
        .layim_chatview{display: block;height: 400px;}
        .layim_chatme .layim_chatsay{float: right;}
        .expimg{display: inline-block;width: 16px;height: 16px;}
    </style>

</head>
<body>
    <?php if(!empty($chats) && count($chats) > 0){ ?>
            <ul class="layim_chatview">
                <?php foreach ($chats as $key => $value) { ?>
                    <?php if($value['whosaid'] == 1){ ?>
                        <li class="layim_chatme">
                            <div class="layim_chatuser">
                                <span class="layim_chattime"><?php echo date('Y-m-d H:i:s', $value['time']); ?></span>
                                <span class="layim_chatname"><?php echo $servername; ?></span>
                            </div>
                            <div class="layim_chatsay">
                                <?php echo $value['content']; ?>
                                <em class="layim_zero"></em>
                            </div>
                        </li>
                    <?php }else{ ?>
                        <li class="">
                            <div class="layim_chatuser">
                                <span class="layim_chatname"><?php echo $clientname; ?></span>
                                <span class="layim_chattime"><?php echo date('Y-m-d H:i:s', $value['time']); ?></span>
                            </div>
                            <div class="layim_chatsay">
                                <?php echo $value['content']; ?>
                                <em class="layim_zero"></em>
                            </div>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
            <?php echo page($total,$page,'',$pageshow,'p');?>
            <?php echo getJs('content/jquery-1.7.2.min');?>
            <?php echo getJs('serverol/jquery.qqFace'); ?>
            <script type="text/javascript">
                $(window).load(function(){
                        function replaceFaces(str){
                            var _str = str || '',
                                reg = /\[.+?\]/g,
                            _str = _str.replace(reg,function(a,b){
                                return '<img class="expimg" src="<?php echo PUBLIC_PATH;?>images/' + faces[a] + '">';    
                            });
                            return _str;   
                        }
                        $('.layim_chatsay').each(function(){
                            var t =this;
                            $(t).html(replaceFaces($(t).text()));
                        });
                });
            </script>
    <?php }else{ ?>
        <p>没有记录</p>
    <?php } ?>

</body>
</html>
