<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/*
 * 安全输出内容，可以选择是否转义HTML标记
 * @param $str string 输出内容串
 * @param $safe bool 是否要进行HTML转义
 * @return string 处理后的内容
 */

function _e($str, $safe = true) {
    echo $str && $safe ? htmlspecialchars($str, ENT_QUOTES) : $str;
}

/*
 * 加载CSS
 * @param $mycss string or array css文件名列表
 * @param $autoEcho bool 是否直接输出还是返回处理好后的字符串
 * @param $noCache bool 是否不缓存
 * @return string 处理后的内容
 * @example getCss('global,style');
 * @example getCss(array('global','style'));
 * 以上都是加载/statics/[default]/css/global.css,/statics/[default]/css/style.css
 * 以“./”开头的资源会直接载入本地文件而忽视CDN设置
 * @example getCss(array('./global','style'));
 */

function getCss($mycss, $autoEcho = true, $noCache = false) {
    if (!$mycss) {
        return '';
    }
    $str = '';
    if (!is_array($mycss) && strExists($mycss, ',')) {
        $mycss = explode(',', $mycss);
    }
    if (is_array($mycss)) {
        foreach ($mycss as $value) {
            $str.=getCss($value, $autoEcho, $noCache);
        }
    } else {
        if (strExists($mycss, '|')) {
            $ar = explode('|', $mycss);
            $mycss = $ar[0];
            $time = isset($ar[1]) ? '?v=' . $ar[1] : '';
        } else {
            $time = $noCache ? '?v=' . date('Ymd', TIME) : '';
        }
        $str = '<link rel="stylesheet" type="text/css" href="' . (strpos($mycss, './') === 0 ? LOCAL_PUBLIC_PATH . 'css/' : CSS_PATH) . $mycss . '.css' . $time . '" />' . "\n";
    }
    if ($autoEcho) {
        echo $str;
    } else {
        return $str;
    }
}

/*
 * 加载JS
 * @param $myjs string or array js文件名列表
 * @param $autoEcho bool 是否直接输出还是返回处理好后的字符串
 * @param $noCache bool 是否缓存
 * @return string 处理后的内容
 * @example getJs('global,main');
 * @example getJs(array('global','main'));
 * 以上都是加载/statics/[default]/js/global.js,/statics/[default]/js/main.js
 * 以“./”开头的资源会直接载入本地文件而忽视CDN设置
 * @example getJs(array('./global','main'));
 */

function getJs($myjs, $autoEcho = true, $noCache = false) {
    if (!$myjs) {
        return '';
    }
    $str = '';
    if (!is_array($myjs) && strExists($myjs, ',')) {
        $myjs = explode(',', $myjs);
    }
    if (is_array($myjs)) {
        foreach ($myjs as $value) {
            $str.=getJs($value, $autoEcho, $noCache);
        }
    } else {
        if (strExists($myjs, '|')) {
            $ar = explode('|', $myjs);
            $myjs = $ar[0];
            $time = isset($ar[1]) ? '?v=' . $ar[1] : '';
        } else {
            $time = $noCache ? '?v=' . date('Ymd', TIME) : '';
        }
        $str = '<script src="' . (strpos($myjs, './') === 0 ? LOCAL_PUBLIC_PATH . 'js/' : JS_PATH) . $myjs . '.js' . $time . '"></script>' . "\n";
    }
    if ($autoEcho) {
        echo $str;
    } else {
        return $str;
    }
}

/*
 * 加载模板文件
 * @param $tpl string 模板名称
 * @param $dir string 模板所在目录
 * @example include getTpl('index');
 * @return string 处理后的内容
 */

function getTpl($tpl = '', $dir = '') {
    if (empty($tpl)) {
        return '';
    }
    if (empty($dir)) {
        $dir = C('System', 'c');
    }
    static $_getTpl = array();
    if (defined('GROUP_TPL_DIR') && GROUP_TPL_DIR) {
        $dir = GROUP_TPL_DIR . '/' . $dir;
    } else {
        $dir = GROUP_NAME . '/' . $dir;
    }
    $savePath = $dir . '_' . $tpl;
    if (strExists($savePath, '/')) {
        $savePath = str_replace('/', '_', $savePath);
    }
    $path = TPL_PATH . $dir . '/' . $tpl . '_tpl.php';
    if (isset($_getTpl[$savePath])) {
        return $path;
    }
    if (is_file($path)) {
        $_getTpl[$savePath] = true;
        return $path;
    } else {
        showError('模板文件 ' . $tpl . ' 不存在');
    }
}

/*
 * 分页函数修正版
 * 支持：原生、伪静态、jquery分页
 * @param $total int :总数
 * @param $page  int:传递过来的当前页的值,第八页$page = 8;
 * @param $showId string 显示类型，null：正常，ajax：ajax
 * @param $pageSize int :每页显示的数据的数目
 * @param $mypage string:分页标示
 * @param $url   string:传递的地址,默认为当前页面
 * @param $maxLength int:分页代码时候,中间的分页数的一半
 * @return string 格式化好的分页代码
 */

