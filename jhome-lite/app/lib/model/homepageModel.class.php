<?php

defined('IN_XLP') or exit('Access Denied');

class homepageModel extends model {

    private $city_id = 3301;

    function __construct() {
        parent::__construct();
        $this->config = C('app/index_' . $this->city_id);
        $this->cate = $this->config['cate'];
        $this->device = $this->config['device'];
        $this->template = $this->config['template'];
        $this->moduletips = $this->config['moduletips'];
        $this->module = $this->config['module'];
        $imagescale = array(2 => 1.42, 3 => 1.77, 4 => 1.94);
        //$this->assign(array('imagescale' => $imagescale));
        $this->service = F('steward/service_city_' . $this->city_id);
        if (!$this->service) {
            $this->service = M('ste_service')
                    ->field('`sid`,`stitle`')
                    ->where(array('city_id' => $this->city_id))
                    ->findAll(false);
        }
        $this->cateList = $this->goodsCateCache($this->city_id);
        V('db/mongo');
        $this->mongo = mongoApi::getInstance();
    }

    //根据条件
    public function _getHomePage($cate = 1, $uagent = 1, $service = 1, $time = TIME) {
        $where = array('cate' => $cate, 'status' => 1,
            '$or' => array(array("starttime" => array('$lt' => (int) $time), "endtime" => array('$gt' => (int) $time)), array("endtime" => 0)));
        $result = $this->mongo->table('homepage')->findAll($where, array("sort" . $uagent => 1, "createtime" => 1));
        $result = array_values($result);
        $rs = array();

        foreach ($result as $k => $v) {
            if (!in_array($service, $v['service']) && $v['type'] == 'none') {
                //break;
                continue;
                unset($result[$k]);
            }
            if (!in_array($uagent, $v['uagent'])) {
                //break;
                continue;
                unset($result[$k]);
            }
            $idarr = (array) $v['_id'];
            switch ($v['template']) {
                case '1':
                    $rs[] = array('template' => $v['template'],
                        'sort' . $uagent => $v['sort' . $uagent],
                        'title' => $v['content']['title'],
                        'color' => $v['content']['color'],
                        '_id' => $idarr['$id']);
                    break;
                case '2':
                    $v = $this->_resetDetail($v, $service);
                    $rs[] = array('template' => $v['template'],
                        'sort' . $uagent => $v['sort' . $uagent],
                        'mode' => $v['content']['mode'],
                        'para' => $v['content']['para'],
                        'image' => getImgUrl($v['content']['image']),
                        'goods_desc' => $v['content']['goods_desc'],
                        'goods_spec' => $v['content']['goods_spec'],
                        'goods_name' => $v['content']['goods_name'],
                        'price' => $v['content']['price'],
                        'original_price' => $v['content']['original_price'],
                        'sale_counts' => $v['content']['sale_counts'],
                        'goodinfo' => $v['content']['goodinfo'],
                        '_id' => $idarr['$id']);
                    break;
                case '3':
                    $v = $this->_resetDetail($v, $service);
                    $rs[] = array('template' => $v['template'],
                        'sort' . $uagent => $v['sort' . $uagent],
                        'mode' => $v['content']['mode'],
                        'para' => $v['content']['para'],
                        'image' => getImgUrl($v['content']['image']),
                        'title' => $v['content']['title'],
                        'subtitle' => $v['content']['subtitle'],
                        '_id' => $idarr['$id']);
                    break;
                case '4':
                    foreach ($v['content'] as $kk => $vv) {
                        $v['content'][$kk] = $this->_resetDetail($vv, $service);
                        $v['content'][$kk]['image'] = getImgUrl($vv['image']);
                        //z($v['content'][$kk]);
                    }
                    $rs[] = array('template' => $v['template'],
                        'sort' . $uagent => $v['sort' . $uagent],
                        'images' => $v['content'],
                        '_id' => $idarr['$id']);
                    break;
            }
        }
        return $rs;
    }

