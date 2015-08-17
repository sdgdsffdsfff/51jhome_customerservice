<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/** 获得今日零时格林威治时间的时间戳
 *  @param 无
 *  @return int 时间戳
 */
function getTodayTime() {
    static $today_time = NULL;
    if ($today_time === NULL) {
        $today_time = mktime(0, 0, 0, date('m'), date('d'), date('Y')); // - date('Z');
    }
    return $today_time;
}

/** 时间类处理函数，用于社交互动模式显示友好时间
 *  @param $time int 时间戳
 *  @return string 格式化好的时间
 */
function tranTime($time) {
    $ftime = $time;
    $rtime = date("H:i", $time);
    $time = time() - $time;
    if ($time < 60) {
        $str = '刚刚';
    } elseif ($time < 60 * 60) {
        $min = floor($time / 60);
        $str = $min . '分钟前';
    } elseif ($time < 60 * 60 * 24) {
        $h = floor($time / (60 * 60));
        $str = $h . '小时前 ';
    } elseif ($time < 60 * 60 * 24 * 3) {
        $d = floor($time / (60 * 60 * 24));
        if ($d == 1) {
            $str = '昨天 ' . $rtime;
        } else {
            $str = '前天 ' . $rtime;
        }
    } else {
        $str = date('m-d H:i', $ftime);
    }
    return $str;
}

/** 时间类处理函数，用于显示倒计时剩余友好时间
 *  @param $time int 时间戳
 *  @return string 格式化好的时间
 */
function leftTime($time) {
    $time = $time - TIME;
    if ($time < 0) {
        return false;
    }elseif ($time < 60) {
        $str = $time.'秒';
    } elseif ($time < 60 * 60) {
        $min = floor($time / 60);
        $str = $min . '分钟';
    } elseif ($time < 60 * 60 * 24) {
        $h = floor($time / (60 * 60));
        $str = $h . '小时';
    } elseif ($time >= 60 * 60 * 24) {
        $d = floor($time / (60 * 60 * 24));
        $str = $d . '天';
    }
    return $str;
}
