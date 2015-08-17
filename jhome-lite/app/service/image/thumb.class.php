<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of thumb
 *
 * @author xlp
 */
class thumb {

    protected static $image_w;          //图像的宽
    protected static $image_h;           //图像的高
    protected static $image_ext;        //图像的后缀

    /*
     * 生成缩略图
     * @param $image string 原图路径
     * @param $width int 缩略图宽
     * @param $height int 缩略图高
     * @param $isCheck bool 是否仅仅是检查文件存在
     */

    public static function init($image, $width = 120, $height = 120, $isCheck = false) {
        if (!is_file($image)) {
            return false;
        }
        //处理缩略图文件名
        $imageExt = getFileExt($image);
        $toImage = str_replace('.' . $imageExt, '_' . $width . '.' . $imageExt, $image);
        if (is_file(ROOT . $toImage)) {
            return $toImage;
        } elseif ($isCheck) {
            return false;
        }
        //获取图片信息
        self::imageInfo($image);
        //验证是否获取到信息
        if (empty(self::$image_w) || empty(self::$image_h) || empty(self::$image_ext)) {
            return false;
        }
        //如果图片比设置的小就直接返回
        if (self::$image_w <= $width && self::$image_h <= $height) {
            return $image;
        }
        //以原图做画布
        $a = 'imagecreatefrom' . self::$image_ext;
        $original = $a($image);
        if (self::$image_w > self::$image_h) {//宽 > 高
            $crop_x = (self::$image_w - self::$image_h) / 2;
            $crop_y = 0;
            $crop_w = $crop_h = self::$image_h;
        } else {
            $crop_x = 0;
            $crop_y = (self::$image_h - self::$image_w) / 2;
            $crop_w = $crop_h = self::$image_w;
        }
        if (!$height) {
            $height = floor(self::$image_h / (self::$image_w / $width));
            $crop_x = 0;
            $crop_y = 0;
            $crop_w = self::$image_w;
            $crop_h = self::$image_h;
        }
        $litter = imagecreatetruecolor($width, $height);
        if (!imagecopyresampled($litter, $original, 0, 0, $crop_x, $crop_y, $width, $height, $crop_w, $crop_h)) {
            return false;
        }
        //保存图片
        $keep = 'image' . self::$image_ext;
        $keep($litter, ROOT . $toImage);
        //关闭图片
        imagedestroy($original);
        imagedestroy($litter);
        return $toImage;
    }

    //获取图片信息方法
    protected static function imageInfo($image) {
        $info = getimagesize($image);
        if ($info) {
            //图像的宽
            self::$image_w = $info[0];
            //图像的高
            self::$image_h = $info[1];
            //图像的后缀
            self::$image_ext = ltrim(image_type_to_extension($info[2]), '.');
        }
    }

}
