<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<style>
.pictureList ul {
    margin:0;
    padding:0;
}
.pictureList li {
    list-style-type:none;
    float:left;
    margin:3px
}
.show-pop-content {
    overflow-y:scroll;
    overflow-x:hidden;
    table-layout: fixed;
    word-wrap:break-word;
    word-break:break-all;
}
</style>
<div class="table-responsive show-pop-content" style="width:650px; height:400px;">
<section class="panel portlet-item"> 
<header class="panel-heading">详细信息</header> 
<ul class="list-group"> 
<li class="list-group-item" style="text-align:center"><?php if(isHave($rs['upload'])){ ?>
                    <?php if(preg_match('/^.*?\.(jpg|png|gif|jpeg|bmp|jpe)$/', $rs['upload'])){ ?>
                      <img src="<?php echo getImgUrl($rs['upload']);?>" width="150" height="150"/>
                    <?php }else{ ?>
                      <p class="form-control"><a href="<?php echo getImgUrl($rs['upload']);?>" target="_blank"><i class="fa fa-download"></i>
点击此处下载附件</a></p>
                    <?php } ?>
                  <?php }else{ ?>
                    <p class="form-control">无附件</p>
                  <?php } ?></li>
<li class="list-group-item"><strong>编号</strong>：<?php echo $rs['fid'];?></li> 
<li class="list-group-item"><strong>时间</strong>：<?php echo $rs['fb_time'];?></li>
<li class="list-group-item"><strong>反馈人</strong>：<?php echo $rs['username'];?></li>
<li class="list-group-item"><strong>反馈人真实姓名</strong>：<?php echo $rs['real_name'];?></li>
<li class="list-group-item"><strong>类型</strong>：<?php echo $type[$rs['type_id']]['name'];?></li>
<li class="list-group-item"><strong>是否处理</strong>：<?php echo $status[$rs['status_id']];?></li>
<li class="list-group-item"><strong>订单号</strong>：<?php if($rs['order_id']){ echo $rs['order_id'];} else {?>无<?php }?></li>
<li class="list-group-item"><strong>用户名</strong>：<?php if($rs['client_username']){ echo $rs['client_username'];} else {?>无<?php }?></li>
<li class="list-group-item"><strong>手机号</strong>：<?php if($rs['phone']){ echo $rs['phone'];} else {?>无<?php }?></li>
<li class="list-group-item"><strong>反馈内容</strong>：<pre><?php echo $rs['servicelog'];?></pre></li>

</ul> 
</section>

</div>
