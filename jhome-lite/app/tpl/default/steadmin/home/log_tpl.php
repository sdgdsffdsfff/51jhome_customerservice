<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'home-log', //页面标示
    'pagename' => '微信访问日志', //当前页面名称
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
    <!-- main padder --> 
    <section class="main padder">
        <div class="clearfix"> 
			<h4><i class="fa fa-table"></i><?php echo trim($Document['pagename'],'_');?></h4> 
        </div>
		
        <div class="row">
			<!-- table 11 -->
			<div class="col-lg-12"> 
				<section class="panel">
                <!---->
                <div class="panel-body">
                  <div class="row text-small">
                    <div class="col-sm-4">
                        <div class="block btn-group">
                           <a class="btn btn-default" href="<?php echo U('home/log',array('all'=>1));?>">全部</a>
                           <a class="btn btn-default" href="<?php echo U('home/log',array('status'=>1,'uid'=>$uid));?>">菜单</a>
                           <a class="btn btn-default" href="<?php echo U('home/log',array('status'=>2,'uid'=>$uid));?>">文本</a>
                           <a class="btn btn-default" href="<?php echo U('home/log',array('status'=>4,'uid'=>$uid));?>">图片</a>
                           <a class="btn btn-default" href="<?php echo U('home/log',array('status'=>5,'uid'=>$uid));?>">语音</a>
                           <a class="btn btn-default" href="<?php echo U('home/log',array('status'=>10,'uid'=>$uid));?>">二维码</a>
                        </div>
                    </div>
                    <div class="col-sm-4">
                    <label class="col-md-3 control-label text-right">刷新时间</label>
                <div class="col-sm-6 text-left">
                 <select class="form-control" name="setTime" id="setTime">
                 	<option value="5">5秒</option>
                    <option value="10" selected="selected">10秒</option>
                    <option value="30">30秒</option>
                    <option value="60">1分钟</option>
                    <option value="300">5分钟</option>
                 </select>
         		</div>
                    </div>
                    <div class="col-sm-4">
                      <form action="<?php echo U(ACTION_NAME.'/log');?>" method="get" name="form1" target="_self">
                        <div class="input-group">
                          <input type="text" name="q" class="input-sm form-control" placeholder="请输入搜索关键字..." />
                          <input name="g" type="hidden" value="<?php echo GROUP_NAME;?>" />
                          <input name="c" type="hidden" value="<?php echo ACTION_NAME;?>" />
                          <input name="m" type="hidden" value="<?php echo MODEL_NAME;?>" />
                          <div class="input-group-btn">
                           <button data-toggle="dropdown" class="btn btn-white btn-sm dropdown-toggle">
                           <span class="dropdown-label">留言</span><span class="caret"></span></button>
                           <ul class="dropdown-menu dropdown-select pull-right">
                           	<li class="active"><a href="#"><input type="radio" checked="checked" name="st" value="content">留言</a></li>
                            <li class=""><a href="#"><input type="radio" name="st" value="user">用户名</a></li>
                            <li class=""><a href="#"><input type="radio" name="st" value="uid">UID</a></li>
                           </ul> 
                          </div>
                          <span class="input-group-btn">
                          <button class="btn btn-sm btn-white" type="submit">搜索</button>
                          </span> </div>
                      </form>
                    </div>
                  </div>
                </div>
                <!---->
					<div class="table-responsive"> 
						<table class="table table-striped b-t text-small"> 
							<thead>
                              <tr>
                                <th align="center">ID</th>
                                <th align="center">UID</th>
                                <th align="center">昵称</th>
                                <th align="center">消息ID</th>
                                <th align="center">消息类型</th>
                                <th align="center">消息内容</th>
                                <th align="center">发布时间</th>
                                <th align="center" width="100">操作</th>
                              </tr>
                            </thead>
                            <tbody id="show-content-table">
                             <?php include getTpl('log_item');?>
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
<script>
	var lastId=<?php echo $lastId;?>;
	var ftime=<?php echo $ftime;?>;
	var getDataUrl='<?php echo U('home/log');?>';
	var setp=null;
	var status=<?php echo $status;?>;
	var uid=<?php echo $uid;?>;
	var q='<?php echo $q;?>';
	var st='<?php echo $st;?>';
function getData(){
	$.getJSON(getDataUrl,{"last_id":lastId,"is_ajax":1,"status":status,"q":q,"st":st,"uid":uid},function(res){
		if (res.status==1){
			if(res.data.newData){
				var item = $(res.data.item).hide()
				item.insertBefore('#show-content-table');
				item.slideDown("slow");
				//$("#show-content-table").prepend(res.data.item);
				$('a[rel*=pop]').unbind().facebox();
				//item.slideDown("slow");
				lastId=res.data.lastId;
				if (res.data.count){
					Msg.ok(res.data.count+'条新消息',function(){},2000);
				}
			}
		}else{
			Msg.error(res.info);
		}
	});
}
$(function(){
	$("#setTime").change(function(){
		ftime=$(this).val();
		//console.log(ftime);
		clearInterval(setp);
		setp=window.setInterval('getData()',ftime*1000);
		Msg.ok('设置刷新时间：'+ftime+'秒');
	})
})
setp=window.setInterval('getData()',ftime*1000);
</script>
<?php
include getTpl('footer', 'public');
?>