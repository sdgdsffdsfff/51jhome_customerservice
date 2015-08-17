<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 坐标周边内容计算辅助函数
 */

/*
 * 根据坐标获取范围
 * @param $x int 经度
 * @param $y int 纬度
 * @param $km int 半径值
 */

function getAround($x, $y, $km = 5) {
    $km = $km * 1000;
    $PI = 3.14159265;
    $degree = (24901 * 1609) / 360.0;
    $dpmLat = 1 / $degree;
    $squares = array();
    $radiusLat = $dpmLat * $km;
    $minLat = $y - $radiusLat;
    $maxLat = $y + $radiusLat;
    $squares['maxLat'] = $maxLat;
    $squares['minLat'] = $minLat;
    $mpdLng = $degree * cos($y * ($PI / 180));
    $dpmLng = 1 / $mpdLng;
    $radiusLng = $dpmLng * $km;
    $minLng = $x - $radiusLng;
    $maxLng = $x + $radiusLng;
    $squares['maxLng'] = $maxLng;
    $squares['minLng'] = $minLng;
    return $squares;
}

/*
 * 根据经纬度计算距离 其中A($lat1,$lng1)、B($lat2,$lng2) 
 * 计算两个点之间的距离
 * @param $lat1 int 纬度1
 * @param $lng1 int 经度1
 * @param $lat2 int 纬度2
 * @param $lng2 int 经度2
 */

function getDistance($lat1, $lng1, $lat2, $lng2) {
    if ($lat1 == $lat2 && $lng1 == $lng2) {
        return 0;
    }
    // 地球半径 
    $R = 6378137;
    // 将角度转为狐度 
    $radLat1 = deg2rad($lat1);
    $radLat2 = deg2rad($lat2);
    $radLng1 = deg2rad($lng1);
    $radLng2 = deg2rad($lng2);
    // 结果 
    $s = acos(cos($radLat1) * cos($radLat2) * cos($radLng1 - $radLng2) + sin($radLat1) * sin($radLat2)) * $R;
    // 精度 
    $s = round($s * 10000) / 10000;
    return round($s);
}

/*
 * 将记录集中的数据进行整理
 * @param $rs array 记录集
 * @param $x int x轴
 * @param $y int y轴
 * @param $km int 距离（单位：米）
 * @param $p int 页码
 * @param $id int 需要排除的记录
 * @param $pageShow int 每页显示数量
 * @param $key string 主键
 * @param $lng string 经度
 * @param $lat string 纬度
 */

function getResult(&$rs, $x, $y, $km, $p, $id, $pageShow, $myKey, $lng = 'lng', $lat = 'lat') {
    $km = $km * 1000;
    $data = array('data' => $rs, 'total_count' => 0, 'total_page' => 0);
// 算出实际距离
    if ($data['data']) {
        $sortdistance = array();
        foreach ($data['data'] as $key => $val) {
            $distance = getDistance($x, $y, $val[$lng], $val[$lat]);
            if ($val[$myKey] != $id && $distance <= $km) {
                $data['data'][$key]['distance'] = $distance;
                $sortdistance[$key] = $distance; // 排序列
            } else {
                unset($data['data'][$key]);
            }
        }
        if ($sortdistance) {// 距离排序
            array_multisort($sortdistance, SORT_ASC, $data['data']);
            $data['total_count'] = count($data['data']);
            $data['total_page'] = ceil($data['total_count'] / $pageShow);
            if ($p > $data['total_page']) {
                $p = $data['total_page'];
            }
            $data['data'] = array_slice($data['data'], ($p - 1) * $pageShow, $pageShow);
        }
    }
    return $data;
}

/*
 * 将GPS坐标转换为腾讯地图坐标
 */

function changeGpsToMap($x, $y) {
    $rs = json_decode(getHttp('http://apis.map.qq.com/ws/coord/v1/translate?locations=' .
                    $y . ',' . $x . '&type=1&key=1aa6a77c877c9d026f2f7640bb722f41'), true);
    if ($rs['status'] == 0 && isset($rs['locations']) && is_array($rs['locations'])) {
        return $rs['locations'][0];
    } else {
        return false;
    }
}
