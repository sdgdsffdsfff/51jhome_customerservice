<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of userAction
 *
 * @author xlp
 */
class userAction extends commonAction {

    protected $isLocal = false; //是否本地调试，本地调试不与企业号同步数据
    protected $adminConfig = array();
    protected $hasWorkerInfo = array(3, 4, 5, 9); //需要员工信息的用户组
    protected $status = array(0 => '<span class="red">未审核</span>', 1 => '正常', 2 => '<span class="gray">无效</span>');

    function __construct() {
        parent::__construct();
        parent::_checkLogin();
        $this->adminConfig = C('steward/admin');
        $this->steSetting = C('steward/setting');
        parent::_authUser(array(1, 7, 9));
    }

    function index() {
        $p = $this->_getid('p', 1);
        $group = $this->_getid('group', 0);
        $q = $this->_get('q');
        $where = 'a.city_id=' . steadmin::$adminInfo['city_id'];
        $countWehre = array('city_id' => steadmin::$adminInfo['city_id']);
        if ($group) {
            $where.=' AND a.groupid=' . ($group - 1);
            $countWehre['groupid'] = ($group - 1);
        }
        if ($q) {
            $where.=" AND a.real_name LIKE '%" . parent::safeSearch($q) . "%'";
            $countWehre['LIKE'] = array('real_name' => $q);
        }
        if (steadmin::$adminInfo['groupid'] == 7) {//总店
            $where.=' AND a.build_uid=' . steadmin::$adminInfo['user_id'];
            $countWehre['build_uid'] = steadmin::$adminInfo['user_id'];
        }
        if (parent::_checkIsPresident()) {//社长
            $where.=' AND a.service_id=' . steadmin::$adminInfo['service_id'];
            $countWehre['service_id'] = steadmin::$adminInfo['service_id'];
        }
        $pageShow = 20;
        $rs = D('steadmin')->query('SELECT a.*,b.stitle AS areaName FROM __TABLE__ AS a LEFT JOIN __PRE__ste_service AS b ON a.service_id=b.sid WHERE ' . $where . ' ORDER BY a.user_id DESC LIMIT ' . ($p - 1) * $pageShow . ',' . $pageShow);
        $total = D('steadmin')->where($countWehre)->count();
        $this->assign(array('rs' => $rs, 'status' => $this->status, 'group' => $this->adminConfig['group'], 'p' => $p, 'total' => $total, 'group_id' => $group, 'setting' => $this->steSetting));
        $this->display();
    }

    function add() {
        $userGroup = $this->adminConfig['group'];
        $group = array();
        $isPresident = false;
        if (steadmin::$adminInfo['groupid'] == 7) {
            $group[8] = $userGroup[8];
            $where = array('status' => 1, 'user_id' => steadmin::$adminInfo['user_id']);
            $isAdmin = false;
        } elseif (parent::_checkIsPresident()) {//社长
            $group[3] = $userGroup[3];
            $group[4] = $userGroup[4];
            $where = array('status' => 1, 'service_id' => steadmin::$adminInfo['service_id']);
            $isAdmin = false;
            $isPresident = true;
        } else {
            $group = $userGroup;
            $where = array('status' => 1);
            $isAdmin = true;
        }
        $shop = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')->where($where)->findAll();
        $this->assign(array('status' => $this->status, 'group' => $group, 'shop' => $shop, 'isAdmin' => $isAdmin, 'isPresident' => $isPresident));
        $this->display();
    }

    function checkexist() {
        $wechat_id = $this->_post('wechat_id');
        if (!$wechat_id) {
            $this->JsonReturn('请输入微信号');
        }
        $exist = M('ste_worker')->field('user_id')->where(array('wechat_id' => $wechat_id))->find();
        if ($exist) {
            $this->JsonReturn('ok', D('steadmin')->getUserInfoById($exist['user_id']), 1);
        } else {
            $this->JsonReturn('该微信号未存在');
        }
    }

