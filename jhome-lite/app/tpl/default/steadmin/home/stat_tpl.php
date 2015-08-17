<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'home-stat', //页面标示
    'pagename' => '访问统计', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array('content/highcharts', 'content/exporting'),
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
					<header class="panel-heading">
					访问统计
					</header>
                    <div class="panel-body"> 
						<div class="row text-small"> 
							<div class="col-sm-12 m-b-mini">
                            <div id="container" style="margin:5px auto"></div>
							</div>
						</div> 
					</div>
					<div class="table-responsive"> 
						<table class="table table-striped b-t text-small"> 
							<thead> 
								<tr>
                                <th align="center">ID</th>
                                <th align="center">新增用户</th>
                                <th align="center">消息总数</th>
                                <th align="center">用户流失</th>
                                <th align="center">净增用户</th>
                                <th align="center">统计日期</th>
                              </tr>
							</thead> 
							<tbody> 
								<?php
								if ($rs){
									foreach($rs as $val){
								?><tr>
									<td><?php echo $val['id'];?></td>
									<td><?php echo $val['new_user'];?></td>
									<td><?php echo $val['new_msg'];?></td>
									<td><?php echo $val['new_cancel'];?></td>
                                    <td><?php echo ($val['new_user']-$val['new_cancel']);?></td>
									<td><?php echo ($val['infotime']?outTime($val['infotime'],2):'');?></td>
								  </tr>
								  <?php
									}
								}else{
								  ?>
								 <tr>
									<td colspan="5" align="center">暂无数据</td>
								  </tr>
								<?php
								}?>
                                </tbody> 
						</table> 
					</div> 
					 <footer class="panel-footer"> 
						<div class="row"> 
							<div class="col-sm-12 text-right text-center-sm"> 
									<?php echo page($total,$p,'',20,'p');?>
							</div> 
						</div> 
					</footer>
				</section> 
			</div> 
			<!--/ table 11 -->

        </div> 
    </section>
    <!--/ main padder -->  
  </section> 
  <!--/ content --> 
<!--主体 结束-->
<?php
if ($rs){
?>
<script>
$(function () {
        $('#container').highcharts({
            chart: {
                type: 'line',
                marginRight: 130,
                marginBottom: 25
            },
            title: {
                text: '用户访问走势分析',
                x: -20 //center
            },
            subtitle: {
                text: '',
                x: -20,
				y:5
            },
            xAxis: {
                categories: [<?php echo "'".implode("','",$data['time'])."'";?>]
            },
            yAxis: {
                title: {
                    text: '数据走势（人）'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: ''
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 100,
                borderWidth: 0
            },colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
            series: [{
                name: '新增消息',
                data: [<?php echo implode(',',$data['new_msg']);?>]
            },{
                name: '新增用户',
                data: [<?php echo implode(',',$data['new_user']);?>]
            },{
                name: '用户流失',
                data: [<?php echo implode(',',$data['new_cancel']);?>]
            }]
        });
    });
</script>
<?php
}
?> 
<script>
$(function(){

})
</script>
<?php
include getTpl('footer', 'public');
?>