<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<style>
#show-user-detail{overflow-y:scroll; overflow-x:hidden;table-layout: fixed;word-wrap:break-word;word-break:break-all; width:500px; height:400px}
</style>
<div id="show-user-detail">
<p align="center"><?php echo getAvatar($rs['uid'],132,false);?></p>
<?php
if ($showAll){
?>
<p><strong>UID：</strong><?php echo $rs['uid'];?></p>
<p><strong>来源：</strong><?php echo $regType[$rs['source_id']];?></p>
<p><strong>姓名：</strong><?php echo $rs['username'];?></p>
<p><strong>昵称：</strong><?php echo $rs['nickname'];?></p>
<p><strong>手机：</strong><?php echo $rs['phone'];?></p>
<p><strong>小区：</strong><?php echo $rs['village_name'];?></p><p>
<strong>邀请人：</strong><?php echo getUser($rs['invite_uid']);?></p>
<p><strong>状态：</strong><?php echo $status[$rs['status']];?></p>
<hr />
<?php }?>
<p><strong>微信昵称：</strong><?php echo $rs['nickname'];?></p>
<p><strong>是否关注：</strong><?php echo $rs['subscribe']?'是':'<span class="red">否</span>';?></p>
<p><strong>性别：</strong><?php echo isset($sex[$rs['sex']])?$sex[$rs['sex']]:'未知';?></p>
<p><strong>国家：</strong><?php echo $rs['country'];?></p>
<p><strong>省份：</strong><?php echo $rs['province'];?></p>
<p><strong>城市：</strong><?php echo $rs['city'];?></p>
<p><strong>关注时间：</strong><?php echo $rs['subscribe_time']?outTime($rs['subscribe_time']):'';?></p>
<p></p>
<p></p>
</div>