<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 全局函数
 */
/*
 * 获取配置参数
 * @param $file string 需要获取内容的文件名
 * @param $field string 获取具体内容的键名名称
 * @param $path string 配置文件的路径
 * @param $returnBool bool 没有找到内容时是否返回布尔值
 * @return 键值
 * @example C('system','title');//加载config/config_inc.php 中的title的值
 * @example C('cache');//返回config/cache_inc.php的数组
 * @example C('cache','open');//加载config/cache_inc.php 中的open的值
 */

function C($file = '', $field = null, $path = APP_PATH, $returnBool = true) {
    if (empty($file) || strtolower($file) == 'system') {
        if (!is_null($field)) {
            return (isset($GLOBALS['System'][$field])) ? $GLOBALS['System'][$field] : null;
        } else {
            return $GLOBALS['System'];
        }
    }
    static $_cfile = array();
    if (!isset($_cfile[$file])) {
        $path.='config/' . $file . '_inc.php';
        //$class = strtolower($class);
        if (is_file($path)) {
            $_cfile[$file] = include $path;
        } else {
            return $returnBool ? false : showError($file . '.php 加载失败。');
        }
    }
    if (!is_null($field)) {
        return (isset($_cfile[$file][$field])) ? $_cfile[$file][$field] : null;
    } else {
        return $_cfile[$file];
    }
}

function obGzip($content = '') {
    if (!headers_sent() && extension_loaded('zlib') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
        header("Content-Encoding: gzip");
        header("Vary: Accept-Encoding");
        header("Content-Length: " . strlen($content));
        $content = gzencode($content, 9);
    }
    return $content;
}

/*
 * 加载框架函数和类库
 * @param $file string 需要加载的文件名
 * 注意，该函数可以加载xlphp\lib\driver和xlphp\lib\function下的文件
 * 默认加载driver下的文件，当文件名中包含.fun时则加载function下的文件
 * @return bool 是否成功加载
 * @example load('curl')//加载xlphp\lib\driver 下的curl.class.php
 * @example load('time.fun')//加载xlphp\lib\function下的文件 下的time.fun.php
 */

function load($file = '') {
    if (empty($file)) {
        return '';
    }
    static $_loadfile = array();
    $file = !is_array($file) ? array($file) : $file;
    $dir = '';
    foreach ($file as $f) {
        $fname = $f;
        if (strpos($f, '.fun') !== FALSE) {
            $f = str_replace('.fun', '', $f);
        }
        if (!isset($_loadfile[$f . '_core']) && !isset($_loadfile[$f . '_driver']) && !isset($_loadfile[$f . '_function'])) {
            if (strpos($fname, '.fun') !== FALSE && is_file(XLPHP_PATH . "lib/function/{$fname}.php")) {
                $dir = 'function';
            } elseif (is_file(XLPHP_PATH . "lib/core/{$fname}.class.php")) {
                $dir = 'core';
                $fname.='.class';
            } elseif (is_file(XLPHP_PATH . "lib/driver/{$fname}.class.php")) {
                $dir = 'driver';
                $fname.='.class';
            } elseif (is_file(XLPHP_PATH . "lib/function/{$fname}.fun.php")) {
                $dir = 'function';
                $fname.='.fun';
            } else {
                showError($f . ' 加载失败。');
            }
            include XLPHP_PATH . "lib/{$dir}/{$fname}.php";
            $_loadfile[$f . '_' . $dir] = true;
        }
    }
    return true;
}

/*
 * 系统级消息提示
 * @param $msg string 提示内容
 * @param $url string 跳转链接
 * @param $urlTitle string 显示标题
 * @param $isAutoGo bool 是否自动跳转
 * @param $title string 提示标题
 * @param $exit bool 是否终止程序
 * @param $returnStatus int 返回状态值
 * @return string
 */

function showMsg($msg, $url = '', $urlTitle = '', $isAutoGo = false, $title = '提示信息', $exit = true, $returnStatus = 0) {
    if ($url == 'back') {
        $url = 'javascript:history.back(-1);';
    }
    if (isAjax()) {
        exit(json_encode(array('status' => $returnStatus, 'info' => $msg, 'data' => $url)));
    }
    $fileName = APP_PATH . 'tpl/' . C('System', 'skin') . '/system/message_tpl.php';
    if (!file_exists($fileName)) {
        $fileName = XLPHP_PATH . 'lib/tpl/message_tpl.php';
    }
    return include $fileName;
}

