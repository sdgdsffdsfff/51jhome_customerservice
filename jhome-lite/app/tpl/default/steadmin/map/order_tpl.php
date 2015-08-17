<!--map B-->
    <div class="location-box" style="background:#fff; padding-bottom:20px;">
        <div id="map-container" style="width:600px; height:450px"></div>
    </div>
    <div id="ajax-data"></div>
<!--map E-->
<!-- map.js B-->
<script>
var map=null;
var init = function() {
    var center = new qq.maps.LatLng(<?php echo $rs['user_lat']?$rs['user_lat']:'30.271855'?>,<?php echo $rs['user_lng']?$rs['user_lng']:'120.163254'?>);
    map = new qq.maps.Map(document.getElementById('map-container'),{
		//disableDefaultUI: true,
        center: center,
        zoom: 14
    });
	//实时路况图层
    var layer = new qq.maps.TrafficLayer();
    layer.setMap(map);
	var marker=new qq.maps.Marker({
	position:center,
	animation:qq.maps.MarkerAnimation.DROP,
	map:map
	});
};
init();
$(function(){
	Msg.loading('正在读取位置...');
	$.getJSON('<?php echo U('map/map_data');?>',{"id":<?php echo $rs['order_id'];?>},function(res){
		Msg.hide();
		if(res.status==1){
			$('#ajax-data').html(res.data);
		}else{
			Msg.error(res.info);
		}	
	});
})
</script>
<!-- map.js E-->