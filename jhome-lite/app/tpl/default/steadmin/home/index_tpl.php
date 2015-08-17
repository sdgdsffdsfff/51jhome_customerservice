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
<link rel="stylesheet" media="screen" href="<?php echo PUBLIC_PATH; ?>jquery-ui/css/smoothness/jquery-ui-1.10.0.custom.min.css"/>
<script type="text/javascript" src="<?php echo PUBLIC_PATH; ?>jquery-ui/js/jquery-ui-1.10.0.custom.min.js"></script>
<script type="text/javascript" src="<?php echo PUBLIC_PATH; ?>jquery-ui/js/jquery-ui-timepicker-addon.js"></script>
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
					绑定参数
					</header> 
					<div class="panel-body"> 
						<div class="row text-small"> 
							<div class="col-sm-10 m-b-mini">
								<p>URL：<?php echo $url;?></p>
            					<p>TOKEN：<?php echo $token;?></p>
							</div> 
						</div> 
					</div> 
				</section> 
			</div> 
			<!--/ table 11 -->
<!---->
<!-- table 11 -->
			<div class="col-lg-12"> 
            <!----> 
          <div class="panel-body">
            <div class="row text-small">
              <div class="col-sm-6">
              <a class="btn btn-default" href="<?php echo U('home/index');?>">全部日志</a>
              </div>
              
              <div class="col-sm-6">
                <form action="<?php echo U('home/index');?>" method="get" name="form1" target="_self">
                <div class="col-sm-7 m-b-mini">
                    <div class="form-group">
                        <div class="col-sm-6">
                          <input type="text" readonly  value="<?php echo $startTime;?>" class="form-control" id="stime" placeholder="开始时间" name="stime">
                        </div>
                        <div class="col-sm-6">
                          <input type="text" readonly value="<?php echo $endTime;?>" class="form-control" id="etime" placeholder="结束时间" name="etime">
                        </div>
                    </div>
		     	 </div>
                  <div class="col-sm-5 input-group">
                  <input type="text" name="id" class="input-sm form-control" value="<?php echo $recordId;?>" placeholder="请输入record_id..." />
                    <input name="g" type="hidden" value="<?php echo GROUP_NAME;?>" />
                    <input name="c" type="hidden" value="<?php echo ACTION_NAME;?>" />
                    <input name="m" type="hidden" value="<?php echo MODEL_NAME;?>" />
                    <span class="input-group-btn">
                    <button class="btn btn-sm btn-white" type="submit">搜索</button>
                    </span> </div>
                </form>
              </div>
            </div>
          </div>
          <!----> 
				<section class="panel">
                
					<div class="table-responsive"> 
						<table class="table table-striped b-t text-small"> 
							<thead>
                              <tr>
                                <th align="center">操作人UID</th>
                                <th align="center">操作人</th>
                                <th align="center">分组</th>
                                <th align="center">模块</th>
                                <th align="center">动作</th>
                                <th align="center">动作说明</th>
                                <th align="center">描述</th>
                                <th align="center">操作值</th>
                                <th align="center">时间</th>
                                <th align="center">操作</th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($rsLog){
                                foreach($rsLog as $key=>$val){
                            ?>
                              <tr id="list_detail_<?php echo $key;?>">
                             	 <td><?php echo $val['user_id'];?></td>
                                <td><?php echo $val['user_name'];?></td>
                                <td><?php echo $val['group_name'];?></td>
                                <td><?php echo $val['action_name'];?></td>
                                <td><?php echo $val['model_name'];?></td>
                                <td><?php echo isset($val['action'])?$val['action']:'';?></td>
                                <td><?php echo $val['action_desc'];?></td>
                                <td><?php echo is_array($val['record_id'])?implode(',',$val['record_id']):$val['record_id'];?></td>
                                <td><?php echo outTime($val['infotime']);?></td>
                                <td>
                                <a href="<?php echo U('home/logdetail',array('id'=>$key));?>" rel="pop">详细</a>
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
			</div>
			<!--/ table 11 -->
<!---->
        </div> 
    </section>
    <!--/ main padder -->  
  </section> 
  <!--/ content --> 
<!--主体 结束-->
<script>
$(function(){
$('#stime').datetimepicker();
$('#etime').datetimepicker();
})
</script>
<?php
include getTpl('footer', 'public');
?>