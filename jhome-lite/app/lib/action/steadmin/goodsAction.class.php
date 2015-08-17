<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of goodsAction
 * 商品管理
 * @author skyinter
 */
class goodsAction extends commonAction {

    private $rootCateId = 2;
    protected $minStorage = 10; //低库存提醒阀值

    function __construct() {
        parent::__construct();
        $this->steSetting = C('steward/setting');
    }

    /**
     * 列表
     */
    public function index() {
        parent::_checkLogin();
        parent::_authUser(array(1, 2, 5, 7, 8, 9));
        $title = $this->_get('q'); //关键字搜索
        $st = $this->_get('st', 'name'); //关键字搜索
        $cateId = $this->_getid('cate_id', 0); //类别
        $shopId = $this->_getid('shop_id', 0); //店铺
        $userId = $this->_getid('user_id', 0); //创建者
        $tips = $this->_getid('tips', 0); //参与活动
        $act = $this->_get('act', ''); //属性 是否热门、新品、推荐
        $startTime = $this->_get('stime', ''); //开始时间
        $endTime = $this->_get('etime', ''); //结束时间
        $status = $this->_getid('status', 2); //状态
        $orderby = $this->_getid('orderby', 0); //状态
        $deal = $this->_get('deal', ''); //分组查看
        $p = $this->_getid('p', 1);
        $pageShow = $this->_getid('pageshow', 20);
        $pageShowList = array(20 => 20, 40 => 40, 60 => 60, 80 => 80, 100 => 100);
        $actList = array('is_realtime' => '预约', 'is_hot' => '热门', 'is_new' => '新品',
            'is_recommend' => '推荐', 'is_limited' => '特价');
        $searchType = array('name' => 'goods_name', 'subtitle' => 'goods_subtitle',
            'sn' => 'goods_sn', 'bar' => 'bar_code', 'number' => 'goods_number');
        $orderType = array('默认', '价格高到低↓', '价格低到高↑',
            '下单高到低↓', '下单低到高↑',
            '销量高到低↓', '销量低到高↑',
            '库存高到低↓', '库存低到高↑');
        $orderList = array('gid DESC', 'price DESC', 'price ASC',
            'order_counts DESC', 'order_counts ASC',
            'sale_counts DESC', 'sale_counts ASC',
            'storage_counts DESC', 'storage_counts ASC');
        $dealAction = array(
            'storage' => array('storage_counts[<=]' => $this->minStorage), //低库存快捷查看订单
        );
        if (!isset($orderType[$orderby])) {
            $orderby = 0;
        }
        if (!isset($pageShowList[$pageShow])) {
            $pageShow = 20;
        }
        $where = array();
        if (!$title) {
            unset($_GET['q']);
        }
        if (!$startTime || !$endTime) {
            unset($_GET['stime'], $_GET['etime']);
        }
        if (!$act) {
            unset($_GET['act']);
        }
        if (!$deal) {
            unset($_GET['deal']);
        }
        if ($title && isset($searchType[$st])) {
            $where['LIKE'] = array($searchType[$st] => parent::safeSearch($title));
        }
        if ($deal && isset($dealAction[$deal])) {
            $where = $dealAction[$deal];
        }
        if ($tips) {
            $where['LIKE'] = array('goods_tips' => ',' . ($tips - 1) . ',');
        }
        if ($cateId) {
            $where['cate_id'] = $cateId;
        }
        if ($shopId) {
            $where['shop_id'] = $shopId;
        }
        if ($userId) {
            $where['user_id'] = $userId;
        }
        if ($act && in_array($act, array_keys($actList))) {
            $where[$act] = 1;
        }
        if ($startTime && $endTime) {
            $where['info_time[>=]'] = inTime($startTime);
            $where['info_time[<=]'] = inTime($endTime) + 60 * 60 * 24 - 1;
        }
        if ($status && $status != 3) {
            $where['status'] = ($status - 1);
        }
        //DOTO:后期可能会让另外的帐号也可以来管理这个店（比喻品牌创建了多个分店，
        //那么品牌可以管理所有的分店，分店也可以管理自己的店），那么需要额外指定管理权限
        if (!parent::_checkIsAdmin()) {
            if (steadmin::$adminInfo['groupid'] == 8) {//店长
                $where['shop_id'] = steadmin::$adminInfo['shop_id'];
            } elseif (parent::_checkIsPresident()) {
                $where['service_id'] = steadmin::$adminInfo['service_id'];
            } else {
                $myShopList = M('ste_shop')->field('shop_id,shop_name')->
                        where(array('user_id' => steadmin::$adminInfo['user_id'], 'status' => 1))
                        ->select('shop_id');
                $where['shop_id'] = array_keys($myShopList);
            }
        }
        $rs = M('ste_goods')->where($where)->page($p, $pageShow)->order($orderList[$orderby])->findAll();
        $total = M('ste_goods')->getTotal();
//        z(M('ste_shop')->getAllSql());
//        z($rs);
        if ($rs) {
            foreach ($rs as $k => $v) {
                $rs[$k]['cateName'] = parent::_getCateName($v['cate_id']);
                $rs[$k]['tipsList'] = implode('，', parent::_getTipsGroupList($this->steSetting['goods_tips'], $v['goods_tips']));
                $rs[$k]['shopName'] = parent::_getShopName($v['shop_id']);
                $rs[$k]['userName'] = parent::_getAdminName($v['user_id']);
                $rs[$k]['saleStatus'] = $this->getStatus($v['start_times'], $v['end_times'], $v['status']);
                $rs[$k]['start_times'] = outTime($v['start_times']);
                $rs[$k]['end_times'] = outTime($v['end_times']);
                $rs[$k]['cateData'] = $this->getCateTree($v['cate_id']);
            }
        }
        $cate = M('ste_goods_cate')->field('id,name')->where(
                        array('is_del' => 0, 'city_id' => steadmin::$adminInfo['city_id'], 'pid' => $this->rootCateId))->order('`id`')->findAll(false);
//        z($rs);
        $this->assign(array(
            'orderType' => $orderType,
            'orderby' => $orderby,
            'rs' => $rs, 'total' => $total, 'p' => $p,
            'cate_id' => $cateId, 'shop_id' => $shopId,
            'user_id' => $userId,
            'title' => $title,
            'tips_id' => $tips,
            'status' => $status,
            'actlist' => $actList,
            'act' => $act,
            'cate' => $cate,
            'stime' => $startTime, //开始时间
            'etime' => $endTime, //结束时间
            'pageShow' => $pageShow,
            'pageShowList' => $pageShowList,
            'minStorage' => $this->minStorage,
            'setting' => $this->steSetting));
        $this->display();
    }

