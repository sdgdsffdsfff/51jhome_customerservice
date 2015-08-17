<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of villageModel
 * 小区模型
 * @author xlp
 */
//载入辅助处理函数库
loadAppFile('distance.fun');

class villageModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'village';
    }

    /*
     * 根据vid获取小区信息
     * @param $vid int 小区id
     * @return array
     */

    public function getVillageById($vid) {
        return $this->where(array('vid' => $vid))->find();
    }

    /*
     * 根据坐标经纬度获取周边小区信息
     * @param $lng int 经度
     * @param $lat int 纬度
     * @return array
     */

    public function getAround($x, $y, $type, $km = 5, $p = 1, $id = 0, $pageShow = 15, $returnResult = true) {
//        saveLog('changeMap/gps', array($x,$y));
//        $change = changeGpsToMap($x, $y);
//        if ($change) {
//            $x = $change['lng'];
//            $y = $change['lat'];
//            saveLog('changeMap/gps', $change);
//        }
        $squares = getAround($x, $y, $km); //stype=' . $type . ' AND  暂时取消类型限制
        $sql = 'select vid,title,pic_url,area_id,address,lat,lng,service_id,is_open from __TABLE__ where lat>=' .
                $squares['minLat'] . ' and lat<=' . $squares['maxLat'] . ' and lng>=' . $squares['minLng'] .
                ' and lng<=' . $squares['maxLng'] . ' LIMIT 0,200';
        $rs = $this->query($sql);
//        print_r($rs);exit;
        if ($returnResult) {
            return getResult($rs, $x, $y, $km, $p, $id, $pageShow, 'vid', 'lng', 'lat');
        } else {
            return $squares;
        }
    }

    /**
     * 获取指定ID集合的小区信息
     * @param  Array $id_arr 小区ID数组
     * @param  String $field 字段名
     * @return Array
     */
    public function infoAll($id_arr, $field = '*') {
        $id = '';
        foreach ($id_arr as $v) {
            $id .= $v . ',';
        }
        $id = rtrim($id, ',');
        $result = $this->field($field)->where('`vid` in (' . $id . ')')->findAll();

        return $result;
    }

}
