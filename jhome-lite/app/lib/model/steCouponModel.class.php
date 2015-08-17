<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}
/*
 * 小管家抵价券模型
 */

class steCouponModel extends Model {

    function __construct() {
        parent::__construct();
        $this->dbTable = 'ste_coupon';
    }

    /*
     * 获取我的可用抵价券
     * $uid int UID
     * $cityid int 城市ID
     * $type int 客户端类型 0:不限制 1:微信 2:App
     */

    public function getMyCoupon($uid, $cityId, $type = 0) {
        $where = array('used_uid' => (int) $uid, 'is_used' => 0, 'city_id' => (int) $cityId,
            'used_time' => 0, 'start_time[<=]' => TIME, 'end_time[>=]' => TIME);
        if ($type && in_array($type, array(1, 2))) {
            $where['use_client'] = array(0, $type);
        }
        $rs = $this->field('stype,smod,coupon_title,coupon_code,start_amount,'
                                . 'coupon_money,end_time,use_cate,use_shop')
                        ->where($where)->order('coupon_money DESC')->findAll();
        $total = 0;
        if ($rs) {
            $total = $this->getTotal();
            foreach ($rs as $key => $val) {
                //================
                if ($val['use_cate']) {
                    $val['use_cate'] = explode(',', $val['use_cate']);
                    //循环取出所有类目的子类目
                    $cate = M('ste_goods_cate')->field('id,name,sort,pid,path,depth')
                            ->where(array('city_id' => user::$userInfo['city_id'], 'is_show' => 1, 'is_del' => 0))
                            ->order('sort DESC')
                            ->select('id');
                    $cateList = array();
                    foreach ($val['use_cate'] as $v) {
                        $cates = D('tree')->getSubs($cate, $v, true);
                        if (isset($cates['items']) && $cates['items']) {
                            $cateList = array_merge($cateList, $cates['items']);
                        }
                        $rs[$key]['cateNameList'][$v] = $cate[$v]['name'];
                    }
                } else {
                    $rs[$key]['cateNameList'] = array();
                }
                $rs[$key]['useShopList'] = array();
                if ($val['use_shop']) {
                    $val['use_shop'] = explode(',', $val['use_shop']);
                    $shopList = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')
                            ->where(array('shop_id' => $val['use_shop']))
                            ->select('shop_id', false);
                    foreach ($shopList as $v) {
                        $rs[$key]['useShopList'][$v['shop_id']] = $v['shop_name'] . ($v['shop_alt_name'] ? '[' . $v['shop_alt_name'] . ']' : '');
                    }
                }
                $rs[$key]['leftTime'] = round((($val['end_time'] - TIME) / 86400), 0).'天';
                if ($rs[$key]['leftTime'] < 1) {
                    if ($val['end_time'] > TIME) {
                        $rs[$key]['leftTime'] = round((($val['end_time'] - TIME) / 3600), 0) . '小时';
                    } else {
                        $rs[$key]['leftTime'] = '0天';
                    }
                }
                //================
            }
            return array('rs' => $rs, 'total' => $total);
        } else {
            return array('rs' => array(), 'total' => 0);
        }
    }

    /*
     * 检查抵价券是否可用
     * @param $couponUsed string 抵价券码
     * @param $cityId int 用户城市id
     * @return array array('status'=>0,'info'=>'','offset'=>'');
     */

