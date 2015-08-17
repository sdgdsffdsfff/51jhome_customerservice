<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

function getFaceContent($content){
    static $faceExpress = null;
    //表情转换
    if (!$faceExpress) {
        $faceExpress = F('weixin/wxface');
    }
    if (!$faceExpress){
        $faceList=array();
        $faceConfig=C('face');
        foreach($faceConfig as $key=>$val){
            $faceList[0][]=$val['code'];
            $faceList[1][]='<img src="' . SITE_PATH . 'statics/default/images/content/face/'.$key.'.png" width="24" height="24" rel="'.$val['code'].'" title="'.$val['name'].'"/>';
        }
        F('weixin/wxface',$faceList);
    }
    return str_replace($faceExpress[0], $faceExpress[1], $content);
}