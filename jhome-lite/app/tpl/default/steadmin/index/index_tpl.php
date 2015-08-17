<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'home-index', //页面标示
    'pagename' => '管理中心', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array(),
    'head' => true, //加载头部文件
);
include getTpl('header', 'public');
?>
<!--顶部导航 开始-->
<?php include getTpl('top', 'public');?>
<!--顶部导航 结束-->
<!--左侧菜单 开始-->
<?php include getTpl('nav', 'public');?>
<!--左侧菜单 结束-->
<!--主体 开始-->
<!-- content -->
<section id="content"> 
  <!-- main padder -->
  <section class="main padder">
    <div class="clearfix">
      <h4><i class="fa fa-table"></i><?php echo trim($Document['pagename'],'_');?></h4>
    </div>
    <div class="row"> 
      <!-- table 11 -->
      <div class="col-lg-12">
        <section class="panel">
          <header class="panel-heading">欢迎登陆</header>
          <div class="panel-body">
            <div class="row text-small">
              <div class="col-sm-10 m-b-mini">
                <p>欢迎您：<?php echo $adminInfo['username'];?></p>
                <p>用户组：<?php echo $group[$adminInfo['groupid']];?></p>
                <p>有效期：<span class="red"><?php echo $adminInfo['effective']?outTime($adminInfo['effective']):'长期';?></span></p>
                <p>登陆时间：<?php echo $adminInfo['logintime']?outTime($adminInfo['logintime']):'';?></p>
                <p>登陆IP：<?php echo long2ip($adminInfo['loginip']);?></p>
              </div>
            </div>
          </div>
        </section>
      </div>
      <!--/ table 11 -->
    </div>
  </section>
  <!--/ main padder --> 
</section>
<!--/ content --> 
<!--主体 结束--> 
<script>
$(function(){

})
</script>
<?php
include getTpl('footer', 'public');
?>
