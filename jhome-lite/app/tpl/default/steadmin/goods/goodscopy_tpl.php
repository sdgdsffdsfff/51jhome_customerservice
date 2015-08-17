<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>

<div style="width:500px; height:250px">
  <div class="row">
    <div class="col-sm-12">
      <div class="form-group">
        <label class="col-sm-3 control-label">店铺</label>
        <div class="col-sm-9"> <span class="red"><?php echo $shop['shop_name'].'【'.$shop['shop_alt_name'].'】';?></span> </div>
      </div>
      <div class="form-group" style="clear:both; margin-top:30px">
        <div class="col-sm-2"><strong>复制到</strong></div>
        <div class="col-sm-5">
          <select name="select-shopid" id="select-shopid" class="form-control">
            <?php
            foreach($rs as $val){
	  ?>
            <option value="<?php echo $val['shop_id']?>"><?php echo $val['shop_name'].'【'.$val['shop_alt_name'].'】';?></option>
            <?php
			}
	  ?>
          </select>
        </div>
      </div>
      <!---->
      <p class="btn-group btn-group-justified" style="clear:both; margin-top:130px"><a class="btn btn-danger" href="javascript:;" id="copy-goods-info">确认复制</a></p>
	<p></p>
      <!----> 
    </div>
  </div>
</div>
<script>
var fshopId=<?php echo $shop['shop_id'];?>;
$(function(){
	$("#copy-goods-info").click(function(){
		var tshopId=$("#select-shopid").val();
		Msg.loading();
		$.post('<?php echo U('goods/savecopy');?>',{"fromshop":fshopId,"toshop":tshopId},function(res){
			Msg.hide();
			if (res.status==1){
				jQuery(document).trigger('close.facebox');
				Msg.ok('共导入<span class="red">'+res.data+'</span>件商品');
			}else{
				Msg.error(res.info);	
			}
		},"json");
	})	
})
</script>