    function edit() {
        $id = $this->_getid('id', steadmin::$adminInfo['user_id']);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = D('steadmin')->getUserInfoById($id, 'all');
        if (!$rs) {
            showError('帐号不存在');
        }
        if (steadmin::$adminInfo['groupid'] == 7 && $rs['build_uid'] !== steadmin::$adminInfo['user_id']) {//总店
            showError('没有编辑权限');
        }
        if (parent::_checkIsPresident() && $rs['service_id'] !== steadmin::$adminInfo['service_id']) {//社长
            showError('没有编辑权限');
        }
        $serviceData = parent::getServiceCache();
        $rs['stitle'] = $rs['service_id'] && isset($serviceData[$rs['service_id']]) ? $serviceData[$rs['service_id']]['stitle'] : '';
        $rs['isShowDetail'] = in_array($rs['groupid'], $this->hasWorkerInfo) ? true : false;
        $userGroup = $this->adminConfig['group'];
        if (steadmin::$adminInfo['groupid'] == 7) {//总店
            $group[8] = $userGroup[8];
            $where = array('status' => 1, 'user_id' => steadmin::$adminInfo['user_id']);
            $isAdmin = false;
        } elseif (parent::_checkIsPresident()) {//社长
            $group[3] = $userGroup[3];
            $group[4] = $userGroup[4];
            $group[9] = $userGroup[9];
            $where = array('status' => 1, 'service_id' => steadmin::$adminInfo['service_id']);
            $isAdmin = false;
        } else {//管理员
            $group = $userGroup;
            $where = array('status' => 1);
            $isAdmin = true;
        }
        $shop = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')->where($where)->findAll();
        $this->assign(array('rs' => $rs, 'status' => $this->status, 'group' => $group, 'shop' => $shop, 'isAdmin' => $isAdmin));
        $this->display();
    }

