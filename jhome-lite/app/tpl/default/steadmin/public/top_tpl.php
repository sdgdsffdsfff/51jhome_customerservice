<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<header id="header" class="navbar <?php if($menuSetting['header']){echo 'bg bg-black';}?>">
  <ul class="nav navbar-nav navbar-avatar pull-right">
    <li class="dropdown"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <span class="hidden-xs-only"><strong>[<?php echo $userGroup[$adminInfo['groupid']];?>] <?php echo $adminInfo['username'];?></strong></span> <span class="thumb-small avatar inline"><img src="<?php echo getImgUrl('upload/avatar/noavatar_big.jpg');?>" alt="" class="img-circle" /></span> <b class="caret hidden-xs-only"></b> </a>
      <ul class="dropdown-menu pull-right">
        <li><a href="<?php echo U('my/edit');?>">个人资料</a></li>
        <li class="divider"></li>
        <li><a href="<?php echo U('login/logout');?>">退出</a></li>
      </ul>
    </li>
  </ul>
  <a class="navbar-brand" href="#">JHome</a>
  <ul class="nav navbar-nav hidden-xs">
    <li class="dropdown shift" data-toggle="shift:appendTo" data-target=".nav-primary .nav">
     <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog fa-lg visible-xs visible-xs-inline"></i>个性设置 <b class="caret hidden-sm-only"></b></a>
      <ul class="dropdown-menu" id="admin-setting-skin">
        <li> <a href="#" data-toggle="class:navbar-fixed" data-target="body" <?php if($menuSetting['navbar']){echo 'class="active"';}?>>导航 [<span class="text-active">自动</span> <span class="text">静止</span> ]</a> </li>
        <li class="hidden-xs"> <a href="#" data-toggle="class:nav-vertical" data-target="#nav" <?php if($menuSetting['nav']){echo 'class="active"';}?>>菜单 [<span class="text-active">垂直</span> <span class="text">水平</span> ]</a> </li>
        <li class="divider hidden-xs"></li>
        <li class="dropdown-header">颜色</li>
        <li> <a href="#" data-toggle="class:bg bg-black" data-target=".navbar" <?php if($menuSetting['header']){echo 'class="active"';}?>>导航 [<span class="text-active">白色</span> <span class="text">反转</span> ]</a> </li>
        <li> <a href="#" data-toggle="class:bg-light" data-target="#nav" <?php if($menuSetting['navbg']){echo 'class="active"';}?>>菜单 [<span class="text-active">反转</span> <span class="text">浅色</span> ]</a> </li>
        <li class="divider hidden-xs"></li>
        <li class="dropdown-header">声音</li>
        <li> <a href="#" data-toggle="class:bg bg-black">默认</a> </li>
		<li> <a href="#" data-toggle="class:bg bg-black">铃声1</a> </li>
        <li> <a href="#" data-toggle="class:bg bg-black">铃声2</a> </li>
      </ul>
    </li>
  </ul>
</header>
<!-- / header -->
<script>
$(function(){
$("#admin-setting-skin").find('a').click(function(){var index=$("#admin-setting-skin").find('a').index(this);var data={};switch(index){case 0:data['navbar']=$("body").hasClass("navbar-fixed")?0:1;break;case 1:data['nav']=$("#nav").hasClass("nav-vertical")?1:0;break;case 2:data['header']=$("#header").hasClass("bg-black")?0:1;break;case 3:data['navbg']=$("#nav").hasClass("bg-light")?0:1;break;case 4:data['music']=0;break;case 5:data['music']=1;break;case 6:data['music']=2;break;}
$.post('<?php echo U('my/setting');?>',{"data":data},function(res){if(res.status==1){Msg.ok('设置成功');}else{Msg.error(res.info);}},'json');});
})
</script>