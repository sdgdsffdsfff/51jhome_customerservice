<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

class permission {

    static public $accessRule = array(); //访问规则

    /*
     * 检查用户权限
     */

    static public function checkPermission($groupid) {
        if (!$groupid || $groupid == 1) {
            return true;
        }
        self::getCacheFile();
        if (self::checkPublicModel() || self::checkGroupModel($groupid)) {
            return true;
        } else {
            showError('抱歉，您没有访问权限');
        }
    }

    /*
     * 检查公共模块
     */

    static private function checkPublicModel() {
        return isset(self::$accessRule['public'][GROUP_NAME][ACTION_NAME]) ? true : false;
    }

    /*
     * 检查当前用户组
     */

    static private function checkGroupModel($groupid) {
        return isset(self::$accessRule['user'][$groupid][GROUP_NAME][ACTION_NAME][MODEL_NAME]) ? true : false;
    }

    /*
     * 获取缓存文件
     */

    static private function getCacheFile() {
        self::$accessRule = F('access/rule');
        if (!self::$accessRule) {
            //生成公共模块缓存
            $rsPub = M('admin_node')->field('n_group,n_action')->where(array('is_public_action' => 1))->findAll();
            if ($rsPub) {
                foreach ($rsPub as $val) {
                    self::$accessRule['public'][$val['n_group']][$val['n_action']] = 1;
                }
            } else {
                self::$accessRule['public'] = array();
            }
            //生成用户组缓存
            $rsUser = M('admin_access')->field('group_id,n_group,n_action,n_model')->findAll();
            if ($rsUser) {
                foreach ($rsUser as $val) {
                    self::$accessRule['user'][$val['group_id']][$val['n_group']][$val['n_action']][$val['n_model']] = 1;
                }
            } else {
                self::$accessRule['user'] = array();
            }
            F('access/rule', self::$accessRule);
        }
        return true;
    }

}
