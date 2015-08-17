<?php
$nowProgress=round($now / $total * 100, 2);
$progress= $nowProgress. '%';
if (!isset($j)){
    $j=0;
}
if (!isset($doing)){
    $doing=$now;
}
if (!isset($sleepTime)||!$sleepTime){
	$sleepTime=300;	
}
$list=array('orange'=>'orange','green'=>'green','blue'=>'blue','red'=>'red','black'=>'black');
if (!isset($style)||!$style||!isset($list[$style])){
	$style=$list['green'];
}
if (!$isAjax){
?>
<style>
#show-doing{font:verdana 13px;}
#graphbox{border: 1px solid #e7e7e7;padding: 10px;width:800px;background-color: #f8f8f8;margin: 5px auto;}
#graphbox h2{color: #666666;font-family: Arial;font-size: 18px;font-weight: 700;}
.graph{position: relative;background-color: #F0EFEF;border: 1px solid #cccccc;padding: 2px;font-size: 13px; border-radius:10px}
.graph .orange, .green, .blue, .red, .black{position: relative;text-align:left;height:23px;line-height:23px;font-family: Arial;display: block; border-radius:10px}
.show{position:absolute; z-index:99; bottom:5px;color:<?php echo ($nowProgress>=9?'#ffffff':'#333');?>;}
.graph .orange{background-color: #ff6600;}
.graph .green{background-color: #66CC33;}
.graph .blue{background-color: #3399CC;}
.graph .red{background-color: red;}
.graph .black{background-color: #555;}
.fred{color:#f00}
.info{ width:750px; height:30px; line-height:30px; margin:0; padding:0; font-size:13px; text-align:left}
.nowdoing a{color:#ff0000}
#msg{width:800px;height:auto;min-height:18px;border:1px #FFCC7F solid; text-align:left;background:#FFFFE5;color:#666;font:normal 13px/23px 宋体; margin:0 auto 5px auto;padding:10px;word-wrap:break-word;word-break:normal;}
</style>
<div id="show-doing">
<?php }?>
  <div id="graphbox">
      <h2><?php echo $title; ?></h2>
      <div class="graph"><span class="<?php echo $style;?>" style="width:<?php echo $progress;?>;"></span><span class="show">&nbsp; 完成度:<?php echo $progress;?></span></div>
      <p class="info">总数：<span class="fred"><?php echo $total;?></span> 条，已完成：<span class="fred"><?php echo $now;?></span> 条</p>
      <p class="info nowdoing">状态信息：<span class="fred"><?php echo $doing; ?></span></p>
  </div>
 <?php
    if ($now <= $total) {
        if ($reload && $reload < 3) {
            $reload+=1;
            echo '<span class="red">数据读取失败，重新读取中！</span>';
        } else {
            $now+=1;
        }
        ?>
        <?php
    }
    ?>
	<br/>
	<?php if (isset($msg)&&$msg){?>
			<div id="msg"><pre style="background-color:transparent; border:none"><?php echo $msg;?></pre></div>
	<?php
	}
	?>
<?php if (!$isAjax){?>
</div>
<script>
var p=<?php echo $now;?>,
	total=<?php echo $total; ?>,
	j=<?php echo $j; ?>,
	users=<?php echo $users;?>,
	send=<?php echo $send;?>;
	function getAjaxData(){
		isSending=window.setTimeout(function(){
			Msg.loading('正在群发中...');
			$.getJSON('<?php echo $pagename;?>',{"is_ajax":1,"p":p,"total":total,"j":j},function(res){
			  Msg.hide();
			  if (res.status==1){
				  $("#show-doing").html(res.data.item);
				  p=parseInt(res.data.p)+1;
				  total=res.data.total;
				  j=res.data.j;
				  getAjaxData();
			  }else if(res.status==-1){
				  //clearInterval(init);
				  Msg.ok(res.info);
				  $(document).trigger('close.facebox');
			  }else if(res.status==-2){
				 // clearInterval(init);
				  Msg.error(res.info);
				  $(document).trigger('close.facebox');
			  }else{
				  Msg.alert(res.info);
			  }	
		  }).error(function(){Msg.error('数据处理失败，正在尝试重新加载');
			getAjaxData();
		  });
	  },<?php echo $sleepTime;?>);
	}
	getAjaxData();
</script>
</body>
</html>
<?php }?>