function page($total, $page, $showId = '', $pageSize = 20, $mypage = 'p', $url = '', $maxLength = 5) {
    $page = intval($page);
    $page = $page < 1 ? 1 : $page;
    $start = ($page - 1) * $pageSize;
    $totalPage = ceil($total / $pageSize);
    $totalPage = ($totalPage < 1) ? 1 : $totalPage;
    $page = $page > $totalPage ? $totalPage : $page;
    $showType = 'href';
    if (!empty($showId)) {
        $showType = 'href="javascript:;" rel';
        $showId.='_pagebox';
    }
    $urlHome = '';
    //如果$url使用默认，即空值，则赋值为本页URL：
    if (!$url) {
//        $url = $_SERVER['REQUEST_URI'];
        $urlHome = U(getUrlStrList(array($mypage => null), true));
    }
    //===========解析参数开始，主要为去掉分页标示======
    if (C('System', 'path_mod') == '1' && C('System', 'postfix')) {//开启路由模式
        $urlHome = str_replace(C('System', 'postfix'), '', $urlHome);
    } else {
        if (!strExists($urlHome, '?')) {
            $urlHome.='?';
        } else {
            $urlHome.='&';
        }
        if (strExists($urlHome, '?&')) {
            $urlHome = str_replace('?&', '?', $urlHome);
        }
    }
    //===========解析参数结束，主要为去掉分页标示======
    $pageTable = '';
    //aways in the pages
    $pageTable = '<div id="' . $showId . '" class="pagebox">';
    $pageTable .= '<span class="total">共 ' . $total . ' 条 <font class="red">' . $page . '</font>/' . $totalPage . '页</span>';
    //显示第一页
    if ($page == 1) {
        $pageTable .= '<span class="nolink">上页</span><span class="nonce">1</span>';
    } else {
        $pageTable .= '<a ' . $showType . '="' . _mkpageurl($urlHome, $mypage, $page - 1) . '" target="_self">上页</a><a ' . $showType . '="' . _mkpageurl($urlHome, $mypage, 1) . '" target="_self">1</a>';
    }
    //循环中间页码
    if ($totalPage < $maxLength * 2) {
        $loopStart = 2;
        $loopEnd = $totalPage - 1;
    } else {
        $loopStart = $page - $maxLength;
        $loopStart = ($loopStart < 2) ? 2 : $loopStart;
        $loopEnd = $page + $maxLength;
        $loopEnd = ($loopEnd < $maxLength * 2) ? $maxLength * 2 : $loopEnd;
        $loopEnd = ($loopEnd > $totalPage) ? $totalPage - 1 : $loopEnd;
    }
    //... link
    $linkStart = (($loopStart - $maxLength) < 2) ? 2 : $loopStart - $maxLength;
    $linkEnd = (($loopEnd + $maxLength) > $totalPage) ? $totalPage : $loopEnd + $maxLength;
    if ($loopStart > 2) {
        $pageTable .= '<a ' . $showType . '="' . _mkpageurl($urlHome, $mypage, $linkStart) . '" target="_self">...</a>';
    }
    //中间链接
    for ($i = $loopStart; $i <= $loopEnd; $i++) {
        if ($page == $i) {
            $pageTable .= '<span class="nonce">' . $i . '</span>';
        } else {
            $pageTable .= '<a ' . $showType . '="' . _mkpageurl($urlHome, $mypage, $i) . '" target="_self">' . $i . '</a>';
        }
    }
    if ($loopEnd < $totalPage - 1) {
        $pageTable .= '<a ' . $showType . '="' . _mkpageurl($urlHome, $mypage, $linkEnd) . '" target="_self">...</a>';
    }
    //末页链接
    if ($totalPage != 1) {
        if ($page == $totalPage) {
            $pageTable .= '<span class="nonce">' . $totalPage . '</span><span class="nolink">下页</span>';
        } else {
            $pageTable .= '<a ' . $showType . '="' . _mkpageurl($urlHome, $mypage, $totalPage) . '" target="_self">' . $totalPage . '</a><a ' . $showType . '="' . _mkpageurl($urlHome, $mypage, ($page + 1)) . '" target="_self">下页</a>';
        }
    } else {
        $pageTable .= '<span class="nolink">下页</span>';
    }
    $pageTable .='</div>';
    //输出分页代码
    return $pageTable;
}

/**
 * 生成分页链接
 *
 * @param string $urlHome
 * @param string $mypage
 * @param int $page
 * @return string
 */
function _mkpageurl($urlHome, $mypage, $page = 1) {
    if (C('System', 'path_mod') == '1') {//开启路由模式
        return $urlHome . C('System', 'delimiter') . $mypage . C('System', 'delimiter') . $page . C('System', 'postfix');
    } else {
        return $urlHome . $mypage . '=' . $page;
    }
}
