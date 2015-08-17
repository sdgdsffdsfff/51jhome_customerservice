<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of upload 上传文件类
 *
 * @author xlp
 */
class Myupload {

    private $filename; //上传文件信息
    public $format = 'all'; // 文件格式限定，为空时不限制格式
    public $maxsize; //上传文件大小限制, 单位M
    private $savenamelenght = 20; //随机保存文件名长度
    private $ext; //文件扩展名
    public $save_date_dir; //分类保存目录
    public $savename; // 保存名
    public $savepath; // 保存路径
    public $overwrite = false; // 覆盖模式
    public $savedir;
    static $_instance = null;
    public $erroMsg = null;

    function __construct() {
        $this->Sys = C('upload');
        $this->maxsize = intval($this->Sys['maxsize']) * (1024 * 1024);
        $this->save_date_dir = $this->Sys['dirtype'];
        $this->savedir = $this->Sys['dir'];
        if ($this->format == 'pic') {//只允许上传图片
            $this->format = $this->Sys['pic_type'];
        } else {
            $this->format = $this->Sys['pic_type'] . '|' . $this->Sys['attach_type'];
        }
        switch ($this->save_date_dir) {
            case '1':$this->save_date_dir = date('Ymd');
                break;
            case '2':$this->save_date_dir = date('Y-m-d');
                break;
            case '3':$this->save_date_dir = date('Y/md');
                break;
            case '4':$this->save_date_dir = date('Y/m/d');
                break;
            default:$this->save_date_dir = date('Y/md');
        }
    }