    public function checkCoupon($couponUsed, $orderAmount, $cityId, $uid, $isNoNew = false, &$goodsInfo = array(), $type = 1) {
        $rs = $this->where(array('coupon_code' => $couponUsed, 'city_id' => $cityId))->find();
        if (!$rs) {
            return array('status' => 0, 'info' => '抵价券不存在');
        }
        if ($rs['is_used']) {
            return array('status' => 0, 'info' => '该券已失效');
        }
        if ($rs['start_time'] && $rs['start_time'] > TIME) {
            return array('status' => 0, 'info' => '该券尚未生效');
        }
        if ($rs['end_time'] && $rs['end_time'] < TIME) {
            return array('status' => 0, 'info' => '该券已过期');
        }
        if ($rs['start_amount'] > $orderAmount) {
            return array('status' => 0, 'info' => '订单满' . $rs['start_amount'] . '元才可使用');
        }
        if ($rs['used_uid'] && $rs['used_uid'] != $uid) {
            return array('status' => 0, 'info' => '该券已绑定到其他帐户');
        }
        //新人券
        if (!$isNoNew && $rs['stype'] == 2 && !D('steorder')->checkIsNewShopper($uid)) {
            return array('status' => 0, 'info' => '您不满足新人券使用条件');
        }
//        z($goodsInfo);
        //检查客户端
        if ($rs['use_client'] && $rs['use_client'] != $type) {
            return array('status' => 0, 'info' => '不支持当前客户端使用');
        }
        //检查品类券
        if ($rs['use_cate']) {
            $rs['use_cate'] = explode(',', $rs['use_cate']);
            //循环取出所有类目的子类目
            $cate = M('ste_goods_cate')->field('id,name,sort,pid,path,depth')->where(array('city_id' => $cityId, 'is_show' => 1, 'is_del' => 0))->order('sort DESC')->select('id');
            $cateList = array();
            foreach ($rs['use_cate'] as $val) {
                $cates = D('tree')->getSubs($cate, $val, true);
                if (isset($cates['list']) && $cates['list']) {
                    $cateList = array_merge($cateList, $cates['list']);
                }
            }
            $totalAmount = 0;
            foreach ($goodsInfo as $val) {
                if (in_array($val['cate_id'], $cateList)) {
                    $totalAmount+=$val['price'] * $val['goods_counts'];
                }
            }
            if (!$totalAmount || ($rs['start_amount'] && $rs['start_amount'] > $totalAmount)) {
                return array('status' => 0, 'info' => '该品类券需指定品类下商品满' . $rs['start_amount'] . '元才可使用');
            }
            if ($totalAmount <= $rs['coupon_money']) {
                $rs['coupon_money'] = priceFormat($totalAmount);
            }
        }
        //检查商家券
        if ($rs['use_shop']) {
            $rs['use_shop'] = explode(',', $rs['use_shop']);
            $totalAmount = 0;
            foreach ($goodsInfo as $val) {
                if (in_array($val['shop_id'], $rs['use_shop'])) {
                    $totalAmount+=$val['price'] * $val['goods_counts'];
                }
            }
            if (!$totalAmount || ($rs['start_amount'] && $rs['start_amount'] > $totalAmount)) {
                return array('status' => 0, 'info' => '该商家券需指定商家的商品满' . $rs['start_amount'] . '元才可使用');
            }
            if ($totalAmount <= $rs['coupon_money']) {
                $rs['coupon_money'] = priceFormat($totalAmount);
            }
        }
        /*
         * 临时加上，限制每天只能使用一张
         */
        $stime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $etime = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        if ($this->where(array('used_uid' => $uid, 'city_id' => $cityId, 'used_time[>=]' => $stime, 'used_time[<=]' => $etime, 'is_used' => 1))->count() >= 2) {
            return array('status' => 0, 'info' => '每天只能使用2张抵价券');
        }
        return array('status' => 1, 'info' => 'ok', 'type' => $rs['stype'], 'offset' => $rs['coupon_money']);
    }

    /*
     * 处理抵价券
     * @param $coupon string 抵价券码
     * @param $cityId int 用户城市id
     * @param $orderSn string 订单号
     * @param $uid int 使用者uid
     * @return array
     */

    public function setUserCoupon($coupon, $orderAmount, $cityId, $orderSn, $uid) {
//        $check = $this->checkCoupon($coupon, $orderAmount, $cityId, $uid, true);
//        if (!$check['status']) {
//            return $check;
//        }
        $info = $this->update(array('is_used' => 1, 'used_uid' => $uid, 'used_time' => TIME,
            'order_sn' => $orderSn), array('coupon_code' => $coupon));
        return array('status' => 1, 'info' => $info);
    }

    /*
     * 退还抵价券,仅限用户及管理员取消订单使用
     * @param $orderSn string 订单号
     * @return array 处理结果
     */

    public function cancelCoupon($orderSn, $uid) {
        $rs = $this->field('id')->where(array('used_uid' => $uid, 'order_sn' => $orderSn))->find();
        if (!$rs) {
            return array('status' => 0, 'info' => '抵价券不存在');
        }
        return array('status' => 1, 'info' => $this->update(array('is_used' => 0,
                'used_time' => 0, 'order_sn' => ''), array('id' => $rs['id'])));
    }

