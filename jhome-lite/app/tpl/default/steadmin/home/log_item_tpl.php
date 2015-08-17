<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<?php
  if ($rs){
	  foreach($rs as $val){
  ?>
	<tr>
	  <td><?php echo $val['id'];?></td>
	  <td><a href="<?php echo U('home/log',array('uid'=>$val['uid'],'status'=>$status));?>"><?php echo $val['uid'];?></a></td>
	  <td><a href="<?php echo U('home/log',array('uid'=>$val['uid'],'status'=>$status));?>"><?php echo getUser($val['uid']);?></a></td>
	  <td><?php echo $val['msg_id'];?></td>
	  <td><?php echo getMsgType($val['msg_type']);?></td>
	  <td><?php echo $val['content'];?></td>
	  <td><?php echo ($val['create_time']?outTime($val['create_time']):'');?></td>
	  <td>
		<div class="btn-group"> 
		  <button class="btn btn-white btn-xs dropdown-toggle" data-toggle="dropdown">操作<span class="caret"></span></button> 
		  <ul class="dropdown-menu">
			  <li><a rel="pop" href="<?php echo U('home/msg',array('uid'=>$val['uid']));?>">发送消息</a></li>
			  <li><a rel="pop" href="<?php echo U('member/detail',array('uid'=>$val['uid'],'all'=>1));?>">用户信息</a></li>
		  </ul> 
	  </div>
		</td>
	</tr>
	<?php
	  }
  }
?>