    /**
     * *  静态方法, 单例统一访问入口
     * *  @return  object  返回对象的唯一实例
     * */
    static public function getInstance() {
        if (is_null(self::$_instance) || !isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function setSavepath($sdir = '') {
        $sdir = $sdir ? '/' . $sdir : '';
        $this->savepath = $this->savedir . $sdir; // 保存路径
        $this->savepath = rtrim($this->savepath, '/') . '/';
    }

//设置文件
    public function setFile($img = '') {
        $this->filename = $img;
    }

    /*
     * 功能：检测并组织文件
     * $form      文件域名称
     * $filename 上传文件保存名称，为空或者上传多个文件时由系统自动生成名称
     * $filename = 1，并上传多个同文件域名称文件时，则文件保存为原上传文件名称。
     */

    public function upload($sdir = '') {
        if (empty($_FILES)) {
            $this->halt('请选择上传文件!');
            return null;
        }
        $this->setSavepath($sdir);
        $outfile = array(); //返回文件名称数组
        foreach ($_FILES as $key => $val) {
            if (is_array($val['name'])) {//上传同文件域名称多个文件
                $count = count($val['name']);
                for ($i = 0; $i < $count; $i++) {
                    $outfile[] = $this->getUploadFile($key, array(
                        'name' => $val['name'][$i],
                        'type' => $val['type'][$i],
                        'tmp_name' => $val['tmp_name'][$i],
                        'error' => isset($val['error'][$i]) ? $val['error'][$i] : '',
                        'size' => $val['size'][$i]
                    ));
                }
            } else {//上传单个文件
                $outfile[] = $this->getUploadFile($key, $val);
            }
        }
        return $outfile;
    }

    /*
     * 错误信息
     */

    public function getErrorMsg() {
        return $this->erroMsg;
    }

    //最终保存文件名（连目录）
    private function uploadSave() {
        $Dir = $this->savepath . $this->save_date_dir . '/';
        setDir($Dir, 0755);
        if (!is_dir($Dir) || !is_writable($Dir)) {
            $this->halt('目录不存在或不可写，请检查权限');
            return null;
        }
        return $Dir . $this->savename;
    }

    private function cleanFileName($filename) {
        $bad = array("<!--", "-->", "'", "<", ">", '"', '&', '$', '=', ';', '?', '/',
            "%20",
            "%22",
            "%3c", // <
            "%253c", // <
            "%3e", // >
            "%0e", // >
            "%28", // (
            "%29", // )
            "%2528", // (
            "%26", // &
            "%24", // $
            "%3f", // ?
            "%3b", // ;
            "%3d"  // =
        );
        return stripslashes(substr(str_replace($bad, '', preg_replace("/\s+/", "_", $filename)), -50));
    }

    //获取上传的图片的信息放入数组中
    private function getInfo($photo) {
        $photo = $photo ? $photo : $this->filename;
        $imageInfo = getimagesize($photo);
        $imgInfo['width'] = $imageInfo[0];
        $imgInfo['height'] = $imageInfo[1];
        $imgInfo['type'] = $imageInfo[2];
        $imgInfo['name'] = basename($photo);
        return $imgInfo;
    }

    private function getUploadFile($key, $filear) {
        $outfile = array();
        if ($filear['size'] > 0 && !$filear['error']) {
            $this->getext($filear['name']); //取得扩展名
            $this->setSavename(); //设置保存文件名
            $hash = md5_file($filear['tmp_name']);
            $saveFile = $this->copyFile($filear);
            if (!$saveFile) {
                return $outfile;
            }
            $saveFileInfo = $this->getInfo($saveFile);
            $outfile = array(
                'filefield' => $key,
                'hash' => $hash,
                'savepath' => $saveFile,
                'realname' => $this->cleanFileName($filear['name']),
                'size' => $filear['size'],
                'type' => $this->ext,
                'width' => isset($saveFileInfo['width']) ? $saveFileInfo['width'] : 0,
                'height' => isset($saveFileInfo['height']) ? $saveFileInfo['height'] : 0,
            );
        }
        return $outfile;
    }

    /*
     * 功能：检测并复制上传文件
     * $filear 上传文件资料数组
     */

    private function copyFile($filear) {

        if ($filear['size'] > $this->maxsize) {
            $this->halt('上传文件最大为' . round($this->maxsize / (1024 * 1024), 2) . ' M，当前为 ' . round($filear['size'] / (1024 * 1024), 2) . 'M');
            return null;
        }
        if (!$this->chkExt()) {
            $this->halt($this->ext . ' 文件格式不允许上传。');
            return null;
        }
        if (!$this->overwrite && file_exists($this->savename)) {
            $this->halt($this->savename . ' 文件名已经存在。');
            return null;
        }
        $uploadSave = $this->uploadSave();
        if (!$uploadSave) {
            return null;
        }
        if (function_exists('move_uploaded_file')) {
            $upok = move_uploaded_file($filear['tmp_name'], $uploadSave);
        } else {
            $upok = copy($filear['tmp_name'], $uploadSave);
        }
        if (!$upok) {
            $errors = array(
                0 => '文件上传成功',
                1 => '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。 ',
                2 => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。 ',
                3 => '文件只有部分被上传。 ',
                4 => '没有文件被上传。 '
            );
            $this->halt($errors[$filear['error']]);
            return null;
        } else {
            $this->deleteFile($filear['tmp_name']); //删除临时文件
            return $uploadSave; //返回上传文件名
        }
    }

    /*
     * 功能: 取得文件扩展名
     * $filename 为文件名称
     */

    private function getExt($filename) {
        $filename = $filename ? $filename : $this->filename;
        if (empty($filename)) {
            return false;
        }
        return $this->ext = getFileExt($filename);
    }

    /*
     * 功能：检测文件类型是否允许
     */

    private function chkExt() {
        if ($this->format == '' || in_array($this->ext, explode('|', strtolower($this->format)))) {
            return true;
        } else {
            return false;
        }
    }

//$savename 保存名，如果为空，则自动生成随机文件名
    private function setSavename($savename = '') {
        if (empty($savename)) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $randStr = '';
            for ($i = 0; $i < ($this->savenamelenght); $i++) {
                $randStr.= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            }
            $name = $randStr . '.' . $this->ext;
        } else {
            $savename = $this->cleanFileName($savename);
            $name = $savename;
        }
        return $this->savename = $name;
    }

    /*
     *
     * 主要用来删除已上传的文件，不返回
     * 参数$file ：文件路径
     */

    private function deleteFile($file) {
        if (file_exists($file)) {
            $delete = chmod($file, 0777);
            $delete = unlink($file);
            if (file_exists($file)) {
                $filesys = eregi_replace("/", "\\", $file);
                $delete = system("del $filesys");
                clearstatcache();
                if (file_exists($file)) {
                    $delete = chmod($file, 0777);
                    $delete = unlink($file);
                    $delete = system("del $filesys");
                }
            }
            clearstatcache();
        }
    }

    /*
     * 功能：错误提示
     * $msg 为输出信息
     */

    private function halt($msg = '未知错误') {
        $this->erroMsg = $msg;
        return true;
    }

}
