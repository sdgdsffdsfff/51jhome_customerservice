<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of xlphp
 *
 * @author xlp
 */
class xlphp {

    /**
     * run函数
     * 功能:将URL中的Pathinfo解析为$_GET全局变量
     */
    static function run() {
        $sys = C('System');
        if ($sys['path_mod'] == 3 && isHave($_GET['s']) && !isHave($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = $_GET['s'];
        }
        if (isHave($_SERVER['PATH_INFO'])) {
            $pathinfo = explode($sys['delimiter'], $_SERVER['PATH_INFO']);
            $count = count($pathinfo);
            if ($count) {
                if (strExists($pathinfo[0], $sys['delimiter'])) {
                    $pathinfo[0] = trim($pathinfo[0], $sys['delimiter']);
                    array_unshift($pathinfo, '');
                }
                $count = $count - 1;
                if (empty($pathinfo[$count - 1])) {
                    unset($pathinfo[$count - 1]);
                }
//                print_r($pathinfo);exit;
                if (!empty($sys['postfix']) && strExists($pathinfo[$count], $sys['postfix'])) {
                    $pathinfo[$count] = strtr($pathinfo[$count], array($sys['postfix'] => ''));
                }
                if (isHave($pathinfo[1]) && $pathinfo[1] != $sys['default_group'] && $sys['group_list'] && in_array($pathinfo[1], $sys['group_list'])) {
                    $_GET['g'] = $pathinfo[1];
                    if (isHave($pathinfo[2])) {
                        $_GET['c'] = $pathinfo[2];
                    }
                    if (isHave($pathinfo[3])) {
                        $_GET['m'] = $pathinfo[3];
                    }
                    $goIndex = 4;
                } else {
                    if (isHave($pathinfo[1])) {
                        $_GET['c'] = $pathinfo[1];
                    }
                    if (isHave($pathinfo[2])) {
                        $_GET['m'] = $pathinfo[2];
                    }
                    $goIndex = 3;
                }
                if ($count > $goIndex) {
                    for ($foo = $goIndex; $foo < $count; $foo+=2) {
                        $_GET[$pathinfo[$foo]] = $pathinfo[$foo + 1];
                    }
                }
            }
        }
        $_GET['c'] = isHave($_GET['c']) ? trim($_GET['c']) : $sys['c'];
        $_GET['m'] = isHave($_GET['m']) ? trim($_GET['m']) : $sys['m'];
        $_GET['g'] = isHave($_GET['g']) ? trim($_GET['g']) : $sys['default_group']; //默认分组
//定义当前分组、控制器、模型常量
        define('GROUP_NAME', $_GET['g']);
        define('ACTION_NAME', $_GET['c']);
        define('MODEL_NAME', $_GET['m']);
//规范默认参数
        $GLOBALS['System']['g'] = GROUP_NAME;
        $GLOBALS['System']['c'] = ACTION_NAME;
        $GLOBALS['System']['m'] = MODEL_NAME;
//        print_r($_GET);exit;
//打开控制器和模型
        $c = ACTION_NAME . 'Action';
        $path = APP_PATH . 'lib/action/' . ((GROUP_NAME == 'index' || !GROUP_NAME) ? 'index/' : GROUP_NAME . '/') . $c . '.class.php';
        if (checkPath($c) && is_file($path)) {
            include XLPHP_PATH . 'lib/core/action.class.php';
            if (isHave($sys['autoload_action'])) {
                foreach ($sys['autoload_action'] as $key => $val) {
                    if (!is_string($key) || $key == GROUP_NAME) {
                        if (!is_array($val)) {
                            $val = array($val);
                        }
                        foreach ($val as $v) {
                            if (!$key || $key == 'index') {
                                $key = 'index/';
                            } else {
                                $key.='/';
                            }
                            $autoPath = APP_PATH . 'lib/action/' . $key . $v . 'Action.class.php';
                            if (is_file($autoPath)) {
                                include $autoPath;
                            } else {
                                showError('自动加载控制器：' . $v . '不存在');
                            }
                        }
                    }
                }
            }
            include $path;
//            unset($path, $goIndex, $sys, $count, $pathinfo, $foo, $key, $val);
            $control = '';
            if (class_exists($c, false)) {
                $control = new $c();
            }
            if (checkPath(MODEL_NAME) && method_exists($control, MODEL_NAME)) {
                return $control->$_GET['m']();
            } elseif (method_exists($control, '_empty')) {
                return $control->_empty();
            } elseif (method_exists($control, '_error')) {
                return $control->_error();
            } else {
//                jumpTo(404);
                showError('模型 ' . htmlspecialchars(strip_tags(MODEL_NAME)) . ' 不存在');
            }
        }
//        jumpTo(404);
        showError('控制器 ' . htmlspecialchars(strip_tags(ACTION_NAME)) . ' 不存在');
    }

}