/*
 * 系统级消息提示（错误）
 * @param $msg string 提示内容
 * @param $url string 跳转链接
 * @param $isAutoGo bool 是否自动跳转
 * @return string
 */

function showError($msg, $url = 'back', $isAutoGo = false) {
    if ($msg == 404) {
        jumpTo(404);
    }
    return showMsg($msg, $url, '', $isAutoGo, '错误信息');
}

/*
 * 系统级消息提示（成功）
 * @param $msg string 提示内容
 * @param $url string 跳转链接
 * @param $isAutoGo bool 是否自动跳转
 * @return string
 */

function showOk($msg, $url = 'back', $isAutoGo = false) {
    return showMsg($msg, $url, '', $isAutoGo, '成功信息', true, 1);
}

/*
 * 系统级消息提示（提示）
 * @param $msg string 提示内容
 * @param $url string 跳转链接
 * @param $isexit bool 是否终止程序
 * @return string
 */

function showInfo($msg = '', $url = 'back', $isexit = false) {
    return showMsg($msg, $url, '', false, '提示信息', $isexit, 2);
}

/*
 * 判断变量是否存在
 * @param $str string 需检测的变量名
 * @param $checkValue bool 是否检查有值
 * @return bool
 * @example isHave($_GET['id'])?1:0
 */

function isHave(&$str, $checkValue = true) {
    if (!isset($str)) {
        return false;
    }
    if ($checkValue) {
        $str = is_array($str) ? array_filter($str) : trim($str);
        return !empty($str) ? true : false;
    }
    return true;
}

/*
 * 是否AJAX请求
 * @param 无
 * @return bool
 */

function isAjax() {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return true;
    }
    // 判断Ajax方式提交
    if ((isset($_POST[C('system', 'var_ajax_submit')]) && $_POST[C('system', 'var_ajax_submit')]) || (isset($_GET[C('system', 'var_ajax_submit')]) && $_GET[C('system', 'var_ajax_submit')])) {
        return true;
    }
    return false;
}

/*
 * 保存系统日志
 * @param $fileName string 日志保存路径和名称
 * @param $data string 日志内容
 * @return bool
 * @example saveLog('sys/error',array('info'=>'访问错误'));
 */

function saveLog($fileName = 'error', $data = '') {
    $logPath = APP_PATH . 'runtime' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . trim($fileName, '/');
    $fileName = $logPath . '_' . date('Ymd') . '.php';
    setDir(dirname($fileName));
    $content = '';
    if (!is_file($fileName)) {
        $content = '<?php exit;?>' . PHP_EOL;
    }
    return file_put_contents($fileName, $content . date('Y-m-d H:i:s') . PHP_EOL . var_export($data, true) . PHP_EOL, FILE_APPEND);
}

//拦截程序错误
function showErrorFun($errno, $errstr, $errfile, $errline) {
    $msg = 'desc：' . $errstr . '，code：' . $errno . '，file：' . str_replace(ROOT, '', $errfile) . '，line：' . $errline;
    if (DEBUG) {
        saveLog('log/system', $msg);
        $trace = debug_backtrace();
        $traceInfo = '';
        $time = date('y-m-d H:i:m');
        foreach ($trace as $t) {
            foreach (array('file', 'line', 'class', 'type', 'function') as $v) {
                !isset($t[$v]) && $t[$v] = '';
            }
//            !is_array($t['args']) && $t['args'] = array();
            $traceInfo .= '[' . $time . '] ' . str_replace(ROOT, '', $t['file']) . ' (' . $t['line'] . ') ';
            $traceInfo .= $t['class'] . $t['type'] . $t['function'] . '(';
//            $traceInfo .= var_export($t['args'],true);
            $traceInfo .=')<br/>';
        }
        showError(str_replace('，', '<br/>', $msg) . '<br/>调用信息：<br/>' . $traceInfo);
    }
//    else {
//        include XLPHP_PATH . 'lib/tpl/trace_tpl.php';
//    }
}

/*
 * 载入项目自建函数库
 * @param $file string 需要载入的文件名
 * @param $ext string 文件后缀，默认为.php
 * @return 返回载入对象
 * @example loadAppFile('global');//加载app/funciton下的global.php
 */

