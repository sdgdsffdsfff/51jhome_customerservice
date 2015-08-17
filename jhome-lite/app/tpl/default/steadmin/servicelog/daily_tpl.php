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
<header class="panel-heading"><?php echo $today; ?>的日报</header> 

  <ul class="list-group">

    <li class="list-group-item">
      <textarea name="" id="" cols="70" rows="8"><?php foreach ($rs as $key => $value) { ?><?php echo '类型：' . $type[$value['type_id']]['name'];?>&#13;&#10;<?php if($value['phone']){ echo '手机号码：'. $value['phone'].'&#13;&#10;';} ?><?php if($value['order_id']){ echo '订单号码：'. $value['order_id'].'&#13;&#10;';} ?><?php if($value['username']){ echo '用户名：'. $value['username'].'&#13;&#10;';} ?><?php echo '反馈内容：' . $value['feedback'].'&#13;&#10;';?><?php echo '是否处理：' . $status[$value['status_id']].'&#13;&#10;';?>&#13;&#10;<?php } ?>
      </textarea>
    </li>

  </ul>

</section>

</div>