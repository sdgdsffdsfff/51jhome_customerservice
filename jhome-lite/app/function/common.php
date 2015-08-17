<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
 * 自定义函数库
 */

/**
 * 获取图片的完整路径（用于多图片服务器处理路径）
 * @param $src string 图片路径
 * @return string 处理后的图片地址
 */
function getImgUrl($src = '') {
    if ($src) {
        if (strpos($src, 'yun/') === 0) {
            $upConf = C('upload', 'yun');
            return $upConf['url'] . ltrim($src, '/');
        } elseif (strpos($src, 'statics/') === 0) {
            return C('system', 'main_path') . ltrim($src, '/');
        } else {
            return BASE_URL . $src;
        }
    }
    return '';
}

/*
 * 获取用户信息，可直接返回指定字段内容,用于前端格式化内容输出
 * @param $uid int UID
 * @param $field string 需要取的字段名，默认为nickname，用户昵称
 */

function getUser($uid, $field = 'nickname') {
    $user = D('member')->getUserInfoById($uid);
    if ($field) {
        return isset($user[$field]) ? $user[$field] : '';
    } else {
        return $user;
    }
}

/**
 * 获得用户头像
 * @param $uid int 用户UID
 * @param $size int 头像尺寸
 * @param $returnsrc bool 是否只返回头像路径
 * @param $check bool 是否检查文件存在
 * @param $className string 添加样式
 * @return string 生成后的头像地址
 */
function getAvatar($uid, $size = 132, $returnsrc = true, $check = false, $className = '') {
    $avatarfile = avatarFile($uid, 132);
    if ($uid && $check) {
        return is_file(ROOT . $avatarfile);
    }
    return $returnsrc ? getImgUrl($avatarfile) : '<img width="' . $size . '" height="' . $size . '" class="bImg ' . $className . '" src="' . getImgUrl($avatarfile) . '" onerror="this.onerror=null;this.src=\'' . getImgUrl('yun/avatar/noavatar_big.jpg') . '\'">';
}

/**
 * 保存用户头像
 * @param $uid int 用户UID
 * @param $picData object 头像数据流
 * @return string 生成后的头像地址
 */
function setAvatar($uid, $picData, $sync = false) {
    $fpath = avatarFile($uid, 132);
    if ($uid && $picData) {
        saveFile($fpath, $picData, true, $sync);
    }
    return getImgUrl($fpath);
}

/**
 * 根据UID和尺寸生成头像地址
 * @param $uid int 用户UID
 * @param $size int 头像尺寸
 * @return string 生成后的头像地址
 */
function avatarFile($uid, $size) {
    if ($uid) {
        $uid = sprintf("%09d", $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);
        $dir = $uid >= 128164 || $uid <= 30664 ? 'yun' : 'upload';
        return $dir . '/avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) . "_$size.jpg";
    } else {
        return 'yun/avatar/noavatar_big.jpg';
    }
}

/**
 * 快捷 HTTP 请求，支持简单GET和POST请求
 * @param $url string 请求地址
 * @param $data array POST请求数据
 * @param $hasReferer bool 是否需要设置referer
 * @return string content
 */
function getHttp($url, $data = array(), $hasReferer = false) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    if (strExists($url, 'https://')) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    }
    if ($hasReferer) {
        curl_setopt($curl, CURLOPT_REFERER, 'wx.qq.com');
    }
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_2 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Mobile/10B146 MicroMessenger/5.0'); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    if ($data) {
        if (is_array($data)) {
            $p = '';
            foreach ($data as $k => $v) {
                $p .= $k . '=' . urlencode($v) . '&';
            }
//            $data = http_build_query($data, '', '&');
        } else {
            $p = $data;
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $p);
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 20); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl);
    if (curl_errno($curl)) {
        saveLog('http/error', 'Curl Error:url:' . $url . ' ,info:' . curl_error($curl));
    }
    curl_close($curl);
    return $tmpInfo;
}

/**
 * 获取访问者来源（移动端）
 * @param 无
 * @return string 用户客户端设备类型
 */
function getUserAgent() {
    $userAgent = 'unknown';
    $ua = strtolower(USER_AGENT);
    if ($ua) {
        if (preg_match("/(mobile|iphone|android|webos|ios|wap|blackberry|meizu|mobi)/i", $ua)) {
            if (strExists($ua, 'micromessenger')) {
                $userAgent = 'weixin';
            } elseif (strExists($ua, 'iphone')) {
                $userAgent = 'iPhone';
            } elseif (strExists($ua, 'ipad')) {
                $userAgent = 'iPad';
            } elseif (strExists($ua, 'android')) {
                $userAgent = 'Android';
            } elseif (strExists($ua, 'windows phone')) {
                $userAgent = 'Windows Phone';
            } elseif (strExists($ua, 'iemobile')) {
                $userAgent = 'Windows Phone';
            } elseif (strExists($ua, 'symbianos')) {
                $userAgent = 'Symbian';
            } elseif (strExists($ua, 'nokia')) {
                $userAgent = 'Symbian';
            }
        }
    }
    return $userAgent;
}