function loadAppFile($file = '', $ext = '.php') {
    if (!$file) {
        showError('加载自定义函数失败(文件名不能为空)');
    }
    $file = $file . $ext;
    static $_loadappfile = array();
    if ($file == 'common' || isset($_loadappfile[$file])) {
        return true;
    }
    $path = APP_PATH . 'function/' . $file;
    if (is_file($path)) {
        $_loadappfile[$file] = true;
        return include $path;
    } else {
        showError($path . ' 加载失败。');
    }
}

/*
 * 跳转页面
 * @param $url string 需要跳转的路径
 * @return 无
 * @example jumpTo('http://www.baidu.com');
 */

function jumpTo($url = 'javascript:history.back(-1);') {
    if ($url == 404) {
        header('HTTP/1.1 404 Not Found');
//        header("status: 404 Not Found");
        $url = '/404.html';
    }
    header('location:' . $url);
    exit;
}

/*
 * 超全局变量处理
 * @param $name string 需要获取或者设置的全局变量名称
 * @param $val string 需要设置的值，当值为NULL的时候则是注销该全局变量
 * @return 返回对象变量值
 * @example G('uid');//返回全局变量中的$uid
 * @example G('uid',12);//设置全局变量中的$uid=12;
 * @example G('uid',null);//删除全局变量$uid
 */

function G($name = '', $val = '') {
    if (empty($name)) {
        return $GLOBALS['System'];
    }
    if ('' !== $val) {
        $GLOBALS['System'][$name] = $val;
        return $val;
    }
    return isset($GLOBALS['System'][$name]) ? $GLOBALS['System'][$name] : null;
}

/*
 * 去除默认索引页
 * @param $str string 路径地址
 * @param $hideIndex bool 是否隐藏入口文件
 * @return string 返回处理后的字符串
 */

function setHideIndex($str = '', $hideIndex = true) {
    if (C('System', 'hide_index') && $hideIndex && strExists($str, SCRIPT_NAME)) {
        $str = str_replace(array(SCRIPT_NAME . '/', SCRIPT_NAME), '', $str);
    }
    return $str;
}

/*
 * 增加默认索引页
 * @param $str string 路径地址
 * @return string 返回处理后的字符串
 */

function getShowIndex($str = '') {
    if (!C('System', 'hide_index')) {
        $str.=SCRIPT_NAME;
    }
    return $str;
}

/*
 * 加载model类
 * @param $classname string 模型名称
 * @return object 返回实例化的模型对象
 * @example D('user');//将加载app\lib\model\userModel.class.php并实例化
 */

function D($classname = '') {
    static $_Dmodel = array();
    static $_DmodelAutoLoad = array();
    //自动加载模型
    $sys = C('system', 'autoload_model');
    if (!$_DmodelAutoLoad && isHave($sys)) {
        foreach ($sys as $val) {
            $path = APP_PATH . 'lib/model/' . $val . 'Model.class.php';
            if (is_file($path)) {
                include $path;
                $_DmodelAutoLoad[$val] = true;
            } else {
                showError('自动加载模型：' . $val . '不存在');
            }
        }
    }
    if (!empty($classname)) {
        if (isset($_Dmodel[$classname])) {
            return $_Dmodel[$classname];
        }
        $path = APP_PATH . 'lib/model/' . $classname . 'Model.class.php';
        if (is_file($path)) {
            load('model');
            if (!isset($_DmodelAutoLoad[$classname])) {
                include $path;
            }
            $class = $classname . 'Model';
            if (checkPath($classname) && class_exists($class)) {
                $_Dmodel[$classname] = new $class();
                return $_Dmodel[$classname];
            }
        }
    }
    return M($classname);
}

/*
 * 获取数据表前缀
 * @param $table string 需要获取完整表名的数据表名
 * @return string 返回带前缀的数据表名
 */

function getTableName($table = '') {
    $dbConf = C('database');
    return '`' . $dbConf[$dbConf['dbtype']]['dbprefix'] . $table . '`';
}

/*
 * 无model模型，直接以表名初始化
 * @param $dbname string 数据库名，无需带前缀
 * @param $config string 数据库配置文件，默认为系统数据库
 * @return object 实例化的对象，支持数据库链式操作
 * @example M('user');//将返回一个数据模型对象，操作的表为user
 */

function M($dbname = '', $config = 'database') {
    load('model');
    $_Mmodel = model::getInstance($config);
    if (!empty($dbname)) {
        $_Mmodel->dbTable = $dbname;
    }
    return $_Mmodel;
}