    /*
     * 随机发放指定类型的券,只限发放红包等随机抽取操作
     * @param $uid int UID
     * @param $cityId int 城市ID
     * @param $type int 抵价券类型
     * @param $smod int 小类
     * @param $limit int 限领数量
     * return array
     */

    public function addRandCoupon($uid, $cityId, $type = 0, $smod = 0, $couponMoney = 0, $limit = 0) {
        //先判断是否领取了该条件下的券
        if ($limit && $this->where(array('used_uid' => $uid, 'stype' => $type, 'city_id' => $cityId, 'smod' => $smod))->count() >= $limit) {
            return array('status' => 0, 'msg' => '您已经领取过啦');
        }
        //抽取一张符合条件的券
        $rs = $this->where(array('used_uid' => 0, 'stype' => $type, 'city_id' => $cityId, 'smod' => $smod,
                    'is_used' => 0, 'coupon_money' => $couponMoney))->find();
        if (!$rs) {//说明券发放完毕
            return array('status' => 0, 'msg' => '券领完了');
        } else {
            if ($this->update(array('used_uid' => $uid), array('id' => $rs['id']))) {
                return array('status' => 1, 'rs' => $rs, 'msg' => 'ok');
            } else {
                return array('status' => 0, 'msg' => '领取失败，请重试');
            }
        }
    }

    /*
     * 二维码扫描领券
     * @param $objData array 生成的券组数据
     */

    public function addPrizeCoupon($arrData = array()) {
        if (!$arrData) {
            return array('status' => 0, 'msg' => '抵价券数据有误');
        }
        //判断用户拥有的有效抵价券是否到达上限15张
        if (isHave($arrData['used_uid']) && $this->where(array('used_uid' => $arrData['used_uid'], 'is_used' => 0, 'end_time[>]' => TIME))->count() >= 15) {
            return array('status' => 0, 'msg' => '您账户里未使用抵价券已超过15张');
        }
        $data = array(
            'stype' => 1,
            'smod' => 0,
            'use_client' => 0,
            'use_cate' => '',
            'use_shop' => '',
            'city_id' => 0,
            'coupon_title' => '',
            'coupon_code' => '',
            'start_amount' => 0,
            'coupon_money' => 0,
            'is_used' => 0,
            'used_uid' => 0,
            'used_time' => 0,
            'order_sn' => '',
            'start_time' => 0,
            'end_time' => 0,
            'info_time' => 0
        );
        $objData = array();
        foreach ($data as $key => $val) {
            $objData[$key] = isset($arrData[$key]) ? $arrData[$key] : $val;
        }
        $id = $this->insert($objData);
        return $id ? array('status' => 1, 'msg' => $id) : array('status' => 0, 'msg' => '添加失败');
    }

    /*
     * 生成优惠券码，不可与现有券码重合
     */

    public function getCouponCode() {
        $code = getRandInt();
        if ($this->field('id')->where(array('coupon_code' => $code))->find()) {
            $code = $this->getCouponCode();
        }
        return $code;
    }

    /*
     * 检查同日期的抵价券是否有使用过
     */

    public function checkOneDayCoupon($uid, $infotime) {
        return $this->field('id')->where(array('used_uid' => $uid, 'is_used' => 1, 'info_time' => $infotime))->find() ? true : false;
    }

    /*
     * 新注册用户领取之前活动中抢到的抵价券
     * @param $uid Int 用户UID
     * @param $userID string 用户标示，手机号或者微信openid
     * @param type string 标示类型，openid、phone
     */

