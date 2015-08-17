<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 缓存类 cache
 */

class Mycache {

//缓存目录
    public $cacheRoot = 'cache';
//缓存更新时间秒数，0为不缓存
    public $cacheLimitTime = 3600;
//缓存文件名
    public $cacheFileName = '';
//缓存扩展名
    public $cacheFileExt = 'php';
    static $_instance = NULL;
    public $noCache = false;

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

    /*
     * 构造函数
     * int $cacheLimitTime 缓存更新时间
     */

    function __construct() {
        $this->sys = C('cache');
        if ($this->sys['dir']) {
            $this->cacheRoot = APP_PATH . 'runtime/' . rtrim($this->sys['dir'], '/') . '/';
        }
        $cacheLimitTime = intval($this->sys['time']);
        if ($cacheLimitTime) {
            $this->cacheLimitTime = $cacheLimitTime;
        }
    }

    /*
     * 设置缓存文件名和目录
     * string $fileName 缓存文件名
     * string $cacheRoot 缓存目录
     * int $cacheLimitTime 缓存有效时间
     */

    public function setCache($fileName = '', $cacheContent = '', $cacheLimitTime = '') {
        if ($cacheLimitTime) {
            $this->cacheLimitTime = intval($cacheLimitTime) + TIME;
        }
        $this->getCacheFileName($fileName);
        $this->saveFile($this->getRealFilePath($fileName), '/*' . sprintf('%012d', $this->cacheLimitTime) . '*/' . serialize($cacheContent));
    }

    /*
     * 检查缓存文件是否在设置更新时间之内
     * 返回：如果在更新时间之内则返回文件内容，反之则返回失败
     */

    public function getCache($fileName = '') {
        $content = '';
        $this->getCacheFileName($fileName);
        $cacheFile = $this->getRealFilePath($fileName);
        if (is_file($cacheFile)) {
//            $cTime = $this->getFileCreateTime($cacheFile);
            //保存的内容，按时间戳来判断
            $content = $this->fopen_url($cacheFile);
            $expire = (int) substr($content, 2, 12);
            if ($expire != 0 && TIME < $expire) {
                $content = substr($content, 16);
            } else {
                $this->delCache($fileName);
                return null;
            }
        } else {
            return null;
        }
        if ($content) {
//            if (get_magic_quotes_gpc()) {
//                $content = stripslashes($content);
//            }
            $content = unserialize($content);
            if ($fileName) {
                return $content;
            } else {
                echo $content;
                ob_end_flush();
                exit();
            }
        }
    }

    /*
     * 缓存文件或者输出静态
     * string $fileName 静态文件名（含相对路径）
     */

    public function caching($fileName = '') {
        if ($this->noCache) {//截断缓存，直接输出，用于个别文件不需要缓存
            return true;
        }
//           ob_end_flush();
        return $this->setCache($fileName, ob_get_contents());
    }

    /*
      生成指定名字的html文件
     */

    public function html($fileName) {
        return $this->caching($fileName);
    }

    public function delCache($fileName = '') {
        $filePath = $this->getRealFilePath($fileName);
        if (is_file($filePath)) {
            return @unlink($filePath);
        }
    }

    /*
     * 清除缓存文件
     * string $fileName 指定文件名(含函数)或者all（全部）
     * 返回：清除成功返回true，反之返回false
     */

    public function clearCache($path = '') {
        if (!$path) {
            $path = $this->cacheRoot;
        }
        if (is_dir($path)) {
            $path = rtrim($path, '/') . '/';
            $arr = scandir($path);
            foreach ($arr as $v) {
                if ($v != '.' && $v != '..') {
                    $this->clearCache($path . $v);
                }
            }
            //空文件夹，直接删除
            if ($path != $this->cacheRoot) {
                @rmdir($path);
            }
        } else {//如果不是文件夹，则删除
            @unlink($path);
        }
    }

    /*
     * 获取缓存文件的真实路径
     * string $fileName 静态文件名（含相对路径）
     */

    private function getRealFilePath($fileName) {
        static $_realPath = null;
        if (!isset($_realPath[$fileName])) {
            $selfDir = '';
            if ($fileName && dirname($fileName) !== '.') {
                $selfDir = dirname(trim($fileName, '/')) . '/';
            }
            $dir = '';
            if (isset($this->sys['dir_level'])) {
                // 使用子目录
                $name = $this->getCacheFileName($fileName);
                $this->sys['dir_level'] = intval($this->sys['dir_level']);
                for ($i = 0; $i < $this->sys['dir_level']; $i++) {
                    $dir.=$name{$i} . '/';
                }
                unset($name);
            } else {
                $this->getCacheFileName($fileName);
            }
            $_realPath[$fileName] = $this->cacheRoot . $selfDir . $dir . $this->cacheFileName . '.' . $this->cacheFileExt;
        }
        return $_realPath[$fileName];
    }

    private function settime($time) {
        $this->cacheLimitTime = $time;
    }

    /*
     * 根据当前动态文件生成缓存文件名
     */

    private function getCacheFileName($fileName = '') {
        $this->cacheFileName = $fileName ? strtoupper(md5($fileName)) : strtoupper(md5($this->request_uri()));
        return $this->cacheFileName;
    }

    /*
     * 缓存文件建立时间
     * string $fileName   缓存文件名（含相对路径）
     * 返回：文件生成时间秒数，文件不存在返回0
     */

    private function getFileCreateTime($fileName) {
        if (!trim($fileName)) {
            return 0;
        }
        if (is_file($fileName)) {
            return intval(filemtime($fileName));
        } else {
            return 0;
        }
    }

    /*
     * 保存文件
     * string $fileName  文件名（含相对路径）
     * string $text      文件内容
     * 返回：成功返回ture，失败返回false
     */

    private function saveFile($fileName, $text) {
        if (!$fileName || !$text) {
            return false;
        }
        if (setDir(dirname($fileName))) {
            file_put_contents($fileName, $text);
        }
        return false;
    }

// 说明：获取 _SERVER['REQUEST_URI'] 值的通用解决方案
    private function request_uri() {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            if (isset($_SERVER['argv'])) {
                $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0];
            } elseif ($_SERVER['QUERY_STRING']) {
                $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
            } else {
                $uri = $_SERVER['PHP_SELF'];
            }
        }
        return $uri;
    }

    /*
     * 打开文件
     * string $url 文件地址
     * 返回：文件内容，不存在或者读取错误则返回为空
     */

    private function fopen_url($url) {
        $file_content = '';
        if (function_exists('file_get_contents')) {
            $file_content = file_get_contents($url);
        } elseif (function_exists('curl_init')) {
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $url);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_handle, CURLOPT_FAILONERROR, 1);
            curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Trackback Spam Check'); //引用垃圾邮件检查
            $file_content = curl_exec($curl_handle);
            curl_close($curl_handle);
        } else {
            $file_content = '';
        }
        return $file_content;
    }

}
