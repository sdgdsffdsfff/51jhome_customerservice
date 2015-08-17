<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<!-- nav --><!--active-->
<nav id="nav" class="nav-primary hidden-xs <?php if(!$menuSetting['nav']){echo 'nav-vertical';}?> <?php if($menuSetting['navbg']){echo 'bg-light';}?>">
<ul class="nav" data-spy="affix" data-offset-top="50">
  <?php
  if ($sysMenu){
      $menuClass = '';
      foreach ($sysMenu as $value) {
  ?>
  <li class="dropdown-submenu">
  <a href="<?php echo ($value['url']?$value['url']:'javascript:;');?>"><i class="fa <?php echo (isHave($value['icon'])?$value['icon']:'fa-list-ul');?> fa-lg"></i><span><?php echo $value['title'];?></span></a>
  <?php
  if ($value['item']){
  ?>
    <ul class="dropdown-menu">
    <?php
    foreach($value['item'] as $val){
    ?>
      <li><a href="<?php echo $val['url'];?>" <?php if(isHave($val['new'])){echo 'target="_blank"';}?>><?php echo $val['title'];?></a></li>
      <?php
    }
      ?>
    </ul>
    <?php
    }
    ?>
  </li>
  <?php
      }
      unset($sysMenu);
  }
  ?>
</ul>
</nav>
<!-- / nav -->