    //将套餐转换成各服务社对应的商品,拼合参数
    private function _resetDetail($v, $service) {
        //z($v, false);
        static $package = array();
        if (isHave($v['template'])) {
            switch ($v['template']) {
                case '2':
                    if (!isHave($package[$v['content']['pid']])) {
                        $rs = M('ste_goods_inter')->field('serverinfo')->where(array('id' => $v['content']['pid']))->find();
                        $serviceinfo = json_decode($rs['serverinfo'], true);
                        $package[$v['content']['pid']] = $serviceinfo;
                    } else {
                        $serviceinfo = $package[$v['content']['pid']];
                    }
                    foreach ($serviceinfo as $sk => $sv) {
                        if ($sk == $service) {
                            $v['content']['mode'] = 'steward_good';
                            $v['content']['para'] = 'id=' . $sv;

                            $goodInfo = M('ste_goods')
                                            ->field('gid,cate_id,shop_id,goods_name,goods_subtitle,goods_spec,goods_pic,'
                                                    . 'original_price,price_pre,price,credits,sale_counts,storage_counts,is_hot,is_new,is_recommend,'
                                                    . 'limit_counts,is_realtime,goods_desc,is_limited')
                                            ->where(array('gid' => $sv))->find();
                            $v['content']['goodinfo'] = $goodInfo;
                        }
                    }
                    break;
                case '3':

                    switch ($v['content']['mode']) {
                        //套餐商品转变
                        case 'package_good':
                            if (!isHave($package[$v['content']['para']])) {
                                $rs = M('ste_goods_inter')->field('serverinfo')->where(array('id' => $v['content']['para']))->find();
                                $serviceinfo = json_decode($rs['serverinfo'], true);
                                $package[$v['content']['para']] = $serviceinfo;
                            } else {
                                $serviceinfo = $package[$v['content']['para']];
                            }
                            foreach ($serviceinfo as $sk => $sv) {
                                if ($sk == $service) {
                                    $v['content']['mode'] = 'steward_good';
                                    $v['content']['para'] = 'id=' . $sv;
                                }
                            }
                            break;
                        //论坛转变    
                        case 'forum_thread':
                            $v['content']['para'] = 'fid=' . $v['content']['para'];
                            break;
                        //雷锋转变
                        case 'event':
                            $v['content']['para'] = 'id=' . $v['content']['para'];
                            break;
                        //商品分类转变，只有一家店铺时自动转化为店铺跳转。
                        case 'steward_cate':
                            $tid = $v['content']['para'];
                            $v['content']['para'] = 'tid=' . $tid;
                            //查询是否一家店铺
                            $cates = $this->getSubs($this->cateList, $tid);
                            if (isHave($cates['list'][$tid])) {
                                $shop = array();
                                $shop = M('ste_goods')->field('shop_id')->where(
                                                        array('service_id' => $service, 'status' => 1, 'cate_id' => $cates['list'][$tid]))
                                                ->group('shop_id')->select('shop_id');
                                $shop = array_values($shop);
                                //类目下只有一个商家时直接跳转到商品列表
                                if (count($shop) == 1) {
                                    $v['content']['mode'] = 'steward_shop';
                                    $v['content']['para'] = 'shop_id=' . $shop[0]['shop_id'] . '&tid=' . $tid;
                                    if (isHave($v['content']['view']) && $v['content']['view'] == 'image') {
                                        //echo $k.'--'.$tid.'--'.count($shop).'<br/>';
                                        $v['content']['para'] = $v['content']['para'] . '&view=image';
                                    }
                                }
                            }

                            break;
                    }



                    break;
            }
        } else {
            switch ($v['mode']) {
                //套餐商品转变
                case 'package_good':
                    if (!isHave($package[$v['para']])) {
                        $rs = M('ste_goods_inter')->field('serverinfo')->where(array('id' => $v['para']))->find();
                        $serviceinfo = json_decode($rs['serverinfo'], true);
                        $package[$v['para']] = $serviceinfo;
                    } else {
                        $serviceinfo = $package[$v['para']];
                    }
                    foreach ($serviceinfo as $sk => $sv) {
                        if ($sk == $service) {
                            $gid = $sv;
                            $v['mode'] = 'steward_good';
                            $v['para'] = 'id=' . $gid;
                        }
                    }
                    unset($v['template']);
                    break;
                //论坛转变
                case 'forum_thread':
                    $v['para'] = 'fid=' . $v['para'];
                    unset($v['template']);
                    break;
                //雷锋转变
                case 'event':
                    $v['para'] = 'id=' . $v['para'];
                    unset($v['template']);
                    break;
                //商品分类转变，只有一家店铺时自动转化为店铺跳转。
                case 'steward_cate':
                    $tid = $v['para'];
                    $v['para'] = 'tid=' . $tid;
                    unset($v['template']);
                    //查询是否一家店铺
                    $cates = $this->getSubs($this->cateList, $tid);
                    if (isHave($cates['list'][$tid])) {
                        $shop = array();
                        $shop = M('ste_goods')->field('shop_id')->where(
                                                array('service_id' => $service, 'status' => 1, 'cate_id' => $cates['list'][$tid]))
                                        ->group('shop_id')->select('shop_id');
                        $shop = array_values($shop);
                        //类目下只有一个商家时直接跳转到商品列表
                        if (count($shop) == 1) {
                            $v['content']['mode'] = 'steward_shop';
                            $v['content']['para'] = 'shop_id=' . $shop[0]['shop_id'] . '&tid=' . $tid;
                            if (isHave($v['content']['view']) && $v['content']['view'] == 'image') {
                                //echo $k.'--'.$tid.'--'.count($shop).'<br/>';
                                $v['content']['para'] = $v['content']['para'] . '&view=image';
                            }
                        }
                    }
                    break;
            }
        }
        return $v;
    }

