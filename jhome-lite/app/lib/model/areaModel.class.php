<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of areaModel
 * 区域模型
 * @author xlp
 */
class areaModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'area';
    }

    /*
     * 根据aid获取小区信息
     * @param $aid int 区域id
     * @return array
     */

    public function getAreaById($aid, $field = 'aid') {
        return $this->where(array($field => $aid))->find();
    }

//根据aid递归获取关系树
    public function getAreaListByAid($aid) {
        $objData = array('area_level' => 0, 'area' => array(), 'street' => array(), 'community' => array());
        $rs = $this->getAreaById($aid, 'aid');
        if ($rs) {
            switch ($rs['area_level']) {
                case 0://社区
                    $objData['area_level'] = 0;
                    $objData['community'] = &$rs;
                    $objData['street'] = $this->getAreaById($rs['fid'], 'aid');
                    $objData['area'] = $this->getAreaById($objData['street']['fid'], 'aid');
                    break;
                case 1://街道
                    $objData['area_level'] = 1;
                    $objData['community'] = $this->where(array('fid' => $rs['aid']))->findAll();
                    $objData['street'] = &$rs;
                    $objData['area'] = $this->getAreaById($objData['street']['fid'], 'aid');
                    break;
                case 2://城区
                    $objData['area_level'] = 2;
                    $objData['street'] = $this->where(array('fid' => $rs['aid']))->findAll();
                    if ($objData['street']) {
                        foreach ($objData['street'] as $k => $v) {
                            $objData['street'][$k]['community'] = $this->where(array('fid' => $v['aid']))->findAll();
                        }
                    }
                    $objData['area'] = &$rs;
                    break;
            }
        }
        return $objData;
    }

    //根据aid删除相关区域信息，并且会清除小区的绑定信息
    //TODO 关系树暂时未处理，应该需要同步清理
    public function delAreaInfo($aid) {
        $info = $this->getAreaListByAid($aid);
        $delIds = array();
        $objData = array();
        $delIds[] = $aid;
        //根据当前不同级别，整理出要删除的id串
        switch ($info['area_level']) {
            case 0://社区
                D('village')->update(array('community_id' => 0), array('community_id' => $aid));
                break;
            case 1://街道
                foreach ($info['community'] as $val) {
                    $delIds[] = $val['aid'];
                    $objData[] = $val['aid'];
                }
                if ($objData) {
                    D('village')->update(array('street_id' => 0, 'community_id' => 0), 'street_id=' . $aid . ' AND community_id IN (' . implode(',', $objData) . ')');
                }
                break;
            case 2://城区
                foreach ($info['street'] as $val) {
                    $delIds[] = $val['aid'];
                    foreach ($val['community'] as $v) {
                        $delIds[] = $v['aid'];
                    }
                }
                D('village')->update(array('area_id' => 0, 'street_id' => 0, 'community_id' => 0), array('area_id' => $aid));
                break;
        }
        $delIds = implode(',', $delIds);
        //删除管理员
        M('admin')->delete(' aid IN (' . $delIds . ')');
        //删除管理统计
        M('admin_count')->delete(' aid IN (' . $delIds . ')');
        //文章
        M('article')->delete(' aid IN (' . $delIds . ')');
        //通知
        M('notice')->delete(' aid IN (' . $delIds . ')');
        //删除所有区域
        return $this->delete(' aid IN (' . $delIds . ')');
    }

    /*
     * 根据aid获取所有的小区village_id
     */

    public function getVillageIdByAid($aid) {
        $objData = array();
        $ids = array();
        $rs = $this->getAreaById($aid, 'aid');
        $field = '';
        if ($rs) {
            switch ($rs['area_level']) {
                case 0://社区
                    $field = 'community_id';
                    break;
                case 1://街道
                    $field = 'street_id';
                    break;
                case 2://城区
                    $field = 'area_id';
                    break;
            }
            $objData = D('village')->field('vid')->where(array($field => $aid))->findAll();
        }
//        z(D('village')->getAllsql());
        if ($objData) {
            foreach ($objData as $val) {
                $ids[] = $val['vid'];
            }
        }
        return $ids;
    }

}