    /*
     * 导出商品数据
     */

    public function out() {
        parent::_checkLogin();
        parent::_authUser(array(1));
        $maxRead = 1000; //单次读出最大的数据量
        $serviceId = $this->_getid('service_id', 0); //类别
        $shopId = $this->_getid('shop_id', 0); //店铺
        $where = array('status' => array(0, 1));
        if ($serviceId) {
            $where['service_id'] = $serviceId;
        }
        if ($shopId) {
            $where['shop_id'] = $shopId;
        }
        $count = M('ste_goods')->where($where)->count();
        $pages = ceil($count / $maxRead);
        $rs = array();
        if ($pages) {
            for ($i = 1; $i <= $pages; $i+=1) {
                $list = M('ste_goods')->where($where)->order('shop_id DESC')->limit(($i - 1) * $maxRead . ',' . $maxRead)->findAll(false);
                if ($list) {
                    $rs = array_merge($rs, $list);
                }
            }
        }
        load('csv');
        $myCsv = new csv();
        $list = array();
        foreach ($rs as $val) {
            $cateList = $this->getCateTree($val['cate_id']);
            if ($cateList) {
                $cateName = implode('->', $cateList) . '->' . parent::_getCateName($val['cate_id']);
            } else {
                $cateName = parent::_getCateName($val['cate_id']);
            }
            $list[] = array(
                $val['gid'],
                "\t" . $val['goods_sn'],
                parent::_getShopName($val['shop_id']),
                $cateName,
                parent::_getAdminName($val['user_id']),
                $val['goods_name'],
                $val['goods_subtitle'],
                $val['goods_spec'],
                getImgUrl($val['goods_pic']),
                "\t" . $val['original_price'],
                $val['price_pre'],
                "\t" . $val['price'],
                "\t" . $val['purc_price'],
                "\t" . $val['goods_number'],
                "\t" . $val['bar_code'],
                strip_tags($val['goods_desc']),
                $val['order_counts'],
                $val['sale_counts'],
                $val['storage_counts'],
                "\t" . outTime($val['start_times']),
                "\t" . outTime($val['end_times']),
                ($val['is_limited'] ? '是' : '否'),
                $val['limit_counts'],
                strip_tags($this->getStatus($val['start_times'], $val['end_times'], $val['status']))
            );
        }
        unset($rs);
        $myCsv->write(array(
            '编号', '商品货号', '店铺', '类别', '帐号', '商品名称',
            '副标题', '规格', '图片', '原价', '价格前缀', '售价',
            '进价', '商家货号', '条形码', '描述', '下单量', '销售量',
            '库存', '上架时间', '下架时间', '是否特价', '限购数量', '销售状态',
                ), $list, 'goods_' . date('y-m-d'));
    }

