<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of albumModel
 * 用户上传图片模型
 * @author xlp
 */
class albumModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'album';
    }

    /*
     * 添加照片
     * @param $data array 添加的数组，没有的内容会采用数据库默认值
     * @return int 新记录的id
     */

    public function addphoto($data) {
        $objData = array();
        $list = $this->getTableFields();
//        print_r($list);exit;
        foreach ($list['fields'] as $key => $val) {
            $objData[$key] = isHave($data[$key]) ? $data[$key] : $val['value'];
        }
        $objData['infotime'] = TIME;
        $objData['status'] = 1;
        $objData['real_size'] = $objData['real_size'];
        if (!$objData['real_size'] && $this->isLocationImg($objData['url'])) {
            $objData['real_size'] = abs(filesize(ROOT . $objData['url']));
        }
        return $this->insert($objData);
    }

    /*
     * 删除照片
     * @param $id int 要删除的照片id
     * @param $uid int 操作者UID
     * @return int 影响记录数
     */

    public function delPhoto($id, $uid) {
        $rs = $this->where(array('id' => $id))->find();
        if (!$rs || $rs['uid'] != $uid) {
            return false;
        }
        $isLocationImg = $this->isLocationImg($rs['url']);
        //删除图片文件
        if (!$isLocationImg) {
            $this->deleteYunImg($rs['url']);
        } else {
            @unlink(ROOT . $rs['url']);
        }
        //生成了缩略图则将对应缩略图一起删除,生成多少版本就应该删除多少
        if ($rs['has_thumb'] && $isLocationImg) {
            $list = C('upload', 'thumb_size');
            foreach ($list as $k => $val) {
                $path = getThumb($rs['url'], $k, true);
                $path && @unlink(ROOT . $path);
            }
        }
        //删除照片记录
        return $this->delete(array('id' => $id));
    }

    /*
     * 标记生成缩略图
     * @param $ids int 记录ID
     * @return int 影响记录数
     */

    public function setPhotoHasThumb($ids) {
        if (!$ids) {
            return 0;
        }
        if (is_array($ids)) {
            $ids = implode(',', array_map('intval', $ids));
        }
        if ($ids) {
            return $this->update(array('has_thumb' => 1), "id IN (" . $ids . ")");
        } else {
            return 0;
        }
    }

    /*
     * 根据ID串获取照片列表
     * @param $uid int 用户UID
     * @param $ids array 原始的ids串，从数据库中直接取出
     * @param $setSize int 生成指定的缩略图的编号
     * @return array
     */

    public function getPhotosById($uid, $ids, $setSize = 0) {
        if (!$ids) {
            return array();
        }
        $rs = array();
        if ($ids) {
            $rs = $this->field('id,title,url,real_size,has_thumb,status')->where('id IN(' . $ids . ') AND uid=' . $uid)->findAll(false);
            if ($rs) {
//                $size = $this->getTotal() == 1 ? 150 : 150;
                $size = C('upload', 'thumb_size');
                $idList = array();
                foreach ($rs as $key => $val) {
                    if ($val['status'] == 1) {
                        $rs[$key]['thumb'] = getThumb($val['url'], $setSize, false);
//                    $rs[$key]['real_size'] = changeFileSize($val['real_size']);
//                    $rs[$key]['size'] = $size[$setSize];
                        if (!$val['has_thumb']) {
                            $rs[$key]['has_thumb'] = 1;
                            $idList[] = $val['id'];
                        }
                    } else {
                        $rs[$key]['status'] = 0;
                    }
                }
                if ($idList) {
                    $this->setPhotoHasThumb($idList);
                }
            }
        }
        return $rs;
    }

    /*
     * 判断是否是本地图片
     */

    public function isLocationImg($src = '') {
        if ($src && (stripos($src, 'upload') === 0 || stripos($src, '/upload') === 0)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 删除云储存的图片
     */

    public function deleteYunImg($url = '') {
        $upConf = C('upload');
        T('image/upyun');
        try {
            $upyun = new UpYun($upConf['yun']['bucket'], $upConf['yun']['user'], $upConf['yun']['pwd']);
            return $upyun->delete('/' . $url);
        } catch (Exception $e) {
//            echo $e->getCode().$e->getMessage();
        }
        return false;
    }

}
