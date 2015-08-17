<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of memberModel
 * 用户数据库模型
 * @author xlp
 */
class memberModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'user';
    }

    /*
     * 根据UID获取用户信息，用户信息会缓存
     * @param $uid int UID
     * @param $aid int AID
     * @param $type string 获取类型，基本（默认）、全部
     * @return array
     */

    public function getUserInfoById($uid, $type = 'base') {
        static $_userInfo = array();
        if (isset($_userInfo[$type][$uid])) {
            return $_userInfo[$type][$uid];
        }
        if ($type == 'base') {
            $rs = $this->field('uid,aid,city_id,source_id,username,password,phone,'
                            . 'sex,nickname,event_credit,shopping_credit,user_level,is_auth,'
                            . 'village_id,avatar_status,invite_uid,reg_time,reg_ip,last_login_ip,'
                            . 'last_login_time,status,salt')->where(array('uid' => $uid))->find();
        } else {
            $rs = $this->query("SELECT 
               a.uid,a.aid,a.city_id,a.source_id,a.username,a.phone,a.sex,a.nickname,
               a.event_credit,a.shopping_credit,a.user_level,a.is_auth,a.village_id,
               a.avatar_status,a.invite_uid,a.reg_time,a.reg_ip,a.last_login_ip,
               a.last_login_time,a.status,b.subscribe,b.nickname AS weixin_nickname,
               b.sex,b.city,b.user_year,b.user_month,b.user_day,b.country,b.province,b.language,b.subscribe_time  
            FROM __TABLE__ AS a LEFT JOIN __PRE__user_detail AS b ON a.uid=b.uid 
            WHERE a.uid=" . $uid . ' LIMIT 1', true);
        }
        $_userInfo[$type][$uid] = $rs;
        return $rs;
    }

    /*
     * 根据VID获取小区用户数
     * @param $vid int UID
     * @return int
     */

    public function getUserCountsByVid($vid) {
        return $this->where(array('village_id' => $vid))->count();
    }

    /*
     * 查找用户，不存在则新建，仅用于微信通讯接口
     * @param $openid string 用户唯一OPENID
     * @param $aid int AID
     * @return array
     */

    public function getUserDetail($openid, $aid = 0, $isHome = false, $villageId = 0) {
        $arr = array();
        $arr['openid'] = $openid;
        //检查有没有绑定主帐号
        $uid = $this->checkUserIsExists($openid, 'wx', $aid);
        //检查是否是分帐号
        if (!$uid) {
            $uid = $this->checkUserIsExists($openid, 'wx_sub', $aid);
            if ($uid) {
                $arr['openid'] = $this->getOpenidByUids($uid);
            }
        }
        if (!$uid) {//如果既没有绑定主账号也没有绑定分帐号，则是新用户
            if ($villageId) {
                $cityId = M('village')->where(array('vid' => $villageId))->getField('city_id');
            } else {
                $cityId = 0;
            }
            $arr['isBind'] = false;
            $arr['isAuth'] = false;
            $arr['hasWeixinInfo'] = false;
            $arr['village_id'] = $villageId; //小区ID
            $arr['city_id'] = $cityId; //城市ID
            $arr['isNewUser'] = true; //是否新会员
            $arr['status'] = 1;
            $arr['uid'] = $this->addUser(array('city_id' => $cityId, 'village_id' => $villageId, 'aid' => $aid, 'openid' => $openid), $isHome);
        } else {
            $user = $this->getUserInfoById($uid, 'base');
            $arr['uid'] = $user['uid'];
            $arr['village_id'] = $user['village_id']; //小区ID
            $arr['city_id'] = $user['city_id'];
            $arr['isNewUser'] = false; //是否新会员
            $arr['status'] = $user['status'];
            $arr['hasWeixinInfo'] = $this->getUserDetailByUid($uid) ? true : false;
            $arr['isBind'] = $user['nickname'] && $user['phone'] && $user['village_id'] ? true : false;
            $arr['isAuth'] = $user['is_auth'] ? true : false;
        }
        return $arr;
    }

    /*
     * 添加用户信息到第三方登录表中
     * @param $objData array 用户数据
     * @example array('uid' => $uid, 'aid' => $aid, 'type' => 'wx', 'keyid' => $openid, 'info' => '', 'refresh_time' => 0)
     * @return null
     */

    public function addBindInfo($objData) {
        if (!M('user_bind')->field('uid')->where(array('aid' => $objData['aid'], 'type' => $objData['type'], 'keyid' => $objData['keyid']))->find()) {
            return M('user_bind')->insert($objData);
        }
    }

    /*
     * 添加帐户
     * @param $openid string 必须字段
     * @return int uid
     */

    public function addUser($data, $isHome = true) {
        if (!isHave($data['openid'])) {
            return false;
        }
        //创建新用户
        $objData = array();
        //获取字段内容
        $fieldList = $this->getTableFields();
        foreach ($fieldList['fields'] as $key => $val) {
            $objData[$key] = $val['value'];
        }
        //赋值操作
        $objData['aid'] = isset($data['aid']) ? $data['aid'] : 0;
        $objData['village_id'] = isset($data['village_id']) ? $data['village_id'] : 0;
        $objData['city_id'] = isset($data['city_id']) ? $data['city_id'] : 0;
        $objData['username'] = isset($data['username']) ? $data['username'] : '';
        $objData['nickname'] = isHave($data['nickname']) ? $data['nickname'] : '结邻社主_'.  getRandInt(6);
        $objData['sex'] = isset($data['sex']) ? $data['sex'] : 0;
        $objData['status'] = 1;
        $objData['reg_time'] = TIME;
        $objData['reg_ip'] = ip2long(getUserIp());
        $uid = $this->insert($objData);
        if ($uid) {
            if ($isHome) {
                $type = 'wx';
            } else {
                $type = 'wx_sub';
            }
            //插入登录表
            $this->addBindInfo(array('uid' => $uid, 'aid' => $objData['aid'], 'type' => $type, 'keyid' => $data['openid'], 'unionid' => '', 'info' => '', 'refresh_time' => 0));
            //======需要自动创建的表============
            /*
              if (!M('user_count')->field('uid')->where(array('uid' => $uid))->find()) {
              //生成一个用户统计表
              $userCountObj = array();
              //获取字段内容
              $userCountList = M('user_count')->getTableFields();
              foreach ($userCountList['fields'] as $key => $val) {
              $userCountObj[$key] = $val['value'];
              }
              $userCountObj['uid'] = $uid;
              M('user_count')->insert($userCountObj);
              }
             */
            //======需要自动创建的表============
        }
        return $uid;
    }

    /*
     * 用户登录，用于网页中
     * @param $userInfo array 用户数据
     * @example array('uid' => $uid, 'aid' => $aid)
     * @return null
     */

    public function setUserLogin($userInfo = array(), $remember = 0, $saveLogin = true, $loginFrom = 'wx') {
        $saltkey = getRandInt(8);
        $auth = setEnocde($userInfo['uid'] . "\t" . $userInfo['aid'] . "\t" . $loginFrom, user::getAuthKey($saltkey));
        myCookie('saltkey', $saltkey, $remember);
        myCookie('auth', $auth, $remember);
        //修改登录数据
        if ($saveLogin) {
            $this->update(array('last_login_ip' => ip2long(getUserIp()), 'last_login_time' => TIME), array('uid' => $userInfo['uid']));
        }
        return true;
    }

    /*
     * 第三方登录，检查用户是否存在
     * @param $keyid string 第三方登录检查key
     * @param $type string 第三方登录标示符
     * @return int 如果用户存在则返回uid，不存在返回null
     */

    public function checkUserIsExists($keyid, $type, $aid = 0) {
        return M('user_bind')->where(array('aid' => $aid, 'type' => $type, 'keyid' => $keyid))->getField('uid');
    }

    /*
     * 检查用户是否绑定手机号码和姓名
     * @param $uid int UID
     * @param $aid int AID
     * @return array 如果用户绑定返回姓名和手机号，不存在返回空数组
     */

    public function checkUserIsBind($uid) {
        $rs = $this->field('uid,username,phone')->where(array('uid' => $uid))->find();
        if ($rs && $rs['username'] && $rs['phone']) {
            return $rs;
        } else {
            return array();
        }
    }

    /*
     * 根据uid获取（批量获取）用户openid
     * @param $uidArr array or int UID 或者uid数组
     * @param $field string 获取字段名称 keyid or unionid
     * @return array or string  输入的是用户uid数组则返回数组，单个用户则直接返回openid
     */

    public function getOpenidByUids($uidArr, $field = 'keyid') {
        if (is_array($uidArr)) {
            return M('user_bind')->field($field)->where(array('uid' => $uidArr, 'type' => 'wx'))->findAll(false);
        } else {
            return M('user_bind')->where(array('uid' => $uidArr, 'type' => 'wx'))->getField($field);
        }
    }

    /*
     * 根据UID获取用户的微信信息
     * @param $uid int UID
     * @param $aid int AID
     * @return array
     */

    public function getUserDetailByUid($uid) {
        return M('user_detail')->where(array('uid' => $uid))->find();
    }

    /*
     * 添加用户微信信息
     * @param $objData array 用户微信信息，通过微信接口直接获取
     * @return bool
     */

    public function setUserDetail($objData) {
        $hasAvatar = $objData['headimgurl'];
        //如果存在unionid则处理unionid
        if (isHave($objData['unionid'])) {
            M('user_bind')->update(array('unionid' => $objData['unionid']), array('uid' => $objData['uid']));
        }
        $objData = array(
            'uid' => $objData['uid'],
            'subscribe' => isset($objData['subscribe']) ? $objData['subscribe'] : '',
            'nickname' => isset($objData['nickname']) ? trim(preg_replace('~\xEE[\x80-\xBF][\x80-\xBF]|\xEF[\x81-\x83][\x80-\xBF]~', '', $objData['nickname'])) : '',
            'sex' => isset($objData['sex']) ? $objData['sex'] : '',
            'city' => isset($objData['city']) ? $objData['city'] : '',
            'country' => isset($objData['country']) ? $objData['country'] : '',
            'province' => isset($objData['province']) ? $objData['province'] : '',
            'language' => isset($objData['language']) ? $objData['language'] : '',
            'subscribe_time' => isset($objData['subscribe_time']) ? $objData['subscribe_time'] : ''
        );
        //uid不正确，不保存信息
        if (!$objData['uid']) {
            return false;
        }
        //用户未关注，不保存信息
        if (!$objData['subscribe']) {
            return false;
        }
        if ($this->getUserDetailByUid($objData['uid'])) {
            $updateData = $objData;
            unset($updateData['uid']);
            M('user_detail')->update($updateData, array('uid' => $objData['uid']));
        } else {
            M('user_detail')->insert($objData);
        }
        $update = array('nickname' => $objData['nickname'], 'sex' => $objData['sex']);
        //异步获取用户头像
        if ($hasAvatar) {
            asynHttp('avatar', array('uid' => $objData['uid'], 'imgUrl' => $hasAvatar));
        }
        $this->update($update, array('uid' => $objData['uid']));
        return true;
    }

    /*
     * 更新用户积分
     * @param $data array 积分数据信息
     * @example array('act'=>1,'credit'=>10,'event'=>1,'fid'=>0,'content'=>'登录奖励');
     * @param $type string 操作积分类型 活动积分 event(默认)、电商购物 shopping
     * @return int 执行操作的状态 1:成功，0:失败，-1:未登录,-2:操作不正确,-3:用户不存在
     */

    function setUserScore($data = array(), $type = 'event') {
        if (!isHave($data['uid']) && class_exists('user')) {
            $data['uid'] = user::$userInfo['uid'];
        }
        if (!$data['uid']) {
            return -1;
        }
        if ($data['credit'] <= 0) {
            return -2;
        }
        $user = $this->getUserInfoById($data['uid']);
        if (!$user) {
            return -3;
        }
        $data['infotime'] = TIME;
        //设置表和字段名
        if ($type == 'shopping') {
            $field = 'shopping_credit';
            $table = 'shopping_credit_log';
        } else {
            $field = 'event_credit';
            $table = 'event_credit_log';
        }
        if ($data['act']) {//增加积分
            $this->where(array('uid' => $data['uid']))->setInc($field, $data['credit']);
        } else {//减少积分
            if ($data['credit'] > $user[$field]) {
                $data['credit'] = $user[$field];
            }
            $this->where(array('uid' => $data['uid']))->setDec($field, $data['credit']);
        }
        //积分日志
        if (M($table)->insert($data)) {
            return 1;
        } else {
            return 0;
        }
    }

    /*
     * 更新用户统计信息
     * @param $uid int UID
     * @param $field string 需要更新的字段
     * @param $count int 增加的次数，默认为1
     * @return int
     */
    /*
      public function setUserCount($uid, $field, $count = 1) {
      return M('user_count')->where(array('uid' => $uid))->setInc($field, $count);
      }

     */
    /*
     * 编辑用户统计信息
     * @param $uid int UID
     * @param $field string 需要更新的字段
     * @param $count int 减少的次数，默认为1
     * @return int
     */
    /*
      public function editUserCount($uid, $field, $count = 1) {
      return M('user_count')->where(array('uid' => $uid))->setDec($field, $count);
      }
     */
    /*
     * 将用户标记为不可用状态
     */

    public function delUserByUid($uid) {
        return $this->update(array('status' => 2), array('uid' => $uid));
    }

    /*
     * 用户入住小区
     * 用户调整入住小区
     */

    public function bindVillage($uid, $vid) {
        if (!$vid) {
            return false;
        }
        //更新当前默认小区
        $this->update(array('village_id' => $vid), array('uid' => $uid));
        //先判断当前小区是否已存在
        $hasId = M('user_home')->field('uid')->where(array('uid' => $uid, 'village_id' => $vid))->find();
        //将已有的小区信息都改为0
        M('user_home')->update(array('sort' => 0), array('uid' => $uid));
        //存在的话，当前小区改为1
        if ($hasId) {
            M('user_home')->update(array('sort' => 1), array('uid' => $uid, 'village_id' => $vid));
        } else {//不存在新建
            M('user_home')->insert(array('uid' => $uid, 'village_id' => $vid, 'stype' => 0, 'sort' => 1));
        }
        return true;
    }

}