/*
 * 加载系统级第三方类库，为框架vendor目录的类库
 * @param $file string 类库路径，相对于vendor目录，无需文件后缀
 * @param $addc string 是否在路径后面主动添加“.class”
 * @return bool 是否成功载入类库
 * @example V('weixin/reply');//将加载 \xlphp\vendor\weixin\reply.class.php
 */

function V($file = '', $addc = true) {
    if (!$file) {
        return '';
    }
    static $_vfile = array();
    if (isset($_vfile[$file])) {
        return true;
    }
    $path = $file;
    if ($addc) {
        $path .= '.class';
    }
    $path = XLPHP_PATH . 'vendor/' . $path . '.php';
    //$class = strtolower($class);
    if (is_file($path)) {
        $_vfile[$file] = true;
        return include $path;
    } else {
        showError($file . '.php 加载失败。');
    }
}

/*
 * 加载项目级类库，文件只加载一次，后续直接调用即可
 * @param $file string 类库路径，相对于项目下的service目录，无需文件后缀
 * @param $addc string 是否在路径后面主动添加“.class”
 * @return bool 是否成功载入类库
 * @example T('user');//将加载 app\service\user.class.php
 */

function T($file = '', $addc = true) {
    if (!$file) {
        return '';
    }
    static $_tfile = array();
    if (isset($_tfile[$file])) {
        return true;
    }
    $path = $file;
    if ($addc) {
        $path.='.class';
    }
    $path = APP_PATH . 'service/' . $path . '.php';
    //$class = strtolower($class);
    if (is_file($path)) {
        $_tfile[$file] = true;
        return include $path;
    } else {
        showError('服务类库' . $file . '.php 加载失败。');
    }
}

/*
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 * @param $name string 需要保存的文件名
 * @param $value string or array 保存的内容，当内容为NULL时，则删除文件
 * @param $path string 自定义保存路径
 * @return 返回文件内容
 * @example F('user');//返回runtime/data/user.php 的文件内容
 * @example F('user',12);//设置runtime/data/user.php 的文件内容;
 * @example F('user',null);//删除runtime/data/user.php
 */

function F($name, $value = '', $path = '') {
    V('db/redis');
    redisApi::init();
    if ($path) {
        $name = $path . '_' . $name;
    }
    $saveName = strExists($name, '/') ? strtr($name, '/', '_') : $name;
    if ('' !== $value) {
        if (is_null($value)) {
            return redisApi::hdelete('Fdata',$saveName);
        } else {
            // 缓存数据
            return redisApi::hset('Fdata',$saveName, $value);
        }
    }
    // 获取缓存数据
    return redisApi::hget('Fdata',$saveName);
}

/*
 * 全局缓存设置和读取
 * @param $name string 需要保存的文件名
 * @param $value string or array 保存的内容，当内容为NULL时，则删除文件
 * @param $expire int 缓存有效期
 * @return 返回文件内容
 * @example S('user');//返回runtime/cache/user.php 的文件内容
 * @example S('user',12，3600);//设置runtime/cache/user.php 的文件内容;
 * @example S('user',null);//删除runtime/cache/user.php
 */

function S($name, $value = '', $expire = 0) {
    V('db/redis');
    redisApi::init();
    $saveName = 'Sdata_' . (strExists($name, '/') ? strtr($name, '/', '_') : $name);
    if ('' !== $value) {
        if (is_null($value)) {
            return redisApi::delete($saveName);
        } else {
            return redisApi::set($saveName, $value, $expire);
        }
    }
    return redisApi::get($saveName);
}

/*
 * 设置全局链接，自动匹配模式，支持自定义URL规则
 * @param $strArr string or array 路由URL
 * @param $arrList array URL参数组
 * @param $hidePostFix bool 是否隐藏URL后缀，默认false
 * @param $hideIndex bool 是否隐藏索引文件，默认true
 * @param $native bool 是否保持原生URL，默认false
 * @return string 生成的URL链接
 * @example U('user/index',array('uid'=>12));
 */

