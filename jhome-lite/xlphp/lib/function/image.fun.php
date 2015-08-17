<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * 获得任意大小图像，不足地方拉伸，不产生变形，不留下空白
 * @param $srcFile string 源图像
 * @param $new_width int 新宽度
 * @param $new_height int 新长度
 * @param $thumb_dir string 保存文件夹
 * @param $dst_file string 保存文件名
 * @param $prefix string 前缀
 * @param $suffix strinig 保存格式
 * @param $is_must bool 是否强制生成
 * @param $showErr bool 是否显示错误提示
 * @return string 生成图片的路径
 */
function imageResize($srcFile, $new_width = 3000, $new_height = 3000, $thumb_dir = '', $dst_file = '', $prefix = '', $suffix = '', $is_must = false, $showErr = false) {
    if (!$new_width && !$new_height) {
        if ($showErr) {
            showError('宽度或高度设置不正确!');
        } else {
            return false;
        }
    }
    if (empty($srcFile)) {
        return false;
    }
    if (!$thumb_dir) {
        $sysConfig = C('upload');
        $thumb_dir = $sysConfig['dir'] . '/thumb';
        // 使用子目录
        $name = md5($srcFile);
        for ($i = 0; $i < 3; $i++) {
            $thumb_dir.='/' . $name{$i};
        }
        unset($name);
    }
    /* 默认文件名 */
    if (empty($dst_file)) {
        $srcFilename = basename($srcFile);
        $dst_file = substr($srcFilename, 0, 16) . '_' . $prefix . $new_width . 'X' . $new_height . '.' . getFileExt($srcFilename);
    }
    if (!setDir($thumb_dir) && $showErr) {
        showError('目录创建失败');
    }
    $new_write_name = ROOT . $thumb_dir . '/' . $dst_file;
    $dst_file = trim($thumb_dir, '/') . '/' . $dst_file;
    /* 缩略图存在则直接退出 */
    if (is_file($new_write_name)) {
        return $dst_file;
    }
    $type = exif_imagetype($srcFile);
    if (!$type) {
        if ($showErr) {
            showError('图像加载错误');
        } else {
            return false;
        }
    }
//Load image
    $fun = 'imagejpeg';
    switch ($type) {
        case IMAGETYPE_JPEG :
            $src_img = imagecreatefromjpeg($srcFile);
            $fun = 'imagejpeg';
            break;
        case IMAGETYPE_PNG :
            $src_img = imagecreatefrompng($srcFile);
            $fun = 'imagepng';
            break;
        case IMAGETYPE_GIF :
            $src_img = imagecreatefromgif($srcFile);
            $fun = 'imagegif';
            break;
        default:
            if ($showErr) {
                showError('图像文件载入出错!');
            } else {
                return false;
            }
    }
    if ($suffix) {
        switch ($suffix) {
            case 'jpg':
                $fun = 'imagejpeg';
                break;
            case 'png':
                $fun = 'imagepng';
                break;
            case 'gif':
                $fun = 'imagegif';
                break;
            default :
                $fun = 'imagejpeg';
        }
    }
    $w = imagesx($src_img);
    $h = imagesy($src_img);
    if (!$new_width || $new_width > 3000) {
        $new_width = $w;
    }
    if (!$new_height || $new_height > 3000) {
        $new_height = $h;
    }
    /*
      如果缩略图大小和原图相等则直接退出
     */
    if (!$is_must && $w == $new_width && $h == $new_height) {
        return $srcFile;
    }
    $ratio_w = 1.0 * $new_width / $w;
    $ratio_h = 1.0 * $new_height / $h;
    $ratio = 1.0;
// 生成的图像的高宽比原来的都小，或都大 ，原则是 取大比例放大，取大比例缩小（缩小的比例就比较小了）
    if (($ratio_w < 1 && $ratio_h < 1) || ($ratio_w > 1 && $ratio_h > 1)) {
        if ($ratio_w < $ratio_h) {
            $ratio = $ratio_h; // 情况一，宽度的比例比高度方向的小，按照高度的比例标准来裁剪或放大
        } else {
            $ratio = $ratio_w;
        }
// 定义一个中间的临时图像，该图像的宽高比 正好满足目标要求
        $inter_w = (int) ($new_width / $ratio);
        $inter_h = (int) ($new_height / $ratio);
        $str_x = $w > $new_width ? ($w - $inter_w) / 2 : 0;
        $str_y = 0;
        if ($new_width == 3000 || $new_height == 3000) {
            $new_width > $w && $new_width = $w;
            $new_height > $h && $new_height = $h;
            $inter_w = $w;
            $inter_h = $h;
            if ($new_width == 3000) {
                $inter_w = $new_width = ($new_height / $h) * $w;
            } else {
                $inter_h = $new_height = ($new_width / $w) * $h;
            }
            $str_x = $str_y = 0;
        }
        $inter_img = imagecreatetruecolor($inter_w, $inter_h);
        imagecopy($inter_img, $src_img, 0, 0, $str_x, $str_y, $inter_w, $inter_h);
        // 定义一个新的图像
        $new_img = imagecreatetruecolor($new_width, $new_height);
        // 生成一个以最大边长度为大小的是目标图像$ratio比例的临时图像
        imagecopyresampled($new_img, $inter_img, 0, 0, 0, 0, $new_width, $new_height, $inter_w, $inter_h);
        setThumb($fun, $new_img, $new_write_name, $thumb_dir);
        imagedestroy($new_img);
        imagedestroy($inter_img);
    } else {
        // 2 目标图像 的一个边大于原图，一个边小于原图 ，先放大平普图像，然后裁剪
        if ($new_width == 3000 || $new_height == 3000) {
            $new_width > $w && $new_width = $w;
            $new_height > $h && $new_height = $h;
            $inter_w = $new_width;
            $inter_h = $new_height;
            if ($new_width == 3000) {
                $inter_w = $new_width = ($new_height / $h) * $w;
            } else {
                $inter_h = $new_height = ($new_width / $w) * $h;
            }
            $str_x = $str_y = 0;
        } else {
            $ratio = $ratio_h > $ratio_w ? $ratio_h : $ratio_w; //取比例大的那个值
            // 定义一个中间的大图像，该图像的高或宽和目标图像相等，然后对原图放大
            $inter_w = (int) ($w * $ratio);
            $inter_h = (int) ($h * $ratio);
            //将原图缩放比例后裁剪
            $str_x = $w > $new_width ? ($inter_w - $w) / 2 : ($w == $new_width ? 0 : ($inter_w - $new_width) / 2);
            $str_y = 0;
        }
        $inter_img = imagecreatetruecolor($inter_w, $inter_h);
        imagecopyresampled($inter_img, $src_img, 0, 0, $str_x, $str_y, $inter_w, $inter_h, $w, $h);
        // 定义一个新的图像
        $new_img = imagecreatetruecolor($new_width, $new_height);
        imagecopy($new_img, $inter_img, 0, 0, 0, 0, $new_width, $new_height);
        setThumb($fun, $new_img, $new_write_name, $thumb_dir);
        imagedestroy($new_img);
        imagedestroy($inter_img);
    }
    return $dst_file;
}

function setThumb($fun, $new_img, $new_write_name, $thumb_dir) {
    $definit = ($fun == 'imagepng') ? 8 : 85;
    $fun($new_img, $new_write_name, $definit); // 存储图像
}

// 图像类型
if (!function_exists('exif_imagetype')) {

    function exif_imagetype($filename) {
        if (( list($width, $height, $type, $attr) = @getimagesize($filename) ) !== false) {
            return $type;
        }
        return false;
    }

}