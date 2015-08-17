<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of user
 * 前端用户读取模块
 * @author xlp
 */
class user {

    public static $userInfo = array('uid' => 0, 'aid' => 0);

    /**
     * 根据会员编号获取会员信息,获取详细信息时会缓存会员资料
     * @param int $uid 会员编号
     * @return array
     */
    static function getUserById($uid) {
        return D('member')->getUserInfoById($uid);
    }

    /* 获取当前登录用户信息
     * @param string 需要获取的字段
     * @return array or string
     */

    static function getLoginUser($field = '', $auth = '', $saltKey = '') {
        if (!self::$userInfo['uid']) {
            if (!$auth && !$saltKey && myCookie('auth') && myCookie('saltkey')) {
                $auth = myCookie('auth');
                $saltKey = myCookie('saltkey');
            }
            if ($auth) {
                $auth = explode("\t", getDecode($auth, self::getAuthKey($saltKey)));
                list($uid, $aid) = empty($auth) || count($auth) < 2 ? array(0, 0) : $auth;
                if ($uid) {
                    self::$userInfo = self::getUserById($uid);
                    if (!self::$userInfo) {
                        showError('抱歉，你的帐号存在异常，无法登陆');
                    }
                    switch (self::$userInfo['status']) {
                        case 0://异常
                            self::setUserLoginOut();
                            showError('抱歉，你的帐号存在异常，无法登陆');
                            break;
                        case 1://帐号正常
                            break;
                        case 2://冻结
                            self::setUserLoginOut();
                            showError('抱歉，你的帐号已被冻结，无法登陆');
                            break;
                        case 3://取消关注
                            self::setUserLoginOut();
                            break;
                        default ://未知情况
                            self::setUserLoginOut();
                            showError('抱歉，你的帐号存在异常，无法登陆');
                    }
                    //附加登陆来源
                    self::$userInfo['loginFrom'] = isHave($auth[2]) ? $auth[2] : 'wx';
                    if (getUserAgent() == 'weixin' && self::$userInfo['loginFrom'] != 'wx') {
                        self::setUserLoginOut();
                    }
                }
            }
        }
        return $field && isset(self::$userInfo[$field]) ? self::$userInfo[$field] : self::$userInfo;
    }

    /*
     * 获取会员最新提示信息
     * @param int $uid
     * @return array 返回用户消息条数
     */

    static function getUserTips($uid = 0) {
        $uid = $uid ? $uid : self::$userInfo['uid'];
        $res = D('userNotice')->getNoticeList($uid);
        $userNotice = array();
        $userNotice['all'] = 0;
        foreach ($res as $data) {
            $userNotice[$data['ntype']] = $data['counts'];
            $userNotice['all'] += $data['counts'];
        }
        if ($userNotice['all'] > 99) {
            $userNotice['all'] = 99;
        }
        return $userNotice;
    }

    /* 生成加密密钥
     * @param $saltkey string 随机加盐值
     * @return string
     */

    static function getAuthKey($saltkey) {
        return md5(C('System', 'vcode') . md5($saltkey));
    }

    /* 退出登录
     * @param 无
     * @return bool
     */

    static function setUserLoginOut() {
        myCookie('auth', null);
        myCookie('saltkey', null);
        $_COOKIE['auth'] = null;
        $_COOKIE['saltkey'] = null;
        self::$userInfo = array('uid' => 0, 'aid' => 0);
        return true;
    }

}
