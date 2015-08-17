<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title><?php echo WEB_TITLE;?></title>
<?php echo getCss(array('content/login','content/global'));?>
<?php echo getJs(array('content/jquery-1.7.2.min','content/msgbox'));?>
</head>
<body>
<script>
  var offset = 1508;
  var mov = 1;
  var backgroundheight = offset;
  function scrollbackground() {
      offset = (offset < mov) ? offset + (backgroundheight - mov) : offset - mov;
      $('#cloud').css('background-position', offset + "px");
      setTimeout(function() {
          scrollbackground()
      },50);
  };
  $(function() {
      var width = $(document.body).width() - 2;
      $("#p").animate({left: width - 100}, 2300, function() {
          $(this).hide()
      });
      $("#login-bar").animate({width: width}, 2300).animate({height: 80}, 500, function() {
          $("#form1").fadeIn(300);
		  $("#user").focus();
      });
      $(window).resize(function() {
          $("#login-bar").width($(document.body).width() - 2)
      });
	  $("#form1").submit(function(){
		  if (!$("#user").val()){
			  shake($("#user"),'error-show',3);
			  return false;
		 }
		 if (!$("#password").val()){
			  shake($("#password"),'error-show',3);
			  return false;
		 }
		 //Msg.loading('正在登录，请稍候...');
		 $.post('<?php echo U('login/ajaxlogin');?>',$("#form1").serialize(),function(result){
			 mov = mov == 50 ? 2 : 50;
			 if (result.status==1){
			   location.href=result.data.refer;
		  }else{
			  $("#login-btn").val('登录系统');
			  mov = 2;
			  Msg.error(result.info);
			  return false;
		  }
		},'json');
		return false;
	})
	function shake(ele,cls,times){
		  var i = 0,t= false ,o =ele.attr("class")+" ",c ="",times=times||2;
		  if(t) return;
		  t= setInterval(function(){
			  i++;
			  c = i%2 ? o+cls : o;
			  ele.attr("class",c);
		  if(i==2*times){
			  clearInterval(t);
			  ele.removeClass(cls);
		  }
		  },200);
	  };
  });
  scrollbackground();
</script>
<div id="main">
  <div id="cloud"></div>
  <div id="login-bar" class="selector"></div>
  <div id="login-form">
    <form id="form1" name="form1" method="post" action="">
      <input name="__hash__" id="__hash__" value="<?php echo formHash();?>" type="hidden"/>
      <input name="refer" id="refer" value="<?php echo $refer;?>" type="hidden"/>
      <div id="form-l">
      	帐号：<input name="user" class="txt" type="text"  id="user" value="" />
        密码：<input class="txt" name="password" id="password" type="password" value=""/>
      </div>
      <div id="form-r">
        <input id="login-btn" name="sub" type="submit" class="button b-blue" value="登录系统" />
      </div>
    </form>
  </div>
  <div id="p"></div>
</div>
</body>
</html>