function U($strArr = '', $arrList = array(), $native = false, $hidePostFix = false) {
    $querystr = '';
    $sys = C('System');
    if (!empty($strArr) && is_string($strArr) && strExists($strArr, '/')) {
        $strArr = explode('/', $strArr);
        if (count($strArr) == 2) {
            array_unshift($strArr, GROUP_NAME);
        }
        $strArr = array_merge_recursive(array('g' => $strArr[0], 'c' => $strArr[1], 'm' => $strArr[2]), $arrList);
    }
    if (!empty($strArr) && is_array($strArr)) {
//====保证控制器和模型的顺序=====
        $tmp = array();
        foreach (array('g', 'c', 'm') as $val) {
            if (!isset($strArr[$val])) {
                $tmp[$val] = C('System', $val);
            } else {
                $tmp[$val] = $strArr[$val];
                unset($strArr[$val]);
            }
        }
        $strArr = array_merge($tmp, $strArr);
        if (empty($strArr['g']) || $strArr['g'] == C('System', 'default_group')) {
            unset($strArr['g']);
        }
        unset($tmp);
//====保证控制器和模型的顺序=====
        if (($sys['path_mod'] == 1 || $sys['path_mod'] == 3) && !$native) {//开启PATH_INFO
            if (!C('System', 'hide_index') && !C('System', 'hide_urlkey')) {
                $querystr = '/';
            }
            if (is_array($strArr)) {
                foreach ($strArr as $key => $value) {
                    if (C('System', 'hide_urlkey') || in_array($key, array('g', 'c', 'm'))) {
                        $key = '';
                    } else {
                        $key.=C('System', 'delimiter');
                    }
                    $querystr.=$key . urlencode($value) . C('System', 'delimiter');
                }
                $querystr = rtrim($querystr, C('System', 'delimiter'));
            } else {
                $querystr.=$strArr;
            }
            if (!$hidePostFix) {
                $querystr.=C('System', 'postfix');
            }
            if (C('System', 'url_rule')) {//自定义路由规则
                foreach (C('System', 'url_rule') as $key => $val) {
                    if (strExists($querystr, $key)) {
                        $querystr = str_replace($key, $val, $querystr);
                        break;
                    }
                }
            }
        } elseif ($strArr) {//原生URL
            $querystr = '?' . http_build_query($strArr, '', '&');
        }
    }
    return setHideIndex(MAIN_URL . $querystr);
}

/*
 * 生成一个随机的字符串
 * @param $length int 生成内容的长度
 * @param $specialChars bool 是否添加特殊字符
 * @return string 生成的随机的字符串
 */

function getRandStr($length = 16, $specialChars = false) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    if ($specialChars) {
        $chars .= '!@#$%^&()';
    }
    $randStr = '';
    for ($i = 0; $i < $length; $i++) {
        $randStr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $randStr;
}

/*
 * 生成一个随机数字
 * @param $length int 生成内容的长度
 * @param $numeric bool 生成数字串还是字符串 默认为纯数字
 * @return string 生成的随机的字符串
 */

function getRandInt($length = 12, $numeric = 1) {//产生随机字符
    PHP_VERSION < '4.2.0' ? mt_srand((double) microtime() * 1000000) : mt_srand();
    $seed = base_convert(md5(print_r($_SERVER, 1) . microtime()), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}

/*
 * 删除文件夹下所有文件
 * @param $dir string 需要删除的文件夹名
 * @return bool 操作结果
 */

function deleteDir($dir = '') {
    if (empty($dir)) {
        return false;
    }
    if (substr($dir, -1) == '/') {
        $dir = rtrim($dir, '/');
    }
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir) || is_link($dir)) {
        return @unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDir($dir . '/' . $item)) {
            @chmod($dir . '/' . $item, 0777);
            if (!deleteDir($dir . '/' . $item)) {
                return false;
            }
        }
    }
    return @rmdir($dir);
}

/*
 * 建立多级文件夹
 * @param $dir string 创建的文件夹路径
 * @param $mode int 目录权限
 * @return bool 操作结果
 */

function setDir($dir, $mode = 0777) {
    if (!$dir) {
        return false;
    }
    if (strpos($dir, '\\') !== FALSE) {
        $dir = str_replace('\\', '/', $dir);
    }
    if (is_dir($dir)) {
        return true;
    }
    $mdir = '';
    $dirlist = explode('/', $dir);
    foreach ($dirlist as $val) {
        $mdir .= $val . '/';
        if ($val == '..' || $val == '.' || trim($val) == '') {
            continue;
        }
        if (!is_dir($mdir)) {
            if (!@mkdir($mdir, $mode, true)) {
                return false;
            }
            //chmod($mdir, $mode);
            if (!is_file("$mdir/index.htm")) {
                file_put_contents("$mdir/index.htm", '');
            }
        }
    }
    return true;
}

/*
 * 将格式化的时间转换成时间戳
 * @param $time string 需转换的格式化时间，默认为当前时间
 * @return int 时间戳
 */

