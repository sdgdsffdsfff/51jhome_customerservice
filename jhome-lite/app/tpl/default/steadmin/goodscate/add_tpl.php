<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'goodscate-add', //页面标示
    'pagename' => '分类添加', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	//'footerjs'=>array('content/highcharts', 'content/exporting',),
	  'footerjs'=>array('content/fuelux/fuelux', 'content/combodate/moment.min'),
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
	<section class="main padder"> 
    <div class="clearfix"> 
     <h4><i class="fa fa-edit"></i>分类新增</h4> 
    </div> 
    <div class="row"> 
     <div class="col-sm-12"> 
      <section class="panel"> 
       <div class="panel-body"> 
        <form class="form-horizontal" id="cateAddForm" method="post" > 
         <div class="form-group"> 
          <label class="col-sm-1 control-label">分类名称</label> 
          <div class="col-sm-3"> 
            <input type="text" name="name" id="name" placeholder="分类名称" class="form-control" data-required="true"  />
          </div> 
         </div> 
         
         <div class="form-group"> 
          <label class="col-sm-1 control-label">父类别</label> 
          <div class="col-sm-2"> 
            <select name="select-cateid" id="select-cateid" class="form-control">
            <option value="0">选择类别</option>
            <?php
            foreach($cate as $val){
			?>
             <option value="<?php echo $val['id']?>"><?php echo $val['name'];?></option>
            <?php
			}
			?>
            </select>
          </div>
          <div class="col-sm-2"> 
            <select name="pid" id="pid" class="form-control">
            <option value="0">请先选择大类</option>
            </select>
          </div>
          <div class="col-sm-3" id="show-menu" style="line-height:30px"></div>
         </div>
       
         <div class="form-group"> 
          <label class="col-sm-1 control-label">是否显示</label> 
            <div class="row">
              <div class="col-xs-1 radio m-l-large"><label class="radio-custom"> <input type="radio" name="is_show" checked="checked" value="1" /> <i class="fa fa-circle-o"></i>显示</label></div>
              <div class="col-xs-1 radio m-l-large"><label class="radio-custom"> <input type="radio" name="is_show" value="0" /> <i class="fa fa-circle-o"></i>隐藏</label></div>
            </div>
         </div> 

         <div class="form-group"> 
          <label class="col-sm-1 control-label">排序</label> 
          <div class="col-sm-1"> 
            <input type="number" name="sort"  id="sort" placeholder="" class="form-control" value="0" />
          </div> 
         </div> 

         <div class="form-group"> 
          <div class="col-sm-9 col-lg-offset-3"> 
            <input type="hidden" name="goods_body" id="goods_body">
            <div class="col-sm-1"><button type="submit" id="sub-ok" data-loading-text="正在提交..." class="btn btn-primary">保存</button></div>
            <button type="button" class="btn btn-white col-md-offset-1">取消</button> 
          </div> 
         </div> 
        </form> 
       </div> 
      </section>
     </div> 
    </div> 
   </section> 
  </section> 
  <!--/ content --> 
<!--主体 结束-->
<script>
$(function(){
	$("#pid").change(function(){
		selectCate();	
	})
	function selectCate(){
		var menu=$.trim($("#pid").find("option:selected").text().replace('&emsp;',''));
		$("#show-menu").html('当前类别：<strong class="red">'+menu+'</strong>');	
	}
	$("#select-cateid").change(function(){
		var id=$("#select-cateid option:selected").val();
		if (id=='0'){
			$("#pid").html('<option value="0">请先选择大类</option>');
			return false;
		}
		Msg.loading();
		$.getJSON('<?php echo U('goodscate/cate');?>',{"cid":id,"cateid":0},function(res){
			Msg.hide();
			if (res.status==1){
				$("#pid").html(res.data);
				selectCate();
			}else{
				Msg.error(res.info);
			}	
		})
	})
  $('#cateAddForm').submit(function(){
    if (!$('#name').val()){
      Msg.error('请输入分类名称');
      resetSubmit("#sub-ok",'提交');
      return false;
    }
    Msg.loading();
    $.post('<?php echo U('goodscate/post');?>',$(this).serialize(),function(result){
      Msg.hide();
      resetSubmit("#sub-ok",'提交');
      if (result.status==1){
		Msg.ok('添加成功',function(){
			document.location='<?php echo U('goodscate/index');?>';	
		},1000);  
	  }else{
		Msg.error(result.info);
	  }
    },'json');
    return false;
  });

})
</script>
<?php
include getTpl('footer', 'public');
?>