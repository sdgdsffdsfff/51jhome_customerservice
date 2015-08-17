<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of admin
 * 前端用户读取模块
 * @author xlp
 */
class steadmin {

    public static $adminInfo = array('user_id' => 0, 'username' => '', 'groupid' => 0);

    /**
     * 根据会员编号获取会员信息,获取详细信息时会缓存会员资料
     * @param int $userId 会员编号
     * @return array
     */
    static function getUserById($userId) {
        static $admins = array();
        if (!isset($admins[$userId]) || empty($admins[$userId])) {
            $admins[$userId] = D('steadmin')->getUserInfoById($userId);
        }
        return $admins[$userId];
    }

    /* 获取当前登录用户信息
     * @param string 需要获取的字段
     * @return array or string
     */

    static function getLoginUser($field = '', $getData = '') {
        if (!self::$adminInfo['user_id']) {
            $admin = array();
            if (!$getData) {
                $getData = array('auth' => myCookie('ste_auth'), 'saltkey' => myCookie('ste_saltkey'));
            }
            if ($getData['auth'] && $getData['saltkey']) {
                $getData['auth'] = explode("\t", getDecode($getData['auth'], self::getAuthKey($getData['saltkey'])));
                list($userId, $psw) = empty($getData['auth']) || count($getData['auth']) < 2 ? array(0, '') : $getData['auth'];
                if ($userId) {
                    $admin = self::getUserById($userId); //print_r($admin);
                }
                if ($admin && $admin['psw'] == $psw) {
                    self::$adminInfo = $admin;
                    self::$adminInfo['isAdmin'] = $admin['groupid'] == 1 ? true : false; //是否管理员
                    self::$adminInfo['isService'] = $admin['groupid'] == 2 ? true : false; //是否客服
                    self::$adminInfo['isPresident'] = $admin['groupid'] == 9 ? true : false; //是否社长
                }
            }
        }
        return $field && isset(self::$adminInfo[$field]) ? self::$adminInfo[$field] : self::$adminInfo;
    }

    static function getAuthKey($saltkey) {
        return md5(C('System', 'vcode') . md5($saltkey));
    }

    static function setUserLoginOut() {
        myCookie('ste_auth', null);
        myCookie('ste_saltkey', null);
        $_COOKIE['ste_auth'] = null;
        $_COOKIE['ste_saltkey'] = null;
        self::$adminInfo = array('user_id' => 0, 'username' => '', 'groupid' => 0);
        return true;
    }

}
