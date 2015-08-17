<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of steadminModel
 * 小管家用户模型
 * @author xlp
 */
class steadminModel extends model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'ste_user';
    }

    /*
     * 根据ID获取管理员信息
     * @param $id int 管理员ID
     * @return array
     */

    public function getUserInfoById($id, $type = 'base') {
        if ($type == 'base') {
            return $this->where(array('user_id' => $id))->find();
        } else {
            return $this->query('SELECT a.*,'
                            . 'b.user_avatar,b.nick_name,b.total_service,b.average_times,b.score_service,b.score_speed,b.total_comment,b.wechat_id '
                            . ' FROM __TABLE__ AS a LEFT JOIN __PRE__ste_worker AS b ON a.user_id=b.user_id WHERE a.user_id=' . $id, true);
        }
    }

    /*
     * 管理员登陆设置
     * @param $userInfo array 管理员信息数组
     * @example array('id'=>1,'psw'=>XXXXXX)
     * @param $remember int cookie有效时间
     * @param $saveLogin bool 是否更新登录信息
     * @return bool
     */

    public function setUserLogin($userInfo = array(), $remember = 0, $saveLogin = true) {
        if ($saveLogin) {
            $this->update("logincount=logincount+1,loginip='" . ip2long(getUserIp()) . "',logintime='" . TIME . "'", array('user_id' => $userInfo['user_id']));
        }
        $saltkey = getRandStr(8);
        $auth = setEnocde($userInfo['user_id'] . "\t" . $userInfo['psw'], steadmin::getAuthKey($saltkey));
        myCookie('ste_saltkey', $saltkey, $remember);
        myCookie('ste_auth', $auth, $remember);
        return true;
    }

    /*
     * 添加工作人员详细数据
     */

    public function addUserDetailInfo($data) {
        if (M('ste_worker')->field('user_id')->where(array('user_id' => $data['user_id']))->find()) {
            M('ste_worker')->update($data, array('user_id' => $data['user_id']));
        } else {
            M('ste_worker')->insert($data);
        }
        return true;
    }

    /*
     * 读取用户企业号
     */

    public function getUserOpenid($userId) {
        return $this->where(array('user_id' => $userId, 'status' => 1))->getField('openid');
    }

    /*
     * 通过服务中心id获取社长openid
     */

    public function getUserOpenidByServiceId($serviceId) {
        return $this->where(array('service_id' => $serviceId, 'groupid' => 9, 'status' => 1))->getField('openid');
    }
    
    /*
     * 通过服务中心id获取社长wx_uid
     */

    public function getUserUidByServiceId($serviceId) {
        return $this->where(array('service_id' => $serviceId, 'groupid' => 9, 'status' => 1))->getField('wx_uid');
    }

    /*
     * 获取工作人员详细资料
     */

    public function getUserDetailByUid($uid) {
        return M('ste_worker')->where(array('user_id' => $uid))->find();
    }

    /*
     * 更新用户工作状态
     * @param $id int 用户user_id
     * @param $act int 用户工作状态
     * @return int 
     */

    public function setUserWorkerStatus($id, $act) {
        return $this->update(array('work_status' => $act), array('user_id' => $id));
    }

    /*
     * 删除管理员
     */

    public function delAdmin($id) {
        //获取管理员信息
        $admin = $this->getUserInfoById($id);
        if (!$admin || !isHave($admin['user_id'])) {
            return false;
        }
        $this->update(array('status' => 0, 'work_status' => 2), array('user_id' => $id));
        //需要删除信息的表
//        $list = array(
//            'ste_goods' => 'user_id',
//            'ste_order_log' => 'user_id',
//            'ste_shop' => 'user_id',
//            'ste_user' => 'user_id',
//            'ste_worker' => 'user_id',
//        );
//        foreach ($list as $k => $v) {
//            M($k)->delete(array($v => $admin['user_id']));
//        }
        return true;
    }

    /*
     * 生成用户密码
     * @param $psw string 原始密码
     * @return string 加密处理后的密码
     */

    public function setUserPassword($psw = '') {
        return md5(md5($psw . substr($psw, 0, 2)));
    }

}