function inTime($time = '') {
    return $time ? strtotime($time) : TIME;
}

/*
 * 将时间戳转换为格式化的时间
 * @param $str int 需转换的时间戳
 * @param $style int 格式化样式
 * 1:Y-m-d H:i:s,2:Y-m-d,3:Y/m/d H:i:s,4:Y/m/d
 * @return string 格式化后的时间
 */

function outTime($str = '', $style = 1) {
    if (empty($str)) {
        return '';
    }
    switch ($style) {
        case 2:$time = 'Y-m-d';
            break;
        case 3:$time = 'Y/m/d H:i:s';
            break;
        case 4:$time = 'Y/m/d';
            break;
        case 1:
        default:$time = 'Y-m-d H:i:s';
    }
    return date($time, $str);
}

/*
 * 获取站点链接
 * @param $type string 获取的类型
 * all:完整路径,main:主路径
 * @return string 路径
 * @example getSiteUrl('all');//http://127.0.0.1/index.php/index/index.html?id=12
 * @example getSiteUrl('main');//http://127.0.0.1/index.php
 */

function getSiteUrl($type = 'all') {
    $current_page_url = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $current_page_url .= 's';
    }
    $current_page_url .= '://' . $_SERVER['SERVER_NAME'];
    if ($_SERVER['SERVER_PORT'] != '80') {
        $current_page_url .= ':' . $_SERVER['SERVER_PORT'];
    }
    if ($type == 'all') {
        $current_page_url .= $_SERVER['REQUEST_URI'];
    } elseif ($type == 'main') {
        $current_page_url .=$_SERVER['SCRIPT_NAME'];
    }
    return $current_page_url;
}

/*
 * 获取文件扩展名,不包含"."
 * @param $file string 文件名
 * @return string 文件后缀
 */

function getFileExt($file) {
    $fileExt = addslashes(strtolower(substr(strrchr($file, '.'), 1, 10)));
    if ($fileExt == 'jpeg') {
        $fileExt = 'jpg';
    }
    return $fileExt;
}

/*
 * 判断字符串是否存在
 * @param $haystack string 检查的内容
 * @param $needle string 关键字
 * @return bool 关键字是否存在于检查的内容中
 */

function strExists($haystack, $needle) {
    return !(stripos($haystack, $needle) === FALSE);
}

/*
 * 产生form防伪码
 * @param 无
 * @return string 生成的HASH串
 */

function formHash() {
    static $tokenValue = '';
    if (!$tokenValue) {
        $tokenValue = md5(VCODE . microtime(TRUE));
        mySession('__hash__', $tokenValue);
    }
    return $tokenValue;
}

/*
 * 判断提交是否正确
 * @param 无
 * @return bool 表单提交是否合法
 */

function formCheck() {
    if (isHave($_POST['__hash__']) && mySession('__hash__') && $_SERVER['REQUEST_METHOD'] == 'POST') {
        if ((empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])) && $_POST['__hash__'] == mySession('__hash__')) {
            return true;
        }
    }
    return false;
}

/*
 * 清除表单提交
 * @param 无
 * @return bool 清除表单验证HASH
 */

function formClear() {
    mySession('__hash__', null);
    if (isset($_POST['__hash__'])) {
        unset($_POST['__hash__']);
    }
    return true;
}

/*
 * 检查路径
 * @param $str string 检查是否是合法的文件路径
 * @return bool
 */

function checkPath($str) {
    return preg_match('/^-?[0-9a-zA-Z_]+$/', $str) ? true : false;
}

/*
 * 检查是否纯数字
 * @param $str int 检查是否是纯数字串
 * @return bool
 */

function checkNum($str = '') {
    return preg_match('/^[0-9]{1,}$/', $str) ? true : false;
//    return ctype_digit($str);
}

/*
 * 转换文件大小单位
 * @param $size int 文件大小值
 * @param $dec int 保留小数位数，默认为2
 * @return string 带单位的文件大小串
 */

function changeFileSize($size, $dec = 2) {
    $a = array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB');
    $pos = 0;
    while ($size >= 1024) {
        $size /= 1024;
        $pos++;
    }
    return round($size, $dec) . ' ' . $a[$pos];
}

/*
 * 生成加密字符串
 * @param $data array or string 需要加密的内容
 * @param $secretkey string 加密密钥
 * @param $file string 加密类库
 * @return string 加密后的字符串
 */