    /*
     * 复制商品
     */

    public function goodscopy() {
        parent::_checkLogin();
        parent::_authUser(array(1));
        $fromShopId = $this->_getid('shop_id', 0);
        $checkFromShop = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'status' => 1, 'shop_id' => $fromShopId))->find();
        if (!$checkFromShop) {
            $this->returnJson = false;
            showError('店铺不存在');
        }
        $rs = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'status' => 1))->order('shop_id DESC')->findAll(false);
        $this->assign(array('rs' => $rs, 'shop' => $checkFromShop));
        $this->display();
    }

    /*
     * 完成商品复制
     */

    public function savecopy() {
        parent::_checkLogin();
        parent::_authUser(array(1));
        set_time_limit(0);
        if (!parent::_checkIsAdmin()) {
            $this->JsonReturn('权限不足');
        }
        $fromShopId = $this->_postid('fromshop', 0);
        $toShopId = $this->_postid('toshop', 0);
        if (!$fromShopId || !$toShopId) {
            $this->JsonReturn('请选择复制和被复制的店铺');
        }
        if ($fromShopId == $toShopId) {
            $this->JsonReturn('复制和被复制的店铺不能是同一家');
        }
        $checkFromShop = M('ste_shop')->field('shop_id')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'status' => 1, 'shop_id' => $fromShopId))->find();
        $checkToShop = M('ste_shop')->field('shop_id,user_id,service_id')->where(array('city_id' => steadmin::$adminInfo['city_id'], 'status' => 1, 'shop_id' => $toShopId))->find();
        if (!$checkFromShop || !$checkToShop) {
            $this->JsonReturn('复制或者被复制的店铺不存在');
        }
        //取出所有
        $goodsList = M('ste_goods')->where(array('shop_id' => $fromShopId, 'status' => 1))->findAll();
        $total = M('ste_goods')->getTotal();
        if ($total) {
            foreach ($goodsList as $key => $val) {
                unset($goodsList[$key]['gid']);
                $goodsList[$key]['goods_sn'] = '';
                $goodsList[$key]['service_id'] = $checkToShop['service_id'];
                $goodsList[$key]['shop_id'] = $checkToShop['shop_id'];
                $goodsList[$key]['user_id'] = $checkToShop['user_id'];
                $goodsList[$key]['order_counts'] = 0;
                $goodsList[$key]['sale_counts'] = 0;
                $goodsList[$key]['storage_counts'] = 9999;
                $goodsList[$key]['hits_counts'] = 0;
                $goodsList[$key]['love_counts'] = 0;
            }
//            z($goodsList);
            //插入商品
            $ids = M('ste_goods')->insertAll($goodsList);
            //批量更新
            if ($ids) {
                //读取所有没有货号的商品
                $goodsSnList = M('ste_goods')->field('gid')->where(array('goods_sn' => ''))->findAll(false);
                foreach ($goodsSnList as $val) {
                    M('ste_goods')->update(array('goods_sn' => $this->_getGoodsSn($val['gid'])), array('gid' => $val['gid']));
                }
                //===记录操作日志====
                parent::saveSySLog(1, array(), 0, array('fromShopId' => $fromShopId, 'toShopId' => $toShopId), '商品管理-批量复制');
                //===记录操作日志====
                $this->JsonReturn('ok', $total, 1);
            }
        } else {
            $this->JsonReturn('抱歉，没有符合条件的商品');
        }
    }

    /*
     * 获取商品类别
     */

    public function cate() {
        parent::_checkLogin();
        $cateId = $this->_getid('cid', 0); //类别
        $myCateid = $this->_getid('cateid', 0); //类别
        if (!$cateId) {
            $this->JsonReturn('请先选择类别');
        }
        $cateList = M('ste_goods_cate')->where(array('city_id' =>
                    steadmin::$adminInfo['city_id'], 'is_del' => 0))->order('sort DESC')->select('id');
        if (!isset($cateList[$cateId])) {
            $this->JsonReturn('类别有误');
        }
        $cates = D('tree')->getSubs($cateList, $cateId, false);
        if (!$cates['list']) {
            $this->JsonReturn('ok', '<option value="' . $cateList[$cateId]['id'] . '">' . $cateList[$cateId]['name'] . '</option>', 1);
        }
        V('tree/catetree');
        $this->JsonReturn('ok', D('tree')->genSelectOption(TreeTool::tree($cateList, $cateId), $myCateid, 4), 1);
    }

    /**
     * 添加
     */
    public function add() {
        parent::_checkLogin();
        parent::_authUser(array(1, 5, 7, 8));
        $cate = M('ste_goods_cate')->field('id,name')->where(
                        array('is_del' => 0, 'city_id' => steadmin::$adminInfo['city_id'], 'pid' => $this->rootCateId))->order('`id`')->findAll(false);
        if (steadmin::$adminInfo['groupid'] == 8) {//店长
            $shop = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')
                            ->where(array('shop_id' => steadmin::$adminInfo['shop_id'], 'status' => 1))->findAll(false);
        } elseif (parent::_checkIsAdmin()) {//管理员
            $shop = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')
                            ->where(array('status' => 1))->findAll(false);
        } else {//商家、总店
            $shop = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')
                            ->where(array('user_id' => steadmin::$adminInfo['user_id'], 'status' => 1))->findAll(false);
        }
        $this->assign(
                array(
                    'cate' => $cate,
                    'shop' => $shop,
                    'setting' => $this->steSetting));
        $this->display();
    }

    /*
     * 保存
     */

    public function post() {
        parent::_checkLogin();
        parent::_authUser(array(1, 5, 7, 8));
        $objData = array();
        //获取字段内容
        $fieldList = M('ste_goods')->getTableFields();
        foreach ($fieldList['fields'] as $key => $val) {
            $objData[$key] = $val['type'] == 'int' ? $this->_postid($key, $val['value']) : $this->_post($key, $val['value']);
        }
        $objData['user_id'] = steadmin::$adminInfo['user_id'];
        $objData['goods_tips'] = $this->_post('goods_tips');
        $objData['start_times'] = $this->_post('start_times');
        $objData['end_times'] = $this->_post('end_times');
        $goodsParameter = $this->_post('goods_parameter');
        $objData['goods_desc'] = parent::_postContent('goods_desc');
        $shops = $this->_post('shops');
        if ($objData['goods_tips']) {
            $objData['goods_tips'] = ',' . implode(',', $objData['goods_tips']) . ',';
        }
        $objData['goods_parameter'] = '';
        if ($goodsParameter) {
            $count = count($goodsParameter['n']);
            for ($i = 0; $i < $count; $i+=1) {
                isHave($goodsParameter['v'][$i]) && $objData['goods_parameter'][] = array($goodsParameter['n'][$i], $goodsParameter['v'][$i]);
            }
        }
        if ($objData['goods_parameter']) {
            $objData['goods_parameter'] = json_encode($objData['goods_parameter']);
        }
        if (!$objData['is_realtime']) {
            $objData['booked_time'] = 0;
        }
        if (!$shops || !is_array($shops)) {
            $this->JsonReturn('店铺不能为空，请检查');
        }
        //内容规则检查
        T('content/validate');
        $validate = array(
            array('goods_name', 'range_length', '商品名称不符合要求', 2, 80),
            array('goods_pic', 'required', '商品小图不能为空'),
            array('price', 'currency', '商品价格不正确'),
            array('px', 'int', '排序值必须为正整数'),
            array('start_times', 'required', '上架时间不能为空'),
            array('end_times', 'required', '下架时间不能为空'),
        );
        if ($objData['original_price']) {
            $validate[] = array('original_price', 'currency', '商品原始价格不正确');
        }
        if (!validate::check($validate, $objData)) {
            $this->JsonReturn(validate::getError());
        }
        //补充数据
        $objData['info_time'] = TIME;
        $objData['refresh_time'] = TIME;
        $objData['start_times'] = inTime($objData['start_times']);
        $objData['end_times'] = inTime($objData['end_times']);
        $objData['status'] = 1;
        //因为要插入商品货号，考虑到添加商品的不频繁性，因此采用依次插入的方式，o(╯□╰)o
//        print_r($shops);
//        z($objData);
        foreach ($shops as $val) {
            $objData['shop_id'] = $val;
            $objData['service_id'] = M('ste_shop')->where(array('shop_id' => $val))->getField('service_id');
            $gid = M('ste_goods')->insert($objData);
            if ($gid) {
                M('ste_goods')->update(array('goods_sn' => $this->_getGoodsSn($gid)), array('gid' => $gid));
            }
        }
        //===记录操作日志====
        $objData['shop_id'] = $shops;
        if (isset($objData['service_id'])) {
            unset($objData['service_id']);
        }
        parent::saveSySLog(1, $objData, $gid, array(), '商品管理-添加');
        //===记录操作日志====
        $this->JsonReturn('ok', null, 1);
    }

    /**
     * 详细
     */
    public function detail() {
        parent::_checkLogin();
        parent::_authUser(array(1, 2, 5, 7, 8, 9));
        $id = $this->_getid('id', 0);
        $isAjax = $this->_getid('is_ajax', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_goods')->where(array('gid' => $id))->find();
        if (!$rs) {
            showError('商品不存在');
        }
        if ((parent::_checkIsPresident() && $rs['service_id'] != steadmin::$adminInfo['service_id']) &&
                !parent::_checkUser($rs['user_id'], $rs['shop_id'])) {
            showError('没有编辑权限');
        }
        if ($rs['goods_parameter']) {
            $rs['goods_parameter'] = json_decode($rs['goods_parameter'], true);
        } else {
            $rs['goods_parameter'] = array();
        }
        $rs['cateName'] = parent::_getCateName($rs['cate_id']);
        $rs['shopName'] = parent::_getShopName($rs['shop_id']);
        $rs['userName'] = parent::_getAdminName($rs['user_id']);
        $rs['tipsName'] = parent::_getTipsGroupList($this->steSetting['goods_tips'], $rs['goods_tips']);
        if ($rs['goods_tips']) {
            $rs['goods_tips'] = explode(',', trim($rs['goods_tips'], ','));
        }
        if ($isAjax) {
            return $this->JsonReturn('ok', $rs, 1);
        }
//        z($rs);
        $this->assign(array('rs' => $rs, 'setting' => $this->steSetting));
        $this->display();
    }

    /*
     * 商品预览
     */

    function preview() {
        $id = $this->_getid('id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_goods')->where(array('gid' => $id))->find();
        if (!$rs) {
            showError('商品不存在');
        }
        steadmin::$adminInfo['city_id'] = 3301;
        $goodPic = $rs['goods_pic'];
        $rs['goods_tips'] = parent::_getTipsGroupList($this->steSetting['goods_tips'], $rs['goods_tips']);
        $rs['goods_pic'] = getImgUrl(getThumb($goodPic, 0));
        $rs['orig_pic'] = getImgUrl(getThumb($goodPic, 2));
        if ($rs['is_realtime'] && $rs['booked_time']) {
            $rs['goods_desc'] = '<p><img src="' . getImgUrl('statics/default/images/steward/2015/presell_' . $rs['booked_time'] . '.png') . '"/></p><br/>' . $rs['goods_desc'];
        }
        $rs['goods_desc'] = htmlspecialchars_decode($rs['goods_desc']);
        $rs['is_delay'] = parent::_getShopName($rs['shop_id'], 'is_delay');
        $rs['cate_name'] = parent::_getCateName($rs['cate_id']);
        $rs['shop_name'] = parent::_getShopName($rs['shop_id'], 'shop_name');
        $rs['order_counts'] = $rs['order_counts'] * 3; ////2015-03-31 新增刷新销量
        $list = explode(',', 'is_delay,gid,cate_id,shop_id,shop_name,goods_name,goods_subtitle,goods_spec,goods_tips,goods_pic,orig_pic,original_price,price_pre,price,goods_desc,order_counts,sale_counts,storage_counts,is_realtime,is_hot,is_new,is_recommend,is_limited,limit_counts');
        foreach ($list as $val) {
            $rs['goodsData'][$val] = $rs[$val];
        }
        $rs['goodsData'] = json_encode($rs['goodsData']);
        if (!$rs['status'] || $rs['status'] == 2) {
            $rs['sale_status'] = 0;
        }
        if ($rs['start_times'] <= TIME) {
            if ($rs['end_times'] < TIME) {
                $rs['sale_status'] = 0;
            } elseif ($rs['end_times'] >= TIME) {
                $rs['sale_status'] = 1;
            }
        } else {
            $rs['sale_status'] = 2;
        }
        $rs['sale_time'] = outTime($rs['start_times']);

//        z($rs);
        $this->assign(array('id' => $id,
            'rs' => $rs,
            'vid' => 0
        ));
        $this->display();
    }

    /**
     * 编辑
     */
    public function edit() {
        parent::_checkLogin();
        parent::_authUser(array(1, 2, 5, 7, 8));
        $id = $this->_getid('id', 0);
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_goods')->where(array('gid' => $id))->find();
        if (!$rs) {
            showError('商品不存在');
        }
        if ((!parent::_checkUser($rs['user_id'], $rs['shop_id'])) &&
                (parent::_checkIsPresident() && $rs['service_id'] != steadmin::$adminInfo['service_id'])) {
            showError('没有编辑权限');
        }
        $rs['goods_tips'] = explode(',', trim($rs['goods_tips'], ','));
        if ($rs['goods_parameter']) {
            $rs['goods_parameter'] = json_decode($rs['goods_parameter'], true);
        }
        $cate = M('ste_goods_cate')->where(array('is_del' => 0, 'city_id' =>
                    steadmin::$adminInfo['city_id']))->order('`id`')->findAll(false);
        $mainCate = M('ste_goods_cate')->field('id,name')->where(array(
                            'is_del' => 0, 'city_id' => steadmin::$adminInfo['city_id'], 'pid' => $this->rootCateId))
                        ->order('`id`')->findAll(false);
        if (steadmin::$adminInfo['groupid'] == 8) {//店长
            $shop = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')
                            ->where(array('shop_id' => steadmin::$adminInfo['shop_id'], 'status' => 1))->findAll();
        } elseif (parent::_checkIsAdmin() || parent::_checkIsPresident()) {//管理员
            $shop = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')
                            ->where(array('status' => 1))->findAll();
        } else {//商家、总店
            $shop = M('ste_shop')->field('shop_id,shop_name,shop_alt_name')
                            ->where(array('user_id' => steadmin::$adminInfo['user_id'], 'status' => 1))->findAll();
        }
        V('tree/catetree');
        $ctree = TreeTool::tree($cate, $rs['cate_id'], 'pid', 'id');
        //找到当前项目的父类
        $parentId = 0;
        if ($ctree) {
            foreach ($ctree as $val) {
                if ($val['pid'] == $this->rootCateId) {
                    $parentId = $val['id'];
                    break;
                }
            }
        }
        $this->assign(
                array(
                    'rs' => $rs,
                    'cate' => $mainCate,
                    'parentId' => $parentId,
                    'shop' => $shop,
                    'setting' => $this->steSetting)
        );
//        z($this->tVar);
        $this->display();
    }

    /*
     * 更新
     */

    public function save() {
        parent::_checkLogin();
        parent::_authUser(array(1, 2, 5, 7, 8));
        $id = $this->_postid('id', 0);
        if (!$id) {
            $this->JsonReturn('参数丢失');
        }
        $rs = M('ste_goods')->where(array('gid' => $id))->find();
        if (!$rs) {
            showError('商品不存在');
        }
        if ((!parent::_checkUser($rs['user_id'], $rs['shop_id'])) &&
                (parent::_checkIsPresident() && $rs['service_id'] != steadmin::$adminInfo['service_id'])) {
            showError('没有编辑权限');
        }
        $objData = array();
        $noField = array('goods_sn', 'user_id', 'sale_counts',
            'hits_counts', 'love_counts', 'info_time', 'shop_id', 'status', 'service_id');
        //获取字段内容
        $fieldList = M('ste_goods')->getTableFields($noField);
        foreach ($fieldList['fields'] as $key => $val) {
            $objData[$key] = $val['type'] == 'int' ? $this->_postid($key, $val['value']) : $this->_post($key, $val['value']);
        }
        $objData['goods_tips'] = $this->_post('goods_tips');
        $objData['start_times'] = $this->_post('start_times');
        $objData['end_times'] = $this->_post('end_times');
        $goodsParameter = $this->_post('goods_parameter');
        $objData['goods_desc'] = parent::_postContent('goods_desc');
        if ($objData['goods_tips']) {
            $objData['goods_tips'] = ',' . implode(',', $objData['goods_tips']) . ',';
        }
        $objData['goods_parameter'] = '';
        if ($goodsParameter) {
            $count = count($goodsParameter['n']);
            for ($i = 0; $i < $count; $i+=1) {
                isHave($goodsParameter['v'][$i]) && $objData['goods_parameter'][] = array($goodsParameter['n'][$i], $goodsParameter['v'][$i]);
            }
        }
        if ($objData['goods_parameter']) {
            $objData['goods_parameter'] = json_encode($objData['goods_parameter']);
        }
        if (!$objData['is_realtime']) {
            $objData['booked_time'] = 0;
        }
        //内容规则检查
        T('content/validate');
        $validate = array(
            array('goods_name', 'range_length', '商品名称不符合要求', 2, 80),
            array('goods_pic', 'required', '商品小图不能为空'),
            array('price', 'currency', '商品价格不正确'),
            array('px', 'int', '排序值必须为正整数'),
            array('start_times', 'required', '上架时间不能为空'),
            array('end_times', 'required', '下架时间不能为空'),
        );
        if ($objData['original_price']) {
            $validate[] = array('original_price', 'currency', '商品原始价格不正确');
        }
        if (!validate::check($validate, $objData)) {
            $this->JsonReturn(validate::getError());
        }
        //补充数据
        $objData['refresh_time'] = TIME;
        $objData['start_times'] = inTime($objData['start_times']);
        $objData['end_times'] = inTime($objData['end_times']);
//        z($objData);
        //判断重复
//        $checkHas = M('ste_goods')->where(array('goods_name' => $objData['goods_name'],
//                    'goods_spec' => $objData['goods_spec'], 'shop_id' => $rs['shop_id'], 'status' => 1))->getField('gid');
//        if ($checkHas && $checkHas != $id) {
//            $this->JsonReturn('同名商品已存在，请检查');
//        }
        //同步店铺信息
        $objData['service_id'] = M('ste_shop')->where(array('shop_id' => $rs['shop_id']))->getField('service_id');
//        z($objData);
        M('ste_goods')->update($objData, array('gid' => $id));
        //===记录操作日志====
        parent::saveSySLog(2, $objData, $id, array('gid' => $id), '商品管理-编辑');
        //===记录操作日志====
        $this->JsonReturn('ok', null, 1);
    }

    /*
     * 删除
     */

    public function delete() {
        parent::_checkLogin();
        parent::_authUser(array(1, 5, 7, 8));
        $id = $this->_postid('id');
        if (!$id) {
            showError('参数丢失');
        }
        $rs = M('ste_goods')->where(array('gid' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('商品不存在');
        }
        if (!parent::_checkUser($rs['user_id'], $rs['shop_id'])) {
            showError('没有编辑权限');
        }
        M('ste_goods')->update(array('status' => 2), array('gid' => $id));
        //===记录操作日志====
        parent::saveSySLog(3, array('status' => 2), $id, array('gid' => $id), '商品管理-删除');
        //===记录操作日志====
        //DOTO:清除相关数据
        $this->JsonReturn('ok', null, 1);
    }

    /*
     * 处理状态
     */

    public function deal() {
        parent::_checkLogin();
        parent::_authUser(array(1, 2, 5, 7, 8, 9));
        $id = $this->_postid('id', 0);
        $act = $this->_postid('act', 0);
        if (!$id) {
            showError('参数丢失');
        }
        if (!isset($this->steSetting['goods_status'][$act])) {
            $this->JsonReturn('参数错误');
        }
        $rs = M('ste_goods')->where(array('gid' => $id))->find();
        if (!$rs) {
            $this->JsonReturn('商品不存在');
        }
        if ((!parent::_checkUser($rs['user_id'], $rs['shop_id'])) &&
                (parent::_checkIsPresident() && $rs['service_id'] != steadmin::$adminInfo['service_id'])) {
            showError('没有编辑权限');
        }
        M('ste_goods')->update(array('status' => $act), array('gid' => $id));
        //===记录操作日志====
        parent::saveSySLog(4, array('status' => $act), $id, array('gid' => $id), '商品管理-更改状态');
        //===记录操作日志====
        $this->JsonReturn('ok', $this->getStatus($rs['start_times'], $rs['end_times'], $act), 1);
    }

    /*
     * 批量处理
     */

    public function batch() {
        parent::_checkLogin();
        parent::_authUser(array(1, 2, 5, 7, 8));
        $gids = $this->_post('gid');
        if (!$gids || !is_array($gids)) {
            $this->JsonReturn('请先选择需要操作的商品');
        }
        $objData = array();
        $list = array('original_price', 'price_pre', 'price', 'storage_counts', 'start_times', 'end_times', 'px', 'cate_id', 'order_counts');
        foreach ($list as $val) {
            $objData[$val] = $this->_post($val, '');
        }
        //内容规则检查
        T('content/validate');
        $validate = array();
        if ($objData['price']) {
            $validate[] = array('price', 'currency', '商品价格不正确');
        } else {
            unset($objData['price']);
        }
        if ($objData['storage_counts']) {
            $validate[] = array('storage_counts', 'int', '库存不正确');
        } else {
            unset($objData['storage_counts']);
        }
        if ($objData['original_price']) {
            $validate[] = array('original_price', 'currency', '商品原始价格不正确');
        } else {
            unset($objData['original_price']);
        }
        if ($objData['start_times']) {
            $objData['start_times'] = inTime($objData['start_times']);
        } else {
            unset($objData['start_times']);
        }
        if ($objData['end_times']) {
            $objData['end_times'] = inTime($objData['end_times']);
        } else {
            unset($objData['end_times']);
        }
        if (!$objData['cate_id']) {
            unset($objData['cate_id']);
        }
        if (!$objData['price_pre']) {
            unset($objData['price_pre']);
        }
        if ($objData['px'] !== '') {
            $objData['px'] = intval($objData['px']);
        } else {
            unset($objData['px']);
        }
        if ($objData['order_counts'] !== '') {
            $objData['order_counts'] = intval($objData['order_counts']);
        } else {
            unset($objData['order_counts']);
        }
        if (!$objData) {
            $this->JsonReturn('必须有修改项！');
        }
        if ($validate && !validate::check($validate, $objData)) {
            $this->JsonReturn(validate::getError());
        }
        //补充数据
        $objData['refresh_time'] = TIME;
//        z($objData);
        //取出所以商品,并对商品做权限判断
        $rs = M('ste_goods')->field('user_id,gid,shop_id')->where(array('gid' => $gids))->findAll(false);
        if (!$rs) {
            $this->JsonReturn('没有找到符合条件的商品');
        }
        $ids = array();
        foreach ($rs as $val) {
            if (!parent::_checkUser($val['user_id'], $val['shop_id'])) {
                $this->JsonReturn('操作错误，没有编辑权限');
            } else {
                $ids[] = $val['gid'];
            }
        }
        if ($ids) {
            $total = M('ste_goods')->update($objData, array('gid' => $ids));
            //===记录操作日志====
            parent::saveSySLog(4, $objData, $ids, array('gid' => $ids), '商品管理-批量设置');
            //===记录操作日志====
            $this->JsonReturn('ok', $total, 1);
        } else {
            $this->JsonReturn('没有找到符合条件的商品');
        }
    }

    /*
     * 根据商品ID生成货号
     */

    private function _getGoodsSn($orderId) {
        return '1' . date('Ymd', TIME) . sprintf("%05d", $orderId); //生成14位数，不足前面补0
    }

    /*
     * 获取销售状态
     */

    private function getStatus($stime, $etime, $status) {
        if (!$status) {
            return '<span class="gray">下架</span>';
        }
        if ($status == 2) {
            return '<span class="gray">回收站</span>';
        }
        if ($stime <= TIME) {
            if ($etime < TIME) {
                return '<span class="gray">过期下架</span>';
            } elseif ($etime >= TIME) {
                return '<span class="green">在售</span>';
            }
        } else {
            return '<span class="red">未开始</span>';
        }
    }

    /*
     * 生成类目树
     */

    private function getCateTree($cateId) {
        static $cateData = null;
        static $_treeList = array();
        if (!isset($_treeList[$cateId])) {
            if (!$cateData) {
                V('tree/catetree');
                $cateData = M('ste_goods_cate')->where(array('city_id' =>
                                    steadmin::$adminInfo['city_id'], 'is_del' => 0))
                                ->order('sort DESC')->select('id');
            }
            $ctree = TreeTool::getParent($cateData, $cateId);
            if ($ctree) {
                $sort = array();
                foreach ($ctree as $key => $val) {
                    if ($val['pid'] >= $this->rootCateId) {
                        $_treeList[$cateId][$key] = $val['name'];
                        $sort[$key] = $val['depth'];
                    }
                }
                if ($sort) {
                    array_multisort($sort, SORT_ASC, $_treeList[$cateId]);
                }
            } else {
                return array();
            }
            if (!isset($_treeList[$cateId])) {
                $_treeList[$cateId] = array();
            }
        }
        return $_treeList[$cateId];
    }

}

//处理url参数
function getSearchUrl($arr = array()) {
    static $_url = array();
    if (!$_url) {
        $url = getUrlStrList(array(), true);
        unset($url['g'], $url['c'], $url['m']);
        $_url = $url;
    }
    $url = $_url;
    if ($arr) {
        foreach ($arr as $key => $val) {
            if (!is_null($val)) {
                $url[$key] = $val;
            } elseif (isset($url[$key])) {
                unset($url[$key]);
            }
        }
    }
    return $url;
}
