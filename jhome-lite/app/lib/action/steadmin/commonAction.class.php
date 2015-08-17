<?php

/**
 * Description of CommonAction
 * 小管家后台控制器父类
 * @author xlp
 */
class commonAction extends action {

    //初始化全局信息
    function __construct() {
        parent::__construct();
        T('user/steadmin');
        //全局获取登录用户数据
        steadmin::getLoginUser();
//        T('user/permission');
//        permission::checkPermission(steadmin$adminInfo['groupid']);
        if (!isAjax()) {
            $this->getSysMenu(); //获取系统菜单
            $this->getAdminMenuSetting(); //获取帐号个性化菜单设置
        }
        //全局获取当前页面URL
        $this->assign(array('adminInfo' => steadmin::$adminInfo, 'userGroup' => C('steward/admin', 'group')));
        //清除main_path对后台的影响
        G('main_path', null);
    }

    /*
     * 生成系统菜单
     */

    private function getSysMenu() {
        if ($this->_checkLogin(true) && steadmin::$adminInfo['groupid']) {
            $this->assign(array('sysMenu' => C('steward/menu_group_' . steadmin::$adminInfo['groupid'])));
            return true;
        }
        return false;
    }

    /*
     * 获取帐号菜单个性设置
     */

    private function getAdminMenuSetting() {
        $adminMenuSetting = F('content/setting/steward_id_' . steadmin::$adminInfo['user_id']);
        $list = array('navbar' => 0, 'nav' => 0, 'header' => 0, 'navbg' => 0);
        if ($adminMenuSetting) {
            $list = array_merge($list, $adminMenuSetting);
        }
        $this->assign(array('menuSetting' => $list));
    }

    /*
     * 检查登录，用于必须登录的模块
     * @param 无
     * @return
     */

    protected function _checkLogin($returnBool = false) {
        if (steadmin::$adminInfo['user_id']) {
            return true;
        } else {
            if ($returnBool) {
                return false;
            } elseif (isAjax()) {
                return $this->JsonReturn('必须登录后才能进行此操作');
            } else {
                jumpTo(U('login/index'));
            }
            return $returnBool ? false : jumpTo(U('login/index'));
        }
    }

    /*
     * 检查是否管理员或者客服
     * @param 无
     * @return bool
     */