//根据id生成目录
function setDirList($id, $size = 'original') {
    $id = abs(intval($id));
    $id = sprintf("%09d", $id);
    $dir1 = substr($id, 0, 3);
    $dir2 = substr($id, 3, 2);
    $dir3 = substr($id, 5, 2);
    return $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($id, -2) . "_$size.jpg";
}

/*
 * 对输出内容进行格式化处理
 * @param 需要格式化的内容
 * @return 处理完毕的内容
 */

function express(&$content) {
    static $express = null;
    //表情转换
    if (!$express) {
        $express = F('emotion_express');
    }
    if (!$express) {
        $express_rs = C('emotion');
        foreach ($express_rs as $key => $val) {
            $express[0][] = '[' . $key . ']';
            $express[1][] = '<img class="expimg" src="' . SITE_PATH . 'statics/default/images/smiley/' . $val . '" title="' . $key . '" />';
        }
        F('emotion_express', $express);
    }
    //@的好友，暂时为空
    //
    $content = nl2br(str_replace($express[0], $express[1], $content));
    return $content;
}

/*
 * 生成或检查缩略图
 * @param $image string 原图路径
 * @param $width int 缩略图宽
 * @param $height int 缩略图高
 * @param $isCheck bool 是否仅仅是检查文件存在
 */

function getThumb($image, $thumbId = 0, $isCheck = false) {
    $upConf = C('upload', 'thumb_size');
    if (!isset($upConf[$thumbId])) {
        $thumbId = 0;
    }
    if (strpos($image, 'yun/') === 0) {
        return $image . $upConf[$thumbId]['t'];
    } else {
        T('image/thumb');
        return thumb::init($image, $upConf[$thumbId]['w'], $upConf[$thumbId]['h'], $isCheck);
    }
}

/**
 * 调试函数
 */
function z($str, $exit = true) {
    echo '<pre>';
    print_r($str);
    echo '</pre>';
    if ($exit) {
        exit;
    }
}

/**
 * 格式化金额函数
 */
function priceFormat($price) {
    return number_format($price, 2, '.', '');
}

/**
 * 全概率计算
 *
 * @param array $input array('a'=>0.5,'b'=>0.2,'c'=>0.4)
 * @param int $pow 小数点位数
 * @return array key
 */
function getRand($input, $pow = 3) {
    $much = pow(10, $pow);
    $max = array_sum($input) * $much;
    $rand = mt_rand(1, $max);
    $base = 0;
    $defaultRand = '';
    foreach ($input as $k => $v) {
        $min = $base * $much + 1;
        $max = ($base + $v) * $much;
        if ($min <= $rand && $rand <= $max) {
            return $k;
        } else {
            $base += $v;
        }
        if (!$defaultRand) {
            $defaultRand = $k;
        }
    }
    return $defaultRand;
}

/*
 * 保存图片等上传资源
 * @param $filename 保存文件名
 * @param $data 数据流
 * @return array
 */

function saveFile($filename, $data, $isAvatar = false, $sync = false) {
    $upConf = C('upload');
    $fileInfo = array('status' => 0, 'width' => 0, 'height' => 0, 'type' => '', 'size' => strlen($data), 'url' => $filename);
    //临时处理，检查是否由于又拍云导致失败率飙升
    if ($isAvatar && !$sync) {
        $upConf['yun']['open'] = false;
    }
    //临时处理，检查是否由于又拍云导致失败率飙升
    if ($upConf['yun']['open']) {//开启云存储
        T('image/upyun');
        try {
            $upyun = new UpYun($upConf['yun']['bucket'], $upConf['yun']['user'], $upConf['yun']['pwd']);
            $fileInfo['url'] = $isAvatar ? $filename : $upConf['yun']['dir'] . $filename;
            $rsp = $upyun->writeFile('/' . $fileInfo['url'], $data, true); // 上传图片，自动创建目录
            $fileInfo['status'] = 1;
            $fileInfo['width'] = $rsp['x-upyun-width'];
            $fileInfo['height'] = $rsp['x-upyun-height'];
            $fileInfo['type'] = strtolower($rsp['x-upyun-file-type']);
        } catch (Exception $e) {
//            echo $e->getCode().$e->getMessage();
        }
    } else {//本地存储
        $filename = $upConf['dir'] . ltrim(str_replace(array('./', '../'), '', $filename), $upConf['dir']);
        $fileInfo['url'] = $filename;
        setDir(dirname($filename));
        if (file_put_contents(ROOT . $filename, $data)) {
            $fileInfo['status'] = 1;
        }
    }
    return $fileInfo;
}

/*
 * 调用页面片
 * @param $id int 页面片编号
 * @return string 页面片内容，html片段
 */

