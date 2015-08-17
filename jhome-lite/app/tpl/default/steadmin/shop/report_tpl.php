<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'shop-report', //页面标示
    'pagename' => '订单详情统计', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array('content/jquery.smoothConfirm','content/calendar'),
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
  <style>
.sm{font-size:12px; color:#999}
.bigf{font-size:20px; color:#F60}
.showTips{font-size:13px; line-height:20px; text-align:left; padding:5px 10px}
</style>
  <!-- main padder -->
  <section class="main padder">
    <div class="clearfix">
      <h4><i class="fa fa-table"></i><?php echo trim($Document['pagename'],'_');?></h4>
    </div>
    
    <div class="row"> 
      <!-- table 11 -->
      <div class="col-lg-12"> 
      <div class="alert alert-warning alert-block" style="font-size:14px; margin:0"> 
       <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button> 
       <p>本页面仅统计用户要求的配货日期内状态为：已支付、已审核、正在配货的订单</p> 
      </div>
        <!---->
        <div class="panel-body">
          <div class="row text-small">
            <div class="col-sm-6"> <a class="btn btn-default" href="<?php echo U('shop/report',array('cid'=>$cid));?>">全部订单</a>
              <div data-resize="auto" class="select btn-group" style="margin-left:5px">
                <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label"> <?php echo $service_id&&isset($service[$service_id])?$service[$service_id]['stitle']:'服务中心';?></span> <span class="caret"></span></button>
                <ul class="dropdown-menu show-pop-content">
                  <li data-value="0" <?php if(!$service_id){echo 'class="active"';}?>><a href="<?php echo U('shop/report',getSearchUrl(array('service_id'=>null)));?>">全部</a></li>
                  <?php
                foreach($service as $val){
                ?>
                  <li data-value="<?php echo $val['sid'];?>" <?php if($service_id==$val['sid']){echo 'class="active"';}?>><a href="<?php echo U('shop/report',getSearchUrl(array('service_id'=>$val['sid'])));?>"><?php echo $val['stitle'];?></a></li>
                  <?php
                }
                ?>
                </ul>
              </div>
            </div>
            <div class="col-sm-6">
              <form action="<?php echo MAIN_URL;?>" method="get" name="form1" target="_self">
                <div class="col-sm-7 m-b-mini">
                  <div class="form-group">
                    <div class="col-sm-6">
                      <input type="text" readonly="" onclick="new Calendar().show(this);" value="<?php echo outTime($startTime,2);?>" class="form-control" placeholder="开始时间" name="stime">
                    </div>
                    <div class="col-sm-6">
                      <input type="text" readonly="" onclick="new Calendar().show(this);" value="<?php echo outTime($endTime,2);?>" class="form-control" placeholder="结束时间" name="etime">
                    </div>
                  </div>
                </div>
                <div class="col-sm-5 input-group">
                  <input name="g" type="hidden" value="<?php echo GROUP_NAME;?>" />
                  <input name="c" type="hidden" value="<?php echo ACTION_NAME;?>" />
                  <input name="m" type="hidden" value="<?php echo MODEL_NAME;?>" />
                  <input name="service_id" type="hidden" value="<?php echo $service_id;?>" />
                  <input name="cid" type="hidden" value="<?php echo $cid;?>" />
                  <input name="is_output" id="is_output" type="hidden" value="0" />
                  <span class="input-group-btn">
                  <button class="btn btn-sm btn-white" type="submit" id="search-data">搜索</button>
                  <button id="output" class="btn btn-sm btn-white" style="margin-left:10px" type="submit">导出</button>
                  </span> </div>
              </form>
            </div>
          </div>
        </div>
        <!--1--> 
        
        <!---->
        <section class="panel"> 
		<div class="table-responsive"> 
          <table class="table table-striped b-t text-small">
            <thead>
              <tr>
                <th align="center" width="40%">名称</th>
                <th align="center" width="40%">详细</th>
                <th align="center" width="20%">数量</th>
              </tr>
            </thead>
            <tbody>
              <?php
			  $totalSum=0;
              if ($orderTotal){
				  foreach($orderTotal as $val){
					  $totalSum+=$val['counts'];
			  ?>
              <tr>
                <td><?php echo $val['name'];?></td>
                <td><?php 
				foreach($val['item'] as $v){
					echo $v['name'],'：<span class="red">',$v['counts'],'</span> 份&nbsp; &nbsp; ';
				}
				?></td>
                <td><?php echo $val['counts'];?></td>
              </tr>
              <?php }
			  ?>
            <tr>
                <td>&nbsp; </td>
                <td>&nbsp; </td>
                <td>总计：<span class="red"><?php echo $totalSum;?></span> 份</td>
              </tr>  
            <?php
			  }else{
			?>
            <tr>
                <td colspan="10" align="center">暂无数据</td>
                </tr>
            <?php
			  }
			?>
            </tbody>
          </table>
        </div>
        </section>
        <!---->
        <!---->
        <section class="panel"> 
		<div class="table-responsive"> 
          <table class="table table-striped b-t text-small">
            <thead>
              <tr>
                <th align="center" width="40%">服务社</th>
                <th align="center" width="40%">详细</th>
                <th align="center" width="20%">数量</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($serviceList){
				  foreach($serviceList as $val){
			  ?>
              <tr>
                <td><?php echo $val['service_name'];?></td>
                <td><?php 
				foreach($val['item'] as $v){
					echo $v['name'],'：<span class="red">',$v['counts'],'</span> 份<br/> ';
				}
				?></td>
                <td><?php echo $val['counts'];?></td>
              </tr>
              <?php }
			  }else{
			?>
            <tr>
                <td colspan="10" align="center">暂无数据</td>
                </tr>
            <?php
			  }
			?>
            </tbody>
          </table>
        </div>
        </section>
        <!---->
        <!---->
        <section class="panel"> 
		<div class="table-responsive"> 
          <table class="table table-striped b-t text-small">
            <thead>
              <tr>
              	<th align="center">编号</th>
                <th align="center">服务社</th>
                <th align="center">小区</th>
                <th align="center">订单号</th>
                <th align="center">收货人</th>
                <th align="center">手机</th>
                <th align="center">地址</th>
                <th align="center">配送时间</th>
                <th align="center">下单时间</th>
                <th align="center">商品列表</th>
                <th align="center">留言</th>
                <th align="center">混合订单</th>
                <th align="center">订单状态</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($rs){
				  $i=1;
				  foreach($rs as $val){
			  ?>
              <tr>
                <td><?php echo $i;?></td>
                <td><?php echo $val['service_name'];?></td>
                <td><?php echo $val['village_name'];?></td>
                <td><a target="_blank" href="<?php echo U('order/detail',array('id'=>$val['order_id']));?>"><?php echo $val['order_sn'];?></a></td>
                <td><?php echo $val['username'];?></td>
                <td><?php echo $val['phone'];?></td>
                <td><?php echo $val['address'];?></td>
                <td><?php echo outTime($val['arrive_date'],2);?> <?php echo $val['arrive_time'];?></td>
                <td><?php echo outTime($val['order_time']);?></td>
                <td><?php
                foreach($val['list'] as $v){
					echo $v['goods_name'],' (x ',$v['goods_counts'],')<br/>';
				}
				?></td>
                <td><?php echo $val['desc'];?></td>
                <td><?php echo $val['goods_total']==$val['select_goods_total']?'<span class="red">否</span>':'是';?></td>
                <td><?php echo $setting['order_status'][$val['status']];?></td>
              </tr>
              <?php
			  	$i+=1;
			   }
			  }else{
			?>
            <tr>
                <td colspan="13" align="center">暂无数据</td>
                </tr>
            <?php
			  }
			?>
            </tbody>
          </table>
        </div>
        </section>
        <!---->
        
        <!--1--> 
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
	$("#output").click(function(){
		$("#is_output").val(1);	
		return true;
	})
	$("#search-data").click(function(){
		$("#is_output").val(0);	
		return true;
	})
})
</script>
<?php
include getTpl('footer', 'public');
?>
