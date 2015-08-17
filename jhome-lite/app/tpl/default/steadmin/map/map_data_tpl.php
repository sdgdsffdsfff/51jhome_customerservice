<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<script>
var express = function() {
	<?php if($orderExpress){
	$arr=array();
	?>
    var path1=[
	<?php foreach($orderExpress as $val){
		$arr[]='new qq.maps.LatLng('.$val['lat'].', '.$val['lng'].")";
	}
	echo implode(",\n",$arr);
	?>
    ];
    var polyline = new qq.maps.Polyline({
        path: path1,
        strokeColor: '#060',
        strokeWeight: 4,
        editable:false,
        map: map
    });
	polyline.setStrokeDashStyle("dash");
	<?php
	if ($center){
	?>
	var newCenter=new qq.maps.LatLng(<?php echo $center['lat'];?>, <?php echo $center['lng'];?>);
	map.panTo(newCenter);
		var anchor = new qq.maps.Point(6, 6),
        size = new qq.maps.Size(24, 24),
        origin = new qq.maps.Point(0, 0),
        icon = new qq.maps.MarkerImage('http://lbs.qq.com/javascript_v2/img/center.gif', size, origin, anchor);
    var marker = new qq.maps.Marker({icon: icon,map: map,position:map.getCenter()});
	<?php }
	}
	?>
};
express();
</script>