    function post() {
        $objData = array();
        $infoData = array();
        //获取字段内容
        $fieldList = D('steadmin')->getTableFields();
        foreach ($fieldList['fields'] as $key => $val) {
            $objData[$key] = $val['type'] == 'int' ? $this->_postid($key, $val['value']) : $this->_post($key, $val['value']);
        }
        $objData['effective'] = $this->_post('effective', 0);
        $objData['regdateline'] = TIME;
        //内容规则检查
        T('content/validate');
        $validate = array(
            array('username', 'username', '帐户名称不符合要求', 2, 30),
            array('real_name', 'username', '真实姓名不符合要求', 2, 30),
            array('phone', 'phone', '手机号码不符合要求'),
            array('psw', 'min_length', '密码长度必须大于4位', 4),
        );
        if (steadmin::$adminInfo['groupid'] == 7) {
            $objData['groupid'] = 8;
        } else {
            $objData['shop_id'] = 0;
        }
        $infoData = array(
            'nick_name' => $this->_post('nick_name'),
            'user_avatar' => $this->_post('user_avatar'),
            'total_service' => $this->_postid('total_service'),
            'average_times' => $this->_postid('average_times'),
            'score_service' => $this->_post('score_service'),
            'score_speed' => $this->_post('score_speed'),
            'total_comment' => $this->_postid('total_comment'),
            'wechat_id' => $this->_post('wechat_id')
        );
        //工作人员完善资料
        if (in_array($objData['groupid'], $this->hasWorkerInfo)) {
            $validateInData = array(
                array('nick_name', 'username', '称呼不符合要求', 2, 30),
                array('user_avatar', 'required', '用户头像不能为空'),
                array('total_service', 'int', '总服务次数不符合要求'),
                array('average_times', 'int', '服务时间不符合要求'),
                array('score_service', 'double', '服务评分不符合要求'),
                array('score_speed', 'double', '速度评分不符合要求'),
                array('total_comment', 'int', '评论人次不符合要求')
            );
            if (!validate::check($validateInData, $infoData)) {
                $this->JsonReturn(validate::getError());
            }
            $validate[] = array('service_id', 'required', '服务中心不能为空');
        }
        //社长必须有服务中心
        if ($objData['groupid'] == 9) {
            if (!$objData['service_id']) {
                $this->JsonReturn('服务中心不能为空');
            } elseif (D('steadmin')->field('user_id')->where(array('city_id' => steadmin::$adminInfo['city_id'],
                        'service_id' => $objData['service_id'], 'groupid' => 9, 'status' => 1))->find()) {//社长只能有一个帐号
                $this->JsonReturn('该服务中心已有社长帐号');
            }
        }
        //社长只能创建配货员和小管家帐号
        if (parent::_checkIsPresident()) {
            if (!in_array($objData['groupid'], array(3, 4))) {
                $this->JsonReturn('权限不足');
            } else {
                $objData['service_id'] = steadmin::$adminInfo['service_id'];
            }
        }
        if (!validate::check($validate, $objData)) {
            $this->JsonReturn(validate::getError());
        }
        if ($objData['psw'] != $this->_post('repsw')) {
            $this->JsonReturn('两次密码不一致，请检查');
        }
        $exist = D('steadmin')->field('user_id')
                        ->where(array('city_id' => steadmin::$adminInfo['city_id'], 'username' => $objData['username']))->find();
        if ($exist) {
            $this->JsonReturn('该帐户名已存在');
        }
//        $exist = D('steadmin')->field('user_id')
//                        ->where(array('city_id' => steadmin::$adminInfo['city_id'], 'phone' => $objData['phone']))->find();
//        if ($exist) {
//            $this->JsonReturn('该手机号已经存在');
//        }
//        if ($infoData['wechat_id']) {
//            $exist = M('ste_worker')->field('user_id')
//                            ->where(array('wechat_id' => $infoData['wechat_id']))->find();
//            if ($exist) {
//                $this->JsonReturn('该微信号已经存在');
//            }
//        }
        $objData['psw'] = D('steadmin')->setUserPassword($objData['psw']);
        if ($objData['effective']) {
            $objData['effective'] = inTime($objData['effective']);
        } else {
            $objData['effective'] = 0;
        }
        $objData['status'] = 1;
        $objData['city_id'] = steadmin::$adminInfo['city_id'];
        $objData['build_uid'] = steadmin::$adminInfo['user_id'];
        $syncToQyh = false;
//        z($objData);
        $id = D('steadmin')->insert($objData);
        if ($this->isLocal) {
            $objData['openid'] = 'steward_' . $id;
            $syncToQyh = false;
        } else {
            //是否已经存在于企业号
            if ($objData['openid']) {
                T('weixin/qy/qyWeixin.api');
                qyApi::init(steadmin::$adminInfo['city_id']);
                $onlineinfo = qyApi::userGet($objData['openid']);
                if ($onlineinfo) {
                    $qiyehaoinfo['userid'] = $objData['openid'];
                    $corp_info = $this->_getAdminSetting('corp_info');
                    $onlineinfo['department'][] = intval($corp_info['departmentId']);
                    $qiyehaoinfo['department'] = $onlineinfo['department'];
                    if (isHave($infoData['wechat_id'])) {
                        $qiyehaoinfo['weixinid'] = $infoData['wechat_id'];
                    }
                    if (!qyApi::userUpdate($qiyehaoinfo)) {
                        $this->JsonReturn('同步到企业号通讯录失败 ' . qyApi::$errorMsg);
                    }
                } else {
                    $this->JsonReturn('UserId不存在于企业号通讯录中');
                }
                $objData['openid'] = $objData['openid'];
            } else {
                $objData['openid'] = 'steward_' . $id;
                $syncToQyh = true;
            }
        }
        D('steadmin')->update(array('openid' => $objData['openid']), array('user_id' => $id));
        //===记录操作日志====
        parent::saveSySLog(1, $objData, 0, array(), '帐号管理-添加');
        //===记录操作日志====
        if ($id) {
            if (in_array($objData['groupid'], $this->hasWorkerInfo)) {
                $infoData['user_id'] = $id;
                //插入员工信息
                D('steadmin')->addUserDetailInfo($infoData);
            }
//添加到企业号通讯录
            if ($syncToQyh) {
                T('weixin/qy/qyWeixin.api');
                qyApi::init(steadmin::$adminInfo['city_id']);
                $qiyehaoinfo['userid'] = 'steward_' . $id;
                $qiyehaoinfo['name'] = $objData['real_name'];
                $qiyehaoinfo['mobile'] = $objData['phone'];
                if (isHave($infoData['wechat_id'])) {
                    $qiyehaoinfo['weixinid'] = $infoData['wechat_id'];
                }
                $groupidname = $this->adminConfig['group'];
                $qiyehaoinfo['position'] = $groupidname[$objData['groupid']];
                if (!qyApi::userCreate($qiyehaoinfo)) {
                    $this->JsonReturn('同步到企业号通讯录失败 ' . qyApi::$errorMsg);
                }
            }
            $this->JsonReturn('ok', $id, 1);
        } else {
            $this->JsonReturn('数据插入失败');
        }
    }

