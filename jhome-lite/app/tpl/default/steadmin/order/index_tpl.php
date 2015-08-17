<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'order-index', //页面标示
    'pagename' => '订单列表', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array('content/jquery.smoothConfirm','content/calendar','global/audio.min'),
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
.show-pop-content {
	height:200px;
	overflow-y:scroll;
	overflow-x:hidden;
	table-layout: fixed;
	word-wrap:break-word;
	word-break:break-all;
}
</style>
    <!-- main padder --> 
    <section class="main padder">
        <div class="clearfix"> 
			<h4><i class="fa fa-table"></i><?php echo trim($Document['pagename'],'_');?></h4> 
        </div>
        <div id="show-new-orders" class="alert alert-success" style="font-size:14px; display:none"> 
           <button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button> 
           <i class="fa fa-check fa-lg"></i>
           <strong>新订单提示</strong> 有 <span id="new-orders" class="red">0</span> 个新订单需要处理
           <a href="<?php echo U('order/index',array('deal'=>'service'));?>" class="alert-link">点击更新数据 >></a>
      	</div>
        <div class="row">
			<!-- table 11 -->
			<div class="col-lg-12"> 
            <!---->
          <div class="panel-body">
            <div class="row text-small">
              <div class="col-sm-6">
              <a class="btn btn-default" href="<?php echo U('order/index');?>">全部订单</a>
              <a class="btn btn-danger" href="javascript:;" id="test-music">试听铃声</a>
               <div data-resize="auto" class="select btn-group" style="margin-left:5px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">
                  <?php echo $order_type&&isset($setting['order_type'][($order_type-1)])?$setting['order_type'][($order_type-1)]:'订单类型';?></span> <span class="caret"></span></button> 
                <ul class="dropdown-menu">
                <li data-value="0" <?php if(!$order_type){echo 'class="active"';}?>><a href="<?php echo U('order/index',getSearchUrl(array('order_type'=>null)));?>">全部</a></li>
                <?php
                foreach($setting['order_type'] as $key=>$val){
                ?>
                 <li data-value="<?php echo $key;?>" <?php if($order_type==($key+1)){echo 'class="active"';}?>><a href="<?php echo U('order/index',getSearchUrl(array('order_type'=>$key+1)));?>"><?php echo $val;?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
                <div data-resize="auto" class="select btn-group" style="margin-left:5px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">
                  <?php echo $service_id&&isset($service[$service_id])?$service[$service_id]['stitle']:'服务中心';?></span> <span class="caret"></span></button> 
                <ul class="dropdown-menu show-pop-content">
                <li data-value="0" <?php if(!$service_id){echo 'class="active"';}?>><a href="<?php echo U('order/index',getSearchUrl(array('service_id'=>null)));?>">全部</a></li>
                <?php
                foreach($service as $val){
                ?>
                 <li data-value="<?php echo $val['sid'];?>" <?php if($service_id==$val['sid']){echo 'class="active"';}?>><a href="<?php echo U('order/index',getSearchUrl(array('service_id'=>$val['sid'])));?>"><?php echo $val['stitle'];?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
                <div data-resize="auto" class="select btn-group" style="margin-left:5px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">
                  <?php echo $status&&isset($setting['order_status'][($status-1)])?$setting['order_status'][($status-1)]:'订单状态';?></span> <span class="caret"></span></button> 
                <ul class="dropdown-menu">
                <li data-value="0" <?php if(!$status){echo 'class="active"';}?>><a href="<?php echo U('order/index',getSearchUrl(array('status'=>null)));?>">全部</a></li>
                <?php
                foreach($setting['order_status'] as $key=>$val){
                ?>
                 <li data-value="<?php echo ($key+1);?>" <?php if($status==($key+1)){echo 'class="active"';}?>><a href="<?php echo U('order/index',getSearchUrl(array('status'=>($key+1))));?>"><?php echo $val;?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
                <div data-resize="auto" class="select btn-group" style="margin-left:5px"> 
                  <button class="btn btn-white dropdown-toggle" data-toggle="dropdown" type="button"><span class="dropdown-label">
                  <?php echo $orderSource&&isset($setting['order_source'][($orderSource-1)])?$setting['order_source'][($orderSource-1)]:'客户端';?></span> <span class="caret"></span></button> 
                <ul class="dropdown-menu">
                <li data-value="0" <?php if(!$orderSource){echo 'class="active"';}?>><a href="<?php echo U('order/index',getSearchUrl(array('order_source'=>null)));?>">全部</a></li>
                <?php
                foreach($setting['order_source'] as $key=>$val){
                ?>
                 <li data-value="<?php echo ($key+1);?>" <?php if($orderSource==($key+1)){echo 'class="active"';}?>><a href="<?php echo U('order/index',getSearchUrl(array('order_source'=>($key+1))));?>"><?php echo $val;?></a></li>
                <?php
                }
                ?>
                </ul>
                </div>
              </div>
              
              <div class="col-sm-6">
                <form action="<?php echo U('order/index');?>" method="get" name="form1" target="_self">
                <div class="col-sm-7 m-b-mini">
                    <div class="form-group">
                        <div class="col-sm-6">
                          <input type="text" readonly="" onclick="new Calendar().show(this);" value="<?php echo $startTime;?>" class="form-control" placeholder="开始时间" name="stime">
                        </div>
                        <div class="col-sm-6">
                          <input type="text" readonly="" onclick="new Calendar().show(this);" value="<?php echo $endTime;?>" class="form-control" placeholder="结束时间" name="etime">
                        </div>
                    </div>
		     	 </div>
                  <div class="col-sm-5 input-group">
                  	<div class="input-group-btn">
                       <button data-toggle="dropdown" class="btn btn-white btn-sm dropdown-toggle">
                       <span class="dropdown-label">订单号</span><span class="caret"></span></button>
                       <ul class="dropdown-menu dropdown-select pull-right">
                       <li class="active"><a href="#"><input type="radio" checked="checked" name="st" value="order_sn">订单号</a></li>
                        <li class=""><a href="#"><input type="radio" name="st" value="phone">手机号</a></li>
                        <li class=""><a href="#"><input type="radio" name="st" value="username">收件人</a></li>
                        <li class=""><a href="#"><input type="radio" name="st" value="user">下单用户</a></li>
                        <li class=""><a href="#"><input type="radio" name="st" value="uid">UID</a></li>
                       </ul> 
                      </div>
                    <input type="text" name="q" class="input-sm form-control" placeholder="请输入关键字..." />
                    <input name="g" type="hidden" value="<?php echo GROUP_NAME;?>" />
                    <input name="c" type="hidden" value="<?php echo ACTION_NAME;?>" />
                    <input name="m" type="hidden" value="<?php echo MODEL_NAME;?>" />
                    <input name="p" type="hidden" value="1" />
                    <span class="input-group-btn">
                    <button class="btn btn-sm btn-white" type="submit">搜索</button>
                    </span> </div>
                </form>
              </div>
            </div>
          </div>
          <!----><form class="form-horizontal" method="post" id="bacthForm"> 
				<section class="panel">
                
					<div class="table-responsive"> 
						<table class="table table-striped b-t text-small"> 
							<thead>
                              <tr>
                              	<th align="center">编号</th>
                                <th align="center">订单号</th>
                                <th align="center">客户端</th>
                                <th align="center">类型</th>
                                <th align="center">服务中心</th>
                                <th align="center">小区</th>
                                <th align="center">下单用户</th>
                                <th align="center">手机</th>
                                <th align="center">配送时间</th>
                                <th align="center">商品金额</th>
                                <th align="center">订单金额</th>
                                <th align="center">支付金额</th>
                                <th align="center">支付时间</th>
                                <th align="center">状态</th>
                                <th align="center" width="80">操作</th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($rs){
                                foreach($rs as $val){
                            ?>
                              <tr id="list_detail_<?php echo $val['order_id'];?>">
                              	<td><?php echo $val['order_id'];?></td>
                                <td><?php echo $val['order_sn'];?><br/>
								<?php if($val['is_spell']){echo '[<span class="red" title="合拼订单">拼</span>]';}?><?php if($val['is_helpbuy']){echo '[<span class="red" title="代付订单">代</span>]';}?><?php if($val['is_give']){echo '[<span class="red" title="赠送订单">赠</span>]';}?></td>
                                <td><?php echo $setting['order_source'][$val['order_source']];?></td>
                                <td><?php echo $setting['order_type'][$val['order_type']];?></td>
                                <td><a href="<?php echo U('order/index',array('service_id'=>$val['service_id']));?>"><?php echo $val['serviceName'];?></a></td>
                                <td><a href="<?php echo U('order/index',array('village_id'=>$val['village_id']));?>"><?php echo $val['villageName'];?></a></td>
                                <td><a href="<?php echo U('order/index',array('uid'=>$val['uid']));?>"><?php echo $val['userName'];?></a></td>
                                <td><?php echo $val['phone'];?></td>
                                <td><?php echo outTime($val['arrive_date'],2);?> <?php echo $val['arrive_time'];?></td>
                                <td><?php echo $val['goods_amount'];?></td>
                                <td><?php echo $val['order_amount'];?></td>
                                <td><?php echo $val['pay_amount'];?></td>
                                <td><?php echo outTime($val['pay_time']);?></td>
                                <td id="order-status-<?php echo $val['order_id'];?>"><?php echo $setting['order_status'][$val['status']];?></td>
                                <td>
                                <a href="<?php echo U('order/detail',array('id'=>$val['order_id']));?>">详细</a>
                                </td>
                              </tr>
                              <?php
                                }
                            }else{
                              ?>
                             <tr>
                                <td colspan="20" align="center">暂无数据</td>
                              </tr>
                            <?php
                            }
                            ?>
                            </tbody> 
						</table> 
					</div> 
         			<footer class="panel-footer">
                    <div class="row">
                      <div class="col-sm-12 text-right text-center-sm">
                        <?php echo page($total,$p,'',$pageShow,'p');?></div>
                    </div>
                  </footer>
				</section>
                </form>
			</div> 
			<!--/ table 11 -->

        </div> 
    </section>
    <!--/ main padder -->  
  </section> 
  <!--/ content --> 
<!--主体 结束-->
<div style="display:none">
<?php
if (!isset($menuSetting['music'])){
	$menuSetting['music']=0;
}
switch($menuSetting['music']){
	case 0:$music='ring';break;
	case 1:$music='ring1';break;
	case 2:$music='ring2';break;
}
?>
  <audio src="<?php echo PUBLIC_PATH;?>audio/<?php echo $music;?>.mp3" loop="loop" autobuffer id="audio"></audio>
  <link rel="prefetch" href="<?php echo PUBLIC_PATH;?>audio/<?php echo $music;?>.mp3" />
  <link rel="prefetch" href="<?php echo PUBLIC_PATH;?>audio/alert.mp3" />
</div>
<script>
var checkTimes=5000,//刷新时间5秒
	lastOrderId=<?php echo $lastOrderId;?>,
	newOrders=0,
	isPaly=false,
	isError=false,
	checkOrder=null,isTest=false;
$(function(){
	$(".setting-status").click(function(){
		var id=$(this).attr('rel'),act=$(this).attr('act');
	  	Msg.loading();
		$.post('<?php echo U('order/deal');?>',{"id":id,"act":act},function(result){
			Msg.hide();
			if (result.status==1){
				$("#order-status-"+id).html(result.data);
			}else{
				Msg.error(result.info);
			}
		},'json');
	  $("body").click();
	  return false;
	})
	audiojs.events.ready(function () {
       var as = audiojs.createAll();
    });
	$("#test-music").click(function(){
		if (!isTest){
			audio.play();
			isTest=true;
			$(this).text('播放中...');
		}
		setTimeout(function(){
			if (isTest){
				audio.currentTime = 0;
				audio.pause();
				isTest=false;
				$("#test-music").text('试听铃声');
			}
		},4000);
	})
	//定时检测
	checkOrder=setInterval(function(){
		$.getJSON('<?php echo U('order/checkorderlist');?>',{"order_id":lastOrderId},function(res){
			if (res.status==1){
				isError=false;
				if (res.data>0){
					newOrders=parseInt(res.data);
					$("#new-orders").text(newOrders);
						$("#show-new-orders").show();
						if (!isPaly){
							audio.play();
							isPaly=true;
						}
				}else{
					$("#new-orders").text(0);
						$("#show-new-orders").hide();
						if (isPaly){
							audio.currentTime = 0;
							audio.pause();
							isPaly=false;
						}	
				}
			}else{
				showAlert();
			}	
		}).error(function(){showAlert();});
	},checkTimes);
	
	function showAlert(){
		Msg.error('定时检测出错，请刷新页面',function(){},checkTimes);
		if (isError){
			return false;
		}
		isError=true;
		$("#audio").attr('src','<?php echo PUBLIC_PATH;?>audio/alert.mp3');
		audio.play();
		isPaly=true;
	}
	
	function setList(rs){
		var strHtml='<div class="list-group m-b-small">';
		strHtml+='<a href="#" class="list-group-item"><i class="fa fa-chevron-right"></i><span class="badge">201</span><i class="fa fa-fw fa-bell"></i>Inbox</a>';
		strHtml+='<a href="#" class="list-group-item"><i class="fa fa-chevron-right"></i><span class="badge">201</span><i class="fa fa-fw fa-bell"></i>Inbox</a>';
		strHtml+='<a href="#" class="list-group-item"><i class="fa fa-chevron-right"></i><span class="badge">201</span><i class="fa fa-fw fa-bell"></i>Inbox</a>';
      strHtml+='</div>';
	  return strHtml;	
	}
})
</script>
<?php
include getTpl('footer', 'public');
?>