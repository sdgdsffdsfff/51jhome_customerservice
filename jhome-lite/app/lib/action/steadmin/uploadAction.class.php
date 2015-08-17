<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of uploadAction
 *
 * @author xlp
 */
class uploadAction extends commonAction {

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
    }

    function index() {
        $id = $this->_get('id', 'img_url');
        $dir = $this->_get('path', '');
        if (!checkPath($dir)) {
            $dir = 'user';
        }
        $ids = $this->_get('ids', 0);
        if (!$id) {
            showError('参数错误');
        }
        $upload_img_type = str_replace('|', ',', C('upload', 'pic_type'));
        $this->assign(array('upload_img_type' => $upload_img_type, 'id' => $id, 'ids' => $ids, 'dir' => $dir));
        $this->display('index');
    }

    //编辑器上传
    function save() {
        if (parent::_checkLogin(true)) {
            $path = $this->_get('p', 'pic');
            if (!checkPath($path) || !in_array($path, array('goods', 'pic'))) {
                $path = 'pic';
            }
            load('upload');
            $myUpload = new Myupload();
            $upload = $myUpload->upload($path);
            $upload = parent::_sendImageToYunServer($upload); //同步图片到云存储
            if ($upload) {
                $upload = $upload[0];
                if (!$upload) {
                    echo json_encode(array('url' => '', 'fileType' => '', 'original' => '', 'state' => $myUpload->getErrorMsg()));
                } else {
                    echo json_encode(array('url' => getImgUrl($upload['savepath']), 'fileType' => $upload['type'], 'original' => $upload['realname'], 'state' => 'SUCCESS'));
                }
            }
        } else {
            echo json_encode(array('url' => '', 'fileType' => '', 'original' => '', 'state' => '上传失败'));
            exit;
        }
    }

    function up() {
        $id = $this->_post('id', 'img_url');
        $ids = $this->_post('ids', 0);
        $dir = $this->_get('path', '');
        if (!checkPath($dir)) {
            $dir = 'user';
        }
        if (isHave($_FILES['upimg']) && !isHave($_FILES['upimg']['error'])) {
            load('upload');
            $myUpload = new Myupload();
            $upload = $myUpload->upload($dir . '/u' . steadmin::$adminInfo['user_id']);
            $upload = parent::_sendImageToYunServer($upload); //同步图片到云存储
            if ($upload) {
                if (!$upload[0]) {
                    echo "<script>alert('" . $myUpload->getErrorMsg() . "');history.go(-1);</script>";
                    exit;
                }
                $upload[0]['savepath'] = str_replace('./', '', $upload[0]['savepath']);
                echo "<script>window.parent.document.getElementById('" . $id . "').value='" . $upload[0]['savepath'] . "';\r\n";
                echo "window.location.href='" . U('upload/index', array('id' => $id, 'ids' => $ids, 'path' => $dir)) . "';</script>\r\n";
                exit();
            }
        } else {
            echo "<script>alert('" . $_FILES['upimg']['error'] . "');history.go(-1);</script>";
            exit;
        }
    }

    function feedbackUpload() {
        $id = $this->_get('id', 'img_url');
        $dir = $this->_get('path', '');
        if (!checkPath($dir)) {
            $dir = 'user';
        }
        $ids = $this->_get('ids', 0);
        if (!$id) {
            showError('参数错误');
        }
        $upload_img_type = chop(str_replace('|', ',', C('upload', 'attach_type')), "|");
        $upload_img_type .= ','.str_replace('|', ',', C('upload', 'pic_type'));
        $this->assign(array('upload_img_type' => $upload_img_type, 'id' => $id, 'ids' => $ids, 'dir' => $dir));
        $this->display('index');
    }

}