    protected function _checkIsAdmin() {
        if (steadmin::$adminInfo['isAdmin'] || steadmin::$adminInfo['isService']) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 检查是否是社长
     */

    protected function _checkIsPresident($groupid = 0) {
        if ($groupid == 9 || steadmin::$adminInfo['isPresident']) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 检查操作权限，当前记录是否可以操作
     */

    protected function _checkUser($userId, $shopId) {
        if ($this->_checkIsAdmin() || $userId == steadmin::$adminInfo['user_id'] || $shopId == steadmin::$adminInfo['shop_id']) {
            return true;
        } elseif (steadmin::$adminInfo['groupid'] == 7) {//总店的话，如果商品是由店长上线的，则要判断这个店是不是当前账户下的
            return M('ste_shop')->where(array('shop_id' => $shopId))->getField('user_id') == steadmin::$adminInfo['user_id'] ? true : false;
        } else {
            return false;
        }
    }

    /*
     * 检查操作权限
     */

    protected function _authUser($group = array()) {
        if ($this->_checkLogin() && in_array(steadmin::$adminInfo['groupid'], $group)) {
            return true;
        } else {
            showError('抱歉，您没有操作权限');
        }
    }

    //设置用户配置信息
    protected function _setAdminSetting($field, $arr = array()) {
        $list = $this->_getDefaultSetting();
        $isHave = M('ste_setting')->where(array('city_id' => steadmin::$adminInfo['city_id']))->find();
        if ($isHave) {
            $isHave[$field] = isHave($isHave[$field]) ? json_decode($isHave[$field], true) : $list[$field];
            if ($isHave[$field]) {
                $isHave[$field] = array_merge($isHave[$field], $arr);
            } else {
                $isHave[$field] = $arr;
            }
            return M('ste_setting')->update(array($field => json_encode($isHave[$field])), array('city_id' => steadmin::$adminInfo['city_id']));
        } else {
            $list = array_merge($list, array($field => $arr));
            foreach ($list as $k => $v) {
                $list[$k] = json_encode($v);
            }
            $list['city_id'] = steadmin::$adminInfo['city_id'];
            return M('ste_setting')->insert($list);
        }
    }

    //读取用户配置信息
    protected function _getAdminSetting($field = '*') {
        $setting = M('ste_setting')->field($field)->where(array('city_id' => steadmin::$adminInfo['city_id']))->find();
        if ($field == '*') {
            return $setting;
        } else {
            if ($setting) {
                $setting = json_decode($setting[$field], true);
            }
            $list = $this->_getDefaultSetting($field);
            foreach ($list as $key => $val) {
                if (!isset($setting[$key])) {
                    $setting[$key] = $val;
                }
            }
            return $setting;
        }
    }

    //默认设置
    protected function _getDefaultSetting($field = '') {
        $init = array(
            'auto_reply' => array(
                'subscribe' => '感谢您的关注！',
                'no_result' => '很抱歉，您要查询的内容不存在',
                'show_bind' => 0,
                'reply_type' => 0,
                'id' => 0,
                'no_result_type' => 0,
                'no_result_id' => 0
            ),
            'order_setting' => array(
                'shipping_fee' => 0, //运费设置
                'shipping_fee_offset_sum' => 0, //运费减免起始额度，0为不设置
                'shipping_fee_offset' => 0, //运费减免金额
                'sales_offset_sum' => 0, //订单满减起始额度，0为不设置
                'sales_offset' => 0, //订单满减金额
                'limit_fee' => 0, //限购商品起始金额
                'limit_fee_sum' => 1, //每单限购商品可购买数量
                'too_much_shop_fee' => 0, //订单商家过多加收服务费金额
                'too_much_shop_num' => 0, //订单商家过多加收服务费起始店数
                'shop_fee'=>2.00,//商家服务费
                'max_shop_counts'=>5,//收取服务费商家的上限
            ),
            'corp_info' => array(
                'corpID' => '', //企业CorpID
                'corpSecret' => '', //管理组对应Secret
                'agentId' => 1, //应用ID
                'token' => '', //应用回调模式Token
                'encodingAESKey' => '', //应用回调模式Token
                'departmentId' => 1, //小管家所在部门ID
            ),
        );
        return $field && isset($init[$field]) ? $init[$field] : $init;
    }

    //处理搜索关键字
    protected function safeSearch($str) {
        return str_replace(array('_', '%', "'", '"'), array('\_', '\%', '', ''), trim($str)); // 把 '_','%'过滤掉;
    }

    //接受无过滤字段内容
    protected function _postContent($field = '', $val = '', $fun = 'stripslashes') {
        return htmlspecialchars_decode($this->_post($field, $val, $fun), ENT_QUOTES);
    }

    //清理过滤
    protected function _clearFilter($content = '', $val = '', $fun = 'stripslashes') {
        return $content ? htmlspecialchars_decode($fun($content), ENT_QUOTES) : $val;
    }

    /*
     * 根据aid获取当前帐号所辖的社区和小区
     * @param $aid int 区域ID
     * @param $type int 获取类型 1:
     */

    //获取城市名称
    protected function getCityName($cityid) {
        static $_city = null;
        if (!$cityid) {
            return '';
        }
        if (!isset($_city[$cityid])) {
            $_city[$cityid] = M('city')->where(array('codeid' => $cityid))->getField('name');
        }
        return $_city[$cityid];
    }

    //获取区域名称
    protected function getAreaName($aid, $field = 'aid') {
        static $_area = null;
        if (!$aid) {
            return '';
        }
        if (!isset($_area[$aid])) {
            $_area[$aid] = M('area')->where(array($field => $aid))->getField('name');
        }
        return $_area[$aid];
    }

    //获取小区名称
    protected function getVillageName($vid) {
        static $_village = null;
        if (!$vid) {
            return '';
        }
        if (!isset($_village[$vid])) {
            $_village[$vid] = M('village')->where(array('vid' => $vid))->getField('title');
        }
        return $_village[$vid];
    }

    //获取用户姓名
    protected function _getAdminName($userId, $field = 'username') {
        static $_steadmin = null;
        if (!$userId) {
            return '';
        }
        if (!isset($_steadmin[$userId])) {
            $_steadmin[$userId] = D('steadmin')->where(array('user_id' => $userId))->find();
        }
        return isset($_steadmin[$userId][$field]) ? $_steadmin[$userId][$field] : '';
    }

    //获取店铺名字
    protected function _getShopName($id, $field = '') {
        static $_shopName = null;
        if (!$id) {
            return '';
        }
        if (!isset($_shopName[$id])) {
            $rs = M('ste_shop')->where(array('shop_id' => $id))->find();
            $_shopName[$id] = $rs ? ($field ? $rs[$field] : $rs['shop_name'] . '[' . $rs['shop_alt_name'] . ']') : '';
        }
        return $_shopName[$id];
    }

    //获取类别
    protected function _getCateName($id) {
        static $_cateName = null;
        if (!$id) {
            return '';
        }
        if (!isset($_cateName[$id])) {
            $_cateName[$id] = M('ste_goods_cate')->where(array('id' => $id, 'city_id' => steadmin::$adminInfo['city_id'], 'is_del' => 0))->getField('name');
        }
        return $_cateName[$id];
    }

    //获取商圈名称
    protected function getBusinessZoneName($id, $returnAll = false) {
        static $_businessZone = null;
        if (!$id) {
            return '';
        }
        if (!isset($_businessZone[$id])) {
            $_businessZone[$id] = M('business_zone')->where(array('zid' => $id))->find();
        }
        return !$returnAll && isset($_businessZone[$id]['ztitle']) ? $_businessZone[$id]['ztitle'] : $_businessZone[$id];
    }

    /*
     * 检查是否需要同步图片到云存储
     */

    protected function _sendImageToYunServer($uploadList) {
        $upConf = C('upload');
        if ($uploadList && $upConf['yun']['open']) {//开启云存储
            T('image/upyun');
            foreach ($uploadList as $key => $val) {
                try {
                    $upyun = new UpYun($upConf['yun']['bucket'], $upConf['yun']['user'], $upConf['yun']['pwd']);
                    $uploadList[$key]['savepath'] = $upConf['yun']['dir'] . ltrim($val['savepath'], $upConf['dir']);
//                z($uploadList[$key]['savepath']);
                    $upyun->writeFile('/' . $uploadList[$key]['savepath'], file_get_contents(ROOT . $val['savepath']), true); // 上传图片，自动创建目录
                } catch (Exception $e) {
//            echo $e->getCode().$e->getMessage();
                }
            }
        }
        return $uploadList;
    }

    /*
     * 遍历数组取出符合条件的名称
     */

    protected function _getTipsGroupList($arr, $list) {
        $objData = array();
        if ($list) {
            $list = explode(',', trim($list, ','));
            foreach ($list as $val) {
                isset($arr[$val]) && $objData[] = $arr[$val];
            }
        }
        return $objData;
    }

    /*
     * 获取服务中心缓存
     */

    protected function getServiceCache() {
        return F('steward/service_city_' . steadmin::$adminInfo['city_id']);
    }

    /*
     * 生成打印机缓存
     */

    protected function setPrintCache() {
        $rs = M('ste_machine')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'status' => 1))->findAll(false);
        $objData = array();
        foreach ($rs as $val) {
            if ($val['type']) {//商圈打印机
                $objData['zone'][$val['service_id']][] = array(
                    'partner_id' => $val['partner_id'],
                    'machine_code' => $val['machine_code'],
                    'api_key' => $val['api_key'],
                    'm_key' => $val['m_key'],
                );
            } else {
                $objData['shop'][$val['shop_id']][] = array(
                    'partner_id' => $val['partner_id'],
                    'machine_code' => $val['machine_code'],
                    'api_key' => $val['api_key'],
                    'm_key' => $val['m_key'],
                );
            }
        }
        F('steward/print_city_' . steadmin::$adminInfo['city_id'], $objData);
        return true;
    }

    /*
     * 操作日志
     */

    protected function saveSySLog($actionId = 0, $data = array(), $rsId = 0, $whereData = array(), $desc = '') {
        // $list = array('默认操作', '添加', '编辑', '删除', '处理状态', '登陆系统');
        // V('db/mongo');
        // $db = mongoApi::getInstance();
        // return $db->table('steadminLog')->insert(array(
        //             'user_id' => steadmin::$adminInfo['user_id'],
        //             'user_name' => steadmin::$adminInfo['username'] . '-' . (isset(steadmin::$adminInfo['real_name']) ? steadmin::$adminInfo['real_name'] : ''),
        //             'group_name' => GROUP_NAME,
        //             'action_name' => ACTION_NAME,
        //             'model_name' => MODEL_NAME,
        //             'action' => (isset($list[$actionId]) ? $list[$actionId] : $list[0]), //操作描述
        //             'action_desc' => $desc,
        //             'action_id' => $actionId, //操作对应编号 1:添加 2:编辑 3:删除
        //             'record_id' => $rsId, //对应记录集ID
        //             'where_data' => $whereData, //搜索条件
        //             'data' => $data, //操作的数据
        //             'ip' => getUserIp(),
        //             'infotime' => TIME
        // ));
    }

}