function setEnocde($data = array(), $secretkey = '', $file = 'Xxtea') {
    if (empty($data)) {
        return '';
    }
    $secretkey = $secretkey ? $secretkey : VCODE;
    V('Crypt/' . $file);
    return MyEncrypt::encrypt($data, $secretkey);
}

/*
 * 解析加密字符串
 * @param $data array or string 需要解密的内容
 * @param $secretkey string 解密密钥
 * @param $file string 解密类库
 * @return 解密后的内容
 */

function getDecode($data = '', $secretkey = '', $file = 'Xxtea') {
    if (empty($data)) {
        return '';
    }
    $secretkey = $secretkey ? $secretkey : VCODE;
    V('Crypt/' . $file);
    return MyEncrypt::decrypt($data, $secretkey);
}

/*
 * cookie操作
 * @param $var string cookie名称
 * @param $value string 保存的内容，当内容为NULL时，则删除文件
 * @param $expire int 有效期
 * @return 返回文件内容
 */

function myCookie($var, $value = '', $life = 0) {
    global $System;
    if ('' === $value) {
        if (isHave($_COOKIE[$System['cookie']['pre'] . $var])) {
            return $_COOKIE[$System['cookie']['pre'] . $var];
        } else {
            return NULL;
        }
    } else {
        if (is_null($value)) {
            setcookie($System['cookie']['pre'] . $var, '', TIME - 3600, $System['cookie']['path'], $System['cookie']['domain'], $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
            unset($_COOKIE[$var]); // 删除指定cookie
        } else {
            // 设置cookie
            setcookie($System['cookie']['pre'] . $var, $value, $life ? (TIME + $life) : 0, $System['cookie']['path'], $System['cookie']['domain'], $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
            $_COOKIE[$var] = $value;
        }
    }
}

/*
 * session操作
 * @param $var string session名称
 * @param $value string 保存的内容，当内容为NULL时，则删除文件
 * @return 返回文件内容
 */

function mySession($name, $value = '') {
    $sessionConf = C('System', 'session');
    $prefix = $sessionConf['pre'];
    if (is_array($name)) { // session初始化 在session_start 之前调用
        if ($sessionConf['var_session_id'] && isset($_REQUEST[$sessionConf['var_session_id']])) {
            session_id($_REQUEST[$sessionConf['var_session_id']]);
        } elseif (isset($name['id'])) {
            session_id($name['id']);
        }
        ini_set('session.auto_start', 0);
        if (isset($name['name']))
            session_name($name['name']);
        if (isset($name['path']))
            session_save_path($name['path']);
        if (isset($name['domain']))
            ini_set('session.cookie_domain', $name['domain']);
        if (isset($name['expire']))
            ini_set('session.gc_maxlifetime', $name['expire']);
        if (isset($name['use_trans_sid']))
            ini_set('session.use_trans_sid', $name['use_trans_sid'] ? 1 : 0);
        if (isset($name['use_cookies']))
            ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        if (isset($name['cache_limiter']))
            session_cache_limiter($name['cache_limiter']);
        if (isset($name['cache_expire']))
            session_cache_expire($name['cache_expire']);
    } elseif ('' === $value) {
        if (0 === strpos($name, '[')) { // session 操作
            if ('[pause]' == $name) { // 暂停session
                session_write_close();
            } elseif ('[start]' == $name) { // 启动session
                session_start();
            } elseif ('[destroy]' == $name) { // 销毁session
                $_SESSION = array();
                session_unset();
                session_destroy();
            } elseif ('[regenerate]' == $name) { // 重新生成id
                session_regenerate_id();
            }
        } elseif (0 === strpos($name, '?')) { // 检查session
            $name = substr($name, 1);
            if ($prefix) {
                return isset($_SESSION[$prefix][$name]);
            } else {
                return isset($_SESSION[$name]);
            }
        } elseif (is_null($name)) { // 清空session
            if ($prefix) {
                unset($_SESSION[$prefix]);
            } else {
                $_SESSION = array();
            }
        } elseif ($prefix) { // 获取session
            return isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;
        } else {
            return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
        }
    } elseif (is_null($value)) { // 删除session
        if ($prefix && isset($_SESSION[$prefix][$name])) {
            unset($_SESSION[$prefix][$name]);
        } elseif (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    } else { // 设置session
        if ($prefix) {
            if (isset($_SESSION[$prefix]) && !is_array($_SESSION[$prefix])) {
                $_SESSION[$prefix] = array();
            }
            $_SESSION[$prefix][$name] = $value;
        } else {
            $_SESSION[$name] = $value;
        }
    }
}

/*
 * 获得客户端真实的IP地址
 * @param 无
 * @return string Ip地址
 */

function getUserIp() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) {
            unset($arr[$pos]);
        }
        $ip = $arr[0] ? trim($arr[0]) : '0.0.0.0';
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    return sprintf("%u", ip2long($ip)) ? $ip : '0.0.0.0';
}

/*
 * 截取一定长度的完整的中文字符
 * @param $sourcestr string 需要截取的字符串
 * @param $cutlength int 需要截取的长度
 * @param $startlength int 开始截取的长度
 * @param $html bool 是否保留html标签
 * @param $ellipsis bool 内容超出长度时是否显示省略号
 * @return string 截取后的内容
 */

function cnsubStr($sourcestr, $cutlength = 100, $startlength = 0, $html = false, $ellipsis = false) {
    $returnstr = '';
    $i = 0;
    $n = 0;
    if (!$html) {
        $sourcestr = strip_tags($sourcestr, '<br/>,<br>');
    }
    $str_length = strlen($sourcestr);
    while (($n < $cutlength) and ( $i <= $str_length)) {
        $temp_str = substr($sourcestr, $i, 1);
        $ascnum = Ord($temp_str);
        if ($ascnum >= 224) {
            $returnstr .=substr($sourcestr, $i, 3);
            $i = $i + 3;
            $n++;
        } elseif ($ascnum >= 192) {
            $returnstr .=substr($sourcestr, $i, 2);
            $i = $i + 2;
            $n++;
        } elseif ($ascnum >= 65 && $ascnum <= 90) {
            $returnstr .=substr($sourcestr, $i, 1);
            $i = $i + 1;
            $n++;
        } else {
            $returnstr .=substr($sourcestr, $i, 1);
            $i = $i + 1;
            $n = $n + 0.5;
        }
        if ($n <= $startlength) {
            $returnstr = '';
            continue;
        }
    }
    if ($ellipsis && $str_length > $cutlength) {
        $returnstr .='...';
    }
    return $returnstr;
}

/**
 * 获取字符串长度
 * @param string $str 要获取长度的字符串
 * @return int
 */
function getStrLen($str) {
    $length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));
    if ($length) {
        return strlen($str) - $length + intval($length / 3) * 2;
    } else {
        return strlen($str);
    }
}