function getPageDetail($id) {
    $detail = F('page/detail_' . $id);
    if (!$detail) {
        $info = M('page')->where(array('id' => $id))->getField('content');
        if ($info) {
            F('page/detail_' . $id, $info);
            $detail = $info;
        }
    }
    return $detail;
}

//将接口模块转换为相应的有效链接
function changeModToLink($mod, $para) {
    $link = 'javascript:;';
    if ($mod != 'webview' && $para) {
        $para = '&' . $para;
    }
    switch ($mod) {
        case 'webview'://推送到webview链接
            $link = $para ? $para : 'javascript:;';
            break;
        case 'forum_thread':case 'forum'://推送到帖子
            if ($para) {
                $link = U('weixin/forum/detail', array(), true) . $para;
            } else {
                $link = U('weixin/forum/index');
            }
            break;
        case 'event'://嗨一刻
            if ($para) {
                $link = U('weixin/task/info', array(), true) . $para;
            } else {
                $link = U('weixin/task/event');
            }
            break;
        case 'steward_cate'://推送到商品分类
            $link = U('steward/shop/index', array(), true) . $para;
            break;
        case 'steward_shop'://推送到店铺
            $link = U('steward/goods/index', array(), true) . $para;
            break;
        case 'steward_good'://推送到商品详情
            $link = U('steward/goods/detail', array(), true) . $para;
            break;
        case 'orderdetail'://推送订单详情
            $link = U('steward/order/detail', array(), true) . $para;
            break;
    }
    return $link;
}

//邀请码处理
function invite_code($uid, $operae = 1) {
    $repairstr = 'wxyz';
    //生成邀请码
    if ($operae == 1) {
        $sc = base_convert($uid, 10, 32);
        $result = str_pad($sc, 5, $repairstr, STR_PAD_LEFT);
        $result = substr($result, 1) . substr($result, 0, 1);
        //邀请码还原
    } elseif ($operae == 2) {
        $result = substr($uid, -1) . substr($uid, 0, -1);
        $repairarr = str_split($repairstr);
        foreach ($repairarr as $rv) {
            $result = str_ireplace($rv, '', $result);
        }
        $result = base_convert($result, 32, 10);
    }
    return $result;
}

/*
 * 异步操作处理
 * $mod string 操作模块
 * $postData array post数据
 * $cookieData array cookie数据
 * $isShowReturn bool 是否等待返回结果 默认 false
 * 
 */

function asynHttp($mod = '', $postData = array(), $cookieData = array(), $isShowReturn = false) {
    if (!$mod) {
        showError('缺少操作模块');
    }
    $html = '';
    $nonce = getRandInt(8);
    $timestamp = TIME;
    $tmpArr = array(VCODE, $timestamp, $nonce);
    sort($tmpArr, SORT_STRING);
    $signature = sha1(implode($tmpArr));
    $res = array('header' => '', 'body' => '');
    $urlArr = parse_url(BASE_URL);
    $hostname = $urlArr['host'];
    $port = 80;
    $errno = 0;
    $errstr = null;
    $requestPath = WEB_PATH . 'open/asyn/index?mod=' . $mod . '&nonce=' . $nonce .
            '&timestamp=' . $timestamp . '&signature=' . $signature;
    $fp = fsockopen($hostname, $port, $errno, $errstr, 10);
    if (!$fp) {
        showError("$errstr ($errno)");
    }
    $method = 'GET';
    if (!empty($postData)) {
        $method = 'POST';
    }
    $header = "$method $requestPath HTTP/1.1\r\n";
    $header.="Host: $hostname\r\n";
    if (!empty($cookieData)) {
        $_cookie = strval(NULL);
        foreach ($cookieData as $k => $v) {
            $_cookie .= $k . '=' . $v . '; ';
        }
        $cookie_str = 'Cookie: ' . ($_cookie) . " \r\n"; //传递Cookie  
        $header .= $cookie_str;
    }
    if (!empty($postData)) {
        $_post = strval(NULL);
//        foreach ($postData as $k => $v) {
//            $_post[] = $k . '=' . urlencode($v); //必须做url转码以防模拟post提交的数据中有&符而导致post参数键值对紊乱
//        }
//        $_post = implode('&', $_post);
        $_post = http_build_query($postData, '', '&');
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n"; //POST数据
        $header .= "Content-Length: " . strlen($_post) . "\r\n"; //POST数据的长度
        $header.="Connection: Close\r\n\r\n"; //长连接关闭
        $header .= $_post; //传递POST数据
    } else {
        $header.="Connection: Close\r\n\r\n"; //长连接关闭
    }
    fwrite($fp, $header);
    if ($isShowReturn) {
        while (!feof($fp)) {
            $html.=fgets($fp);
        }
        list($res['header'], $res['body']) = preg_split('/\r\n\r\n|\n\n|\r\r/', $html, 2);
    }
    fclose($fp);
    return $isShowReturn ? $res : true;
}