    /*
     * 从类目树中获取全部子类
     */

    protected function getSubs($categorys, $catId = 0, $hasSelf = true, $level = 1, $f = 0) {
        static $_id = array();
        $asd = $catId;
        if ($f) {
            $asd = $f;
        }
        $subs = array();
        if ($categorys) {
            foreach ($categorys as $item) {
                if ($hasSelf && $item['id'] == $catId) {
                    $item['level'] = $level;
                    $subs[] = $item;
                    $_id[$asd][] = $item['id'];
                }
                if ($item['pid'] == $catId) {
                    $item['level'] = $level + 1;
                    $subs[] = $item;
                    $_id[$asd][] = $item['id'];
                    $arr = $this->getSubs($categorys, $item['id'], false, $level + 2, $catId);
                    $subs = array_merge($subs, $arr['items']);
                }
            }
        }
        return array('list' => $_id, 'items' => $subs);
    }

    /*
     * 获取商品分类
     */

    protected function goodsCateCache($city_id) {
        $cate = F('steward/goodscate_city_' . $city_id);
        if (!$cate) {
            $cate = M('ste_goods_cate')->field('id,name,sort,pid,path,depth')->where(array('city_id' => $city_id, 'is_show' => 1, 'is_del' => 0))->order('sort DESC')->select('id');
            F('steward/goodscate_city_' . $city_id, $cate);
        }
        return $cate;
    }

    /*
     * 提交上线
     */

    public function publish($cate=1,$device=1,$service=1,$time = TIME) {
        $rs=D('homepage')->_getHomePage($cate, (int) $device, (int) $service, (int) $time);
        S('homepage/'.$cate.'/'.$service.'/'.$device, $rs, strtotime(date('Y-m-d',$time))+87400-$time);
        return true; 
    }

    /*
     * 添加修改
     */

    public function save() {
        $cate = F('steward/goodscate_city_' . $city_id);
        if (!$cate) {
            $cate = M('ste_goods_cate')->field('id,name,sort,pid,path,depth')->where(array('city_id' => $city_id, 'is_show' => 1, 'is_del' => 0))->order('sort DESC')->select('id');
            F('steward/goodscate_city_' . $city_id, $cate);
        }
        return $cate;
    }

    /*
     * 删除
     */

    public function del($id) {
        $rs = $this->mongo->table('homepage')->update(array('$set' => array('status' => 2)), array("_id" => $this->mongo->getId($id)));
        $loginfo['log_id'] = $id;
        $loginfo['data'] = '删除';
        $loginfo['operator'] = admin::$adminInfo['username'];
        $loginfo['infotime'] = TIME;
        $rs = $this->mongo->table('homepage_log')->insert($loginfo);
        return true;
    }

}