    function save() {
        $id = $this->_postid('id', 0);
        if (!$id) {
            $this->JsonReturn('参数丢失');
        }
        $objData = array();
        $noFields = array('logincount', 'loginip', 'logintime', 'regdateline', 'build_uid', 'shop_id', 'unionid');
        if (!parent::_checkIsAdmin()) {
            $noFields = array_merge($noFields, array('city_id', 'service_id'));
        }
        //获取字段内容
        $fieldList = D('steadmin')->getTableFields($noFields);
        foreach ($fieldList['fields'] as $key => $val) {
            $objData[$key] = $val['type'] == 'int' ? $this->_postid($key, $val['value']) : $this->_post($key, $val['value']);
        }
        $objData['repsw'] = $this->_post('repsw');
        $rs = D('steadmin')->where(array('user_id' => $id))->find();
        if (!parent::_checkIsPresident() && !parent::_checkIsAdmin() && $rs['user_id'] != steadmin::$adminInfo['user_id'] && $rs['build_uid'] !== steadmin::$adminInfo['user_id']) {
            $this->JsonReturn('权限不足');
        }
        //社长
        if (parent::_checkIsPresident()) {
            if ($rs['service_id'] !== steadmin::$adminInfo['service_id']) {
                $this->JsonReturn('权限不足');
            } else {
                $objData['service_id'] = steadmin::$adminInfo['service_id'];
            }
        }
        //内容规则检查
        T('content/validate');
        $validate = array(
            array('username', 'username', '帐户名称不符合要求', 2, 30),
            array('real_name', 'username', '真实姓名不符合要求', 2, 30),
            array('phone', 'phone', '手机号码不符合要求'),
        );
        if ($objData['psw']) {
            $validate[] = array('psw', 'min_length', '密码长度必须大于4位', 4);
        }
        $infoData = array(
            'nick_name' => $this->_post('nick_name'),
            'user_avatar' => $this->_post('user_avatar'),
            'total_service' => $this->_postid('total_service'),
            'average_times' => $this->_postid('average_times'),
            'score_service' => $this->_post('score_service'),
            'score_speed' => $this->_post('score_speed'),
            'total_comment' => $this->_postid('total_comment'),
            'wechat_id' => $this->_post('wechat_id')
        );
        //工作人员完善资料
        if (isset($objData['groupid']) && in_array($objData['groupid'], $this->hasWorkerInfo)) {
            $validateInData = array(
                array('nick_name', 'username', '称呼不符合要求', 2, 30),
                array('user_avatar', 'required', '用户头像不能为空'),
                array('total_service', 'int', '总服务次数不符合要求'),
                array('average_times', 'int', '服务时间不符合要求'),
                array('score_service', 'double', '服务评分不符合要求'),
                array('score_speed', 'double', '速度评分不符合要求'),
                array('total_comment', 'int', '评论人次不符合要求')
            );
            if (!validate::check($validateInData, $infoData)) {
                $this->JsonReturn(validate::getError());
            }
            $validate[] = array('service_id', 'int', '商圈不能为空');
        }
        if (!validate::check($validate, $objData)) {
            $this->JsonReturn(validate::getError());
        }
        if ($objData['psw'] && ($objData['psw'] != $objData['repsw'])) {
            $this->JsonReturn('两次密码不一致');
        }
        if ($objData['psw']) {
            $objData['psw'] = D('admin')->setUserPassword($objData['psw']);
        } else {
            unset($objData['psw']);
        }
        //检测账户名或手机号是否重复
        $exist = D('steadmin')->field('user_id')
                        ->where(array('city_id' => steadmin::$adminInfo['city_id'], 'username' => $objData['username']))->find();
        if ($exist && $exist['user_id'] != $id) {
            $this->JsonReturn('该帐户名已存在');
        }
//        $exist = D('steadmin')->field('user_id')
//                        ->where(array('city_id' => steadmin::$adminInfo['city_id'], 'phone' => $objData['phone']))->find();
//        if ($exist && $exist['user_id'] != $id) {
//            $this->JsonReturn('该手机号已经存在');
//        }
//        if ($infoData['wechat_id']) {
//            $exist = M('ste_worker')->field('user_id')
//                            ->where(array('wechat_id' => $infoData['wechat_id']))->find();
//            if ($exist && $exist['user_id'] != $id) {
//                $this->JsonReturn('该微信号已经存在');
//            }
//        }
        if ($this->isLocal) {
            $syncToQyh = false;
        } else {
            $syncToQyh = true;
        }
        if (parent::_checkIsAdmin()) {
            $objData['effective'] = $this->_post('effective', 0);
            if ($objData['effective']) {
                $objData['effective'] = inTime($objData['effective']);
            } else {
                $objData['effective'] = 0;
            }
            $objData['status'] = $objData['status'] ? $objData['status'] : 0;
        }
        if ($objData['groupid'] == 8) {//店长身份需要设置店铺
            $objData['shop_id'] = $this->_postid('shop_id', 0);
        } else {
            $objData['shop_id'] = 0;
        }
        if ($objData['groupid'] == 9) {//社长身份必须设置服务中心
            $objData['service_id'] = $this->_postid('service_id', 0);
        }
        //社长必须有服务中心
        if ($objData['groupid'] == 9 && !$objData['service_id']) {
            $this->JsonReturn('服务中心不能为空');
        }
        //社长必须有服务中心
        if ($objData['groupid'] == 9) {
            if (!$objData['service_id']) {
                $this->JsonReturn('服务中心不能为空');
            }
            $checkWhere = array('city_id' => steadmin::$adminInfo['city_id'],
                'service_id' => $objData['service_id'], 'groupid' => 9, 'status' => 1);
            $hasUser = D('steadmin')->where($checkWhere)->getField('user_id');
            if (($hasUser && $hasUser != $id) || (D('steadmin')->where($checkWhere)->count() > 1)) {
                $this->JsonReturn('该服务中心已有社长帐号');
            }
        }
        $objData['city_id'] = steadmin::$adminInfo['city_id'];
        unset($objData['repsw']);
//        z($objData);
        D('steadmin')->update($objData, array('user_id' => $id));
        //===记录操作日志====
        parent::saveSySLog(2, $objData, $id, array('user_id' => $id), '帐号管理-编辑');
        //===记录操作日志====
        if (in_array($objData['groupid'], $this->hasWorkerInfo)) {
            if (!M('ste_worker')->field('user_id')->where(array('user_id' => $id))->find()) {
                $infoData['user_id'] = $id;
                //插入员工信息
                D('steadmin')->addUserDetailInfo($infoData);
            } else {
                M('ste_worker')->update($infoData, array('user_id' => $id));
            }
        }
        if ($syncToQyh) {
            T('weixin/qy/qyWeixin.api');
            qyApi::init(steadmin::$adminInfo['city_id']);
            $qiyehaoinfo['userid'] = $objData['openid'];
            $qiyehaoinfo['name'] = $objData['real_name'];
            $qiyehaoinfo['mobile'] = $objData['phone'];
            if (isHave($infoData['wechat_id'])) {
                $qiyehaoinfo['weixinid'] = $infoData['wechat_id'];
            }
            $groupidname = $this->adminConfig['group'];
            $qiyehaoinfo['position'] = $groupidname[$objData['groupid']];
            if (!qyApi::userUpdate($qiyehaoinfo) && (strpos(qyApi::$errorMsg, '60111') !== 0)) {
                $this->JsonReturn('同步到企业号通讯录失败 ' . qyApi::$errorMsg);
            }
        }
        $this->JsonReturn('ok', null, 1);
    }