    public function getMyCouponByEvent($uid, $userID = '', $type = 'phone') {
        $count = 0;
        $where = array('is_grant' => 0);
        switch ($type) {
            case 'openid':
                $where['openid'] = $userID;
                break;
            case 'phone':
                $where['phone'] = $userID;
                break;
        }
        $rs = M('ste_order_coupon')->field('id,phone,coupon_item')->where($where)->findAll(false);
        if ($rs) {
            $phone = '';
            $ids = array();
            foreach ($rs as $val) {
                $ids[] = $val['id'];
                if (!$phone) {
                    $phone = $val['phone'];
                }
                $data = json_decode($val['coupon_item'], true);
                $data['used_uid'] = $uid;
                if (isset($data['expire_date'])) {//新的分享接口，支持自由设置过期日期
//                    $data['expire_date'] && $data['end_time'] = $data['expire_date'];
                    unset($data['expire_date']);
                }
//                 else {
//                    $data['end_time'] = mktime(23, 59, 59, date('m'), date('d') + 15, date('Y'));
//                }
                $infoData = $this->addPrizeCoupon($data);
                $infoData['status'] && $count+=1;
            }
            if ($type == 'openid' && $phone) {
                D('member')->update(array('phone' => $phone), array('uid' => $uid));
            }
            if ($ids) {
                M('ste_order_coupon')->update(array('is_grant' => 1), array('id' => $ids));
            }
        }
        return $count;
    }

    /*
     * App新用户下单后，推荐人获得抵价券
     * @param $uid Int 用户UID
     * @param $oid Int 当前的订单号
     */

    public function thankpromotion($uid, $oid) {
        //是否App新下单用户
        $rs = M('ste_order')->where(array('uid' => $uid, 'order_source[!]' => 0, 'order_id[!]' => $oid, 'status' => array(7, 11)))->find();
        if ($rs) {
            return false;
        } else {
            $refer = M('invite')->where(array('beuid' => $uid))->find();
            if ($refer) {
                //判断邀请人为地推人员直接返回false
                if (($refer['uid'] >= 112856 && $refer['uid'] <= 112955) || ($refer['uid'] >= 151609 && $refer['uid'] <= 151708)) {
                    M('invite')->update(array('is_order' => 1), array('beuid' => $uid));
                    return false;
                }
                if (!$refer['status'] && $refer['coupon_money'] && isset($refer['coupon_info'])) {
                    $coupon_info = json_decode($refer['coupon_info'], true);
                    //判断是否为多张券
                    if (isHave($coupon_info[0])) {
                        foreach ($coupon_info as $ck => $cv) {
                            if ($cv['coupon_title'] && $refer['coupon_money']) {
                                $data = array(
                                    'stype' => $cv['stype'],
                                    'smod' => $cv['smod'],
                                    'use_client' => $cv['use_client'],
                                    'city_id' => $cv['city_id'],
                                    'coupon_title' => $cv['coupon_title'],
                                    'coupon_code' => $this->getCouponCode(),
                                    'start_amount' => $cv['start_amount'],
                                    'coupon_money' => $cv['coupon_money'],
                                    'used_uid' => $refer['uid'],
                                    'start_time' => TIME,
                                    'end_time' => mktime(23, 59, 59, date('m'), date('d') + $cv['passday'], date('Y')), //30天有效期
                                    'info_time' => TIME
                                );
                            } else {
                                return false;
                            }
                            $this->addPrizeCoupon($data);
                        }
                        //改变邀请状态
                        M('invite')->update(array('status' => 1, 'is_order' => 1), array('beuid' => $uid));
                        return true;
                    } else {
                        if ($coupon_info['coupon_title'] && $refer['coupon_money']) {
                            $data = array(
                                'stype' => $coupon_info['stype'],
                                'smod' => $coupon_info['smod'],
                                'use_client' => $coupon_info['use_client'],
                                'city_id' => $coupon_info['city_id'],
                                'coupon_title' => $coupon_info['coupon_title'],
                                'coupon_code' => $this->getCouponCode(),
                                'start_amount' => $coupon_info['start_amount'],
                                'coupon_money' => $refer['coupon_money'],
                                'used_uid' => $refer['uid'],
                                'start_time' => TIME,
                                'end_time' => mktime(23, 59, 59, date('m'), date('d') + 15, date('Y')), //30天有效期
                                'info_time' => TIME
                            );
                        } else {
                            return false;
                        }
                        $infoData = $this->addPrizeCoupon($data);
                        if ($infoData['status']) {
                            //改变邀请状态
                            M('invite')->update(array('status' => 1, 'is_order' => 1), array('beuid' => $uid));
                            return true;
                        } else {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

}
