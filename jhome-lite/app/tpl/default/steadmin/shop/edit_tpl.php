<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
$Document = array(
    'pageid' => 'shop-edit', //页面标示
    'pagename' => '编辑店铺', //当前页面名称
    'mycss' => array(), //加载的css样式表
    'myjs' => array(), //加载的js脚本
	'footerjs'=>array('content/fuelux/fuelux','content/combodate/moment.min','content/combodate/combodate','content/calendar'),
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
<section id="content">
<link rel="stylesheet" media="screen" href="<?php echo PUBLIC_PATH; ?>jquery-ui/css/smoothness/jquery-ui-1.10.0.custom.min.css"/>
<script type="text/javascript" src="<?php echo PUBLIC_PATH; ?>jquery-ui/js/jquery-ui-1.10.0.custom.min.js"></script> 
<script type="text/javascript" src="<?php echo PUBLIC_PATH; ?>jquery-ui/js/jquery-ui-timepicker-addon.js"></script>
  <section class="main padder">
    <div class="clearfix">
      <h4><i class="fa fa-edit"></i><?php echo trim($Document['pagename'],'_');?></h4>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <section class="panel">
          <div class="panel-body">
            <form class="form-horizontal" method="post" id="form1">
              <div class="form-group">
                <label class="col-sm-1 control-label">店铺名称</label>
                <div class="col-sm-3">
                  <input type="text" name="shop_name" id="shop_name" placeholder="" class="form-control" value="<?php echo $rs['shop_name'];?>" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">分店名称</label>
                <div class="col-sm-3">
                  <input type="text" name="shop_alt_name" id="shop_alt_name" placeholder="" class="form-control" value="<?php echo $rs['shop_alt_name'];?>" />
                  <span class="help-block">品牌及连锁店的分店名称，没有则不填</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">店铺LOGO</label>
                <div class="col-sm-3">
                  <input type="text" name="pic_url" id="pic_url" placeholder="" class="form-control" value="<?php echo $rs['pic_url'];?>" />
                  <span class="help-block">请上传您的LOGO图标，标准尺寸150px*150px <?php if ($rs['pic_url']){?>[<a rel="pop" class="red" target="_blank" href="<?php echo getImgUrl($rs['pic_url']);?>">预览>></a>]<?php }?></span></div>
                <div class="col-sm-3">
                  <iframe width="280" height="24" src="<?php echo U('upload/index',array('id'=>'pic_url')); ?>" scrolling="no" frameborder="0"></iframe>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">所属区域</label>
                <div class="col-sm-3">
                  <select class="day form-control" style="width:auto;" name="area_id" id="area_id">
                    <?php
						foreach($area as $val){
					?>
                    <option value="<?php echo $val['aid'];?>" <?php if($rs['area_id']==$val['aid']){echo 'selected="selected"';}?>><?php echo $val['name'];?></option>
					<?php }?>
                    </select>
              </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">所属服务中心</label>
                <div class="col-sm-3">
                  <select class="day form-control" style="width:auto;" name="service_id" id="service_id">
                    <?php
						foreach($service as $val){
					?>
                    <option value="<?php echo $val['sid'];?>" <?php if($rs['service_id']==$val['sid']){echo 'selected="selected"';}?>><?php echo $val['stitle'];?></option>
					<?php }?>
                    </select>
              </div>
              </div>
              
              <div class="form-group">
                <label class="col-sm-1 control-label">店铺类型</label>
                <div class="col-sm-10">
                  <div class="row">
                    <?php
						foreach($setting['shop_type'] as $key=>$val){
					?>
                    <div class="col-xs-1 radio m-l-large select_no_result_type">
                      <label class="radio-custom">
                        <input type="radio" name="shop_type" value="<?php echo $key;?>" <?php if ($key==$rs['shop_type']){echo 'checked';}?>/>
                        <i class="fa fa-circle-o <?php if ($key==$rs['shop_type']){echo 'checked';};?>"></i><?php echo $val;?></label>
                    </div>
                    <?php
						}
					?>
                  </div>
                  <p class="help-block"></p>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">正在参与的活动</label>
                <div class="col-sm-10">
                  <div class="row">
                    <?php
						foreach($setting['shop_tips'] as $key=>$val){
					?>
                    <div class="col-xs-1 radio m-l-large select_no_result_type">
                      <label class="checkbox-custom">
                        <input type="checkbox" name="tips_list[]" value="<?php echo $key;?>" <?php if (in_array($key,$rs['tips_list'])){echo 'checked';}?>/>
                        <i class="fa fa-check-square-o <?php if (in_array($key,$rs['tips_list'])){echo 'checked';};?>"></i><?php echo $val;?></label>
                    </div>
                    <?php
						}
					?>
                  </div>
                  <p class="help-block"></p>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">资金结算方式</label>
                <div class="col-sm-3">
                  <select class="day form-control" style="width:auto;" name="settlement" id="settlement">
                    <?php
						foreach($adminData['settlement'] as $key=>$val){
					?>
                    <option value="<?php echo $key;?>" <?php if($rs['settlement']==$key){echo 'selected="selected"';}?>><?php echo $val;?></option>
					<?php }?>
                    </select>
              </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">店铺等级</label>
                <div class="col-sm-3">
                  <select class="day form-control" style="width:auto;" name="shop_power" id="shop_power">
                    <?php
						foreach($setting['shop_power'] as $key=>$val){
					?>
                    <option value="<?php echo $key;?>" <?php if($rs['shop_power']==$key){echo 'selected="selected"';}?>><?php echo $val;?></option>
					<?php }?>
                    </select>
              </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">电话</label>
                <div class="col-sm-3">
                  <input type="text" name="tel" id="tel" placeholder="0571-85888888" class="form-control" value="<?php echo $rs['tel'];?>" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">人均消费</label>
                <div class="col-sm-1">
                  <input type="text" style="width:80px" name="avg_price" id="avg_price" placeholder="" class="form-control" value="<?php echo $rs['avg_price'];?>" />
                  <span class="help-block"></span></div>
                  <div style="padding-left:0;" class="col-sm-1 help-block">元</div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">综合评分</label>
                <div class="col-sm-1">
                  <input type="text" style="width:80px" name="score_total" id="score_total" placeholder="" class="form-control" value="<?php echo $rs['score_total'];?>" />
                  <span class="help-block">满分：10.0</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">口味评分</label>
                <div class="col-sm-1">
                  <input type="text" style="width:80px" name="score_flavour" id="score_flavour" placeholder="" class="form-control" value="<?php echo $rs['score_flavour'];?>" />
                  <span class="help-block">满分：10.0</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">服务评分</label>
                <div class="col-sm-1">
                  <input type="text" style="width:80px" name="score_service" id="score_service" placeholder="" class="form-control" value="<?php echo $rs['score_service'];?>" />
                  <span class="help-block">满分：10.0</span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">准点率</label>
                <div class="col-sm-1">
                  <input type="text" style="width:80px" name="ontime_point" id="ontime_point" placeholder="填整数" class="form-control" value="<?php echo $rs['ontime_point'];?>" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">营业时间</label>
                <div class="col-sm-3">
                  <input style="width:180px" type="text" name="stime" id="stime" placeholder="" class="form-control" value="<?php echo $rs['stime'];?>" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">打烊时间</label>
                <div class="col-sm-3">
                  <input style="width:180px" type="text" name="etime" id="etime" placeholder="" class="form-control" value="<?php echo $rs['etime'];?>" />
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">延时配送</label>
                <div class="col-sm-10">
                  <div class="row">
                    <?php
						foreach($setting['status'] as $key=>$val){
					?>
                    <div class="col-xs-1 radio m-l-large select_no_result_type">
                      <label class="radio-custom">
                        <input type="radio" name="is_delay" value="<?php echo $key;?>" <?php if ($key==$rs['is_delay']){echo 'checked';}?>/>
                        <i class="fa fa-circle-o <?php if ($key==$rs['is_delay']){echo 'checked';};?>"></i><?php echo $val;?></label>
                    </div>
                    <?php
						}
					?>
                  </div>
                  <p class="help-block">开启后用户配送时间会往后顺延30分钟</p>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">热卖商品</label>
                <div class="col-sm-10">
                  <div class="row">
                    <?php
						foreach($goods as $val){
					?>
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="hot_goods[]" <?php if(in_array($val['gid'],$rs['hot_goods'])){echo 'checked="checked"';}?> value="<?php echo $val['gid'];?>"/> <?php echo $val['goods_name'];?></label>
                    </div>
                    <?php
						}
					?>
                  </div>
                  <p class="help-block"></p>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">地址</label>
                <div class="col-sm-6">
                  <textarea class="form-control" id="address" name="address"  style="height:80px"><?php echo $rs['address'];?></textarea>
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">店铺描述</label>
                <div class="col-sm-6">
                  <textarea class="form-control" id="content" name="content"  style="height:80px"><?php echo $rs['content'];?></textarea>
                  <span class="help-block"></span></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">经纬度</label>
                <div class="col-sm-2">
                  <input type="text" name="lng" id="lng" placeholder="经度坐标..." class="form-control" value="<?php echo $rs['lng'];?>" />
                </div>
                <div class="col-sm-2">
                  <input type="text" name="lat" id="lat" placeholder="纬度坐标..." class="form-control" value="<?php echo $rs['lat'];?>" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">搜索</label>
                <div class="col-sm-3">
                  <input type="text" name="set_address" id="set_address" placeholder="" class="form-control" value="" />
                  <span class="help-block">直接通过地址获取坐标</span></div>
                  <div class="col-sm-3"><a href="javascript:;" id="get_address" class="btn btn-default">搜索</a></div>
              </div>
              <div class="form-group">
                <label class="col-sm-1 control-label">地图</label>
                <div class="col-sm-5">
                <script charset="utf-8" src="http://map.qq.com/api/js?v=2.exp&key=1aa6a77c877c9d026f2f7640bb722f41"></script>
				<script>
				var geocoder,map,marker = null;
				var centerInfo={"city":"杭州","lat":<?php echo $rs['lat']?$rs['lat']:'30.271855';?>,"lng":<?php echo $rs['lng']?$rs['lng']:'120.163254';?>};
                var init = function() {
					var center = new qq.maps.LatLng(centerInfo.lat,centerInfo.lng);
                    var map = new qq.maps.Map(document.getElementById("container"),{
                        center: center,
                        zoom: 16
                    });
					var marker = new qq.maps.Marker({
						position: center,
						map: map
					});
					geocoder = new qq.maps.Geocoder({
						complete : function(result){
							Msg.hide();
							marker.setMap(null);
							map.setCenter(result.detail.location);
							marker = new qq.maps.Marker({
								map:map,
								position: result.detail.location
							});
						}
					});
                    qq.maps.event.addListener(map, 'click', function(event) {
						var lng=event.latLng.getLng().toFixed(8);
						var lat=event.latLng.getLat().toFixed(8);
						$("#lng").val(lng);
						$("#lat").val(lat);
						marker.setMap(null);
						center=new qq.maps.LatLng(lat,lng);
						map.setCenter(center);
						marker = new qq.maps.Marker({
							map:map,
							position: center
						});
						Msg.loading('正在解析地址...');
						$.getJSON('<?php echo U('map/address');?>',{"x":lng,"y":lat},function(res){
							Msg.hide();
							if (res.status){
								$("#address").val(res.data.path);
							}
						});
                    });
                }
				$("#get_address").click(function(){
					var address = $("#set_address").val();
					if (!address){
						Msg.error('请输入地址');
						return false;	
					}
					Msg.loading('正在解析数据...');
					geocoder.getLocation(centerInfo.city+","+address);
				})
                </script>
                <style>#container img{max-width:none}</style>
                  <div style="width:600px;height:400px" id="container"></div>
                </div>
              </div>
              
              <div class="form-group">
                <div class="col-sm-9 col-lg-offset-1">
                  <div class="col-sm-1">
                  <input name="id" type="hidden" value="<?php echo $rs['shop_id'];?>" />
                    <button type="submit" id="sub-ok" data-loading-text="正在提交..." class="btn btn-primary">保存</button>
                  </div>
                  <div class="col-md-offset-2">
                    <button type="button" class="btn btn-white">取消</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </section>
      </div>
    </div>
  </section>
</section>
<!--主体 结束--> 
<script>
$(function(){
	$('#stime').timepicker({timeFormat: "HH:mm"});
    $('#etime').timepicker({timeFormat: "HH:mm"});
	$('#form1').submit(function(){
		var shop_name=$.trim($("#shop_name").val());
		var isOk=true;
		if (!shop_name){
			Msg.error('店铺名称不能为空');
			resetSubmit("#sub-ok",'保存');
			return false;
		}
		Msg.loading();
		  $.post('<?php echo U('shop/save');?>',$(this).serialize(),function(result){
			  Msg.hide();
			  resetSubmit("#sub-ok",'保存');
			  if (result.status==1){
				Msg.ok('编辑成功',function(){
					//document.location='<?php echo U('shop/index');?>';
					history.go(-1);
				},1000);  
			}else{
				Msg.error(result.info);
			}
		  },'json');
		  return false;
	});
	init();
})
</script>
<?php
include getTpl('footer', 'public');
?>