    function delete() {
        $id = $this->_postid('id');
        if (!$id) {
            showError('参数丢失');
        }
        $rs = D('steadmin')->where(array('user_id' => $id))->find();
        if ((!parent::_checkIsAdmin() || $rs['city_id'] != steadmin::$adminInfo['city_id'])) {
            showError('权限不足');
        }
        if ($this->isLocal) {
            D('steadmin')->delAdmin(array('user_id' => $id));
        } else {
//从企业号通讯录删除
            T('weixin/qy/qyWeixin.api');
            qyApi::init(steadmin::$adminInfo['city_id']);
            //是否只存在于小管家部门
            if (strpos($rs['openid'], 'steward_') !== false) {
                if (!qyApi::userDelete($rs['openid']) && (strpos(qyApi::$errorMsg, '60111') !== 0)) {
                    $this->JsonReturn('从企业号通讯录删除失败 ' . qyApi::$errorMsg);
                } else {
                    D('steadmin')->delAdmin(array('user_id' => $id));
                }
            } else {
                $corp_info = $this->_getAdminSetting('corp_info');
                $onlineinfo = qyApi::userGet($rs['openid']);
                foreach ($onlineinfo['department'] as $dk => $dv) {
                    if ($dv == $corp_info['departmentId']) {
                        unset($onlineinfo['department'][$dk]);
                        break;
                    }
                }
                $qiyehaoinfo['userid'] = $rs['openid'];
                $qiyehaoinfo['department'] = $onlineinfo['department'];
                if (!qyApi::userUpdate($qiyehaoinfo) && (strpos(qyApi::$errorMsg, '60111') !== 0)) {
                    $this->JsonReturn('从企业号小管家通讯录删除失败 ' . qyApi::$errorMsg);
                } else {
                    D('steadmin')->delAdmin(array('user_id' => $id));
                }
            }
        }
        //===记录操作日志====
        parent::saveSySLog(3, array(), $id, array('user_id' => $id), '帐号管理-删除');
        //===记录操作日志====
        //DOTO:清除相关数据
        $this->JsonReturn('ok', null, 1);
    }