/*
 * 获取GET参数字符串
 * @param $arr array key:需要处理的键名 value:处理的键值
 * value为空则删除此key
 * @param $retrunArr bool 是否返回数组还是格式化的链接
 * @return 处理完毕的数组（格式化好的链接）
 */

function getUrlStrList($arr = array(), $retrunArr = false) {
    if ($arr) {
        foreach ($arr as $key => $val) {
            if (!is_null($val)) {
                $_GET[$key] = $val;
            } elseif (isset($_GET[$key])) {
                unset($_GET[$key]);
            }
        }
    }
    return $retrunArr ? $_GET : http_build_query($_GET, '', '&');
}

/* 清理内容格式，删除多余空格等
 * @param string 字符串
 * @return string 清理后的内容
 */

function clearStrSpace($str = '') {
    return trim(preg_replace("/\s+/", ' ', str_replace(array("\r", "\n", "\t", "\r\n", '  ', '&nbsp;', '&amp;nbsp;'), '', $str)));
}

/*
 * 过滤用户输入内容
 * @param $content string 输入的内容
 * @param $delhtmltag bool 是否保留普通的html标签
 * return 处理后的内容
 */

function wordsFilter($content = '', $delhtmltag = false) {
    if (empty($content)) {
        return '';
    }
    if (!$delhtmltag) {//清除html
        return htmlspecialchars(strip_tags($content)); //过滤html
    } else {//移除XSS,保留html
        load('string.fun');
        return removeXss($content);
    }
}

/*
 * 自动转换字符集 支持数组转换
 * @param $fContents string 需要转换的内容
 * @param $from string 输入的编码格式
 * @param $to string 输出的编码格式
 * return 处理后的内容
 */

function autoCharset($fContents, $from = 'gbk', $to = 'utf-8') {
    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = autoCharset($key, $from, $to);
            $fContents[$_key] = autoCharset($val, $from, $to);
            if ($key != $_key) {
                unset($fContents[$key]);
            }
        }
        return $fContents;
    } else {
        return $fContents;
    }
}