    /*
     * 处理用户状态
     */

    function deal() {
        $id = $this->_postid('id', 0);
        $act = $this->_postid('act', 0);
        if (!$id) {
            showError('参数丢失');
        }
        if (!isset($this->steSetting['work_status'][$act])) {
            showError('参数错误');
        }
        D('steadmin')->setUserWorkerStatus($id, $act);
        //===记录操作日志====
        parent::saveSySLog(4, array('work_status' => $act), $id, array('user_id' => $id), '帐号管理-处理状态');
        //===记录操作日志====
        $this->JsonReturn('ok', $this->steSetting['work_status'][$act], 1);
    }

    /*
     * 测试企业号关注信息
     */

    function testqy() {
        $touser = $this->_get('openid');
        if (!$touser) {
            showError('请输入openid');
        }
        $rs = $this->_sendQIYENotice($touser);
        $this->assign(array('rs' => $rs));
        $this->display();
    }

    /*
     * 企业号通知接口
     * @param $rs 订单信息
     * @param $type 通知类型 1:通知配货员 2:通知小管家 3:订单信息变更
     * @param $userId 接收通知的用户
     */

    private function _sendQIYENotice($touser) {
        if (!$touser) {
            $this->JsonReturn('请先选择帐号');
        }
        T('weixin/qy/qyWeixin.api');
        qyApi::init(steadmin::$adminInfo['city_id']);
        if (!$touser) {
            $this->JsonReturn('小管家系统用户不存在');
        }
        $sendInfo = array(
            'touser' => $touser,
            'msgtype' => 'news',
            'news' => array(
                'articles' => array(
                    array(
                        'title' => '测试企业号是否关注',
                        'createTime' => TIME,
                        'description' => "确认你已经收到消息了...",
                        'picurl' => '',
                        'url' => ''
                    )
                )
            )
        );
        return array('user_id' => 1, 'name' => $touser,
            'result' => qyApi::messageSend($sendInfo) ? 1 : 0, 'msg' => qyApi::$errorMsg);
    }

}
