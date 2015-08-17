<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}
/*
  $data = array(
  'title'        => $this->_post('title'),
  'content'      => $this->_post('content'),
  'cid'          => $this->_post('cid'),
  );

  T('content/validate');
  $validate = array(
  array('title','required','标题不能为空'),
  array('content','max_length','内容长度不能大于1000',1000),
  array('cid','min','cid最小值为1',1),
  );

  if(!validate::check($validate,$data)){
  showError($vservice->getError());
  }
 */

/**
 * Description of validate
 * 数据验证类
 * @author xlp
 */
class validate {

    static public $error = null;

    /**
     * 验证数据 
     * @param array $validate 验证设置
     * @param array $data 数据
     * @return bool
     */
    static public function check($validate, $data) {
        $is_check = true;
        foreach ($validate as $val) {
            $key = $val[0];
            switch ($val[1]) {
                case 'required'://必填项
                    $is_check = self::regex($data[$key], 'required');
                    break;
                case 'email'://邮箱
                    $is_check = self::regex($data[$key], 'email');
                    break;
                case 'url'://URL链接
                    $is_check = self::regex($data[$key], 'url');
                    break;
                case 'date'://日期 2014-07-12 08:08:08
                    $is_check = self::regex($data[$key], 'date');
                    break;
                case 'currency'://价格，金额
                    $is_check = self::regex($data[$key], 'currency');
                    break;
                case 'qq': // 验证QQ是否合法
                    $is_check = self::qq($data[$key]);
                    break;
                case 'int'://正整数
                    $is_check = self::regex($data[$key], 'digits');
                    break;
                case 'number'://数字，包含正负、逗号分隔等
                    $is_check = self::regex($data[$key], 'number');
                    break;
                case 'zip'://邮编
                    $is_check = self::regex($data[$key], 'zip');
                    break;
                case 'integer'://整数（含正负）
                    $is_check = self::regex($data[$key], 'integer');
                    break;
                case 'double'://双精度
                    $is_check = self::regex($data[$key], 'double');
                    break;
                case 'english'://纯字母组合
                    $is_check = self::regex($data[$key], 'english');
                    break;
                case 'chinese'://纯中文
                    $is_check = self::regex($data[$key], 'chinese');
                    break;
                case 'phone'://手机号码
                    $is_check = self::regex($data[$key], 'phone');
                    break;
                case 'username'://姓名昵称合法性检查，只能输入中文英文数字
                    $is_check = self::regex($data[$key], 'username');
                    break;
                case 'tel'://座机号码，可包含区号
                    $is_check = self::regex($data[$key], 'tel');
                    break;
                case 'idcard'://中国身份证
                    $is_check = self::regex($data[$key], 'idcard');
                    break;
                case 'issafehtml'://是否为安全的html内容
                    $is_check = self::isSafeStr($data[$key]);
                    break;
                case 'min_length': // 验证字符串最小长度
                    $is_check = self::minLength($data[$key], $val[3]);
                    break;
                case 'max_length': // 验证字符串最大长度
                    $is_check = self::maxLength($data[$key], $val[3]);
                    break;
                case 'password'://检查密码最小长度和内容
                    $is_check = self::isPassword($data[$key], $val[3]);
                    break;
                case 'range_length': // 验证字符是否在某个长度范围内
                    $is_check = self::rangeLength($data[$key], $val[3], $val[4]);
                    break;
                case 'min': // 验证数字最小值
                    $is_check = self::min($data[$key], $val[3]);
                    break;
                case 'max': // 验证数字最大值
                    $is_check = self::max($data[$key], $val[3]);
                    break;
                case 'range': // 验证数字是否在某个大小范围内
                    $is_check = self::range($data[$key], $val[3], $val[4]);
                    break;
                case 'confirm': // 验证两个字段是否相同
                    $is_check = $data[$key] == $data[$val[3]];
                    break;
                case 'in': // 验证是否在某个数组范围之内
                    $is_check = in_array($data[$key], $val[3]);
                    break;
                case 'equal': // 验证是否等于某个值
                    $is_check = self::equal($data[$key], $val[3]);
                    break;
                case 'regex':
                default:    // 默认使用正则验证 可以使用验证类中定义的验证名称
                    // 检查附加规则
                    $is_check = self::regex($data[$key], $val[1]);
                    break;
            }

            if (!$is_check) {
                self::$error = $val[2];
                break;
            }
        }

        return $is_check;
    }

    /**
     * 获取错误信息 
     * @return string
     */
    static public function getError() {
        return self::$error;
    }

    static private function equal($value, $value1) {
        return $value == $value1;
    }

    static private function minLength($value, $length) {
        return $length <= getStrLen($value);
    }

    static private function maxLength($value, $length) {
        return $length >= getStrLen($value);
    }

    static private function rangeLength($value, $min_length, $max_length) {
        return $min_length <= getStrLen($value) && $max_length >= getStrLen($value);
    }

    static private function min($value, $num) {
        return $num <= $value;
    }

    static private function max($value, $num) {
        return $num >= $value;
    }

    static private function range($value, $min_num, $max_num) {
        return $min_num <= $value && $max_num >= $value;
    }

    static private function isPassword($value, $num) {
        return self::minLength($value, $num) && self::regex($value, 'password');
    }

    static private function qq($value) {
        return self::regex($value, 'digits') && self::minLength($value, 4);
    }

    static private function isSafeStr($value) {
        $jsEvent = 'onmousedown|onmousemove|onmmouseup|onmouseover|onmouseout|onload|onunload|onfocus|onblur|onchange|onsubmit|ondblclick|onclick|onkeydown|onkeyup|onkeypress|onmouseenter|onmouseleave';
        return (!preg_match('/<[ \t\n]*script/ui', $value) && !preg_match('/<.*(' . $jsEvent . ')[ \t\n]*=/ui', $value) && !preg_match('/.*script\:/ui', $value));
    }

    static private function regex($value, $rule) {
        $rule = strtolower($rule);
        $validate = array(
            'required' => '/.+/',
            'email' => '/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/i',
            'url' => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/i',
            'date' => '/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}(?:|\s\d{1,2}:\d{1,2}(?:|:\d{1,2}))$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'digits' => '/^\d+$/',
            'number' => '/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/',
            'zip' => '/^[1-9]\d{5}$/',
            'integer' => '/^[-\+]?\d+$/',
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'english' => '/^[A-Za-z]+$/',
            'chinese' => '/^[\x{4e00}-\x{9fa5}]+$/u',
            'phone' => '/^1[34578][0-9]{9}$/',
            'username' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]{1,30}$/u',
            'tel' => '/(1[35]\d{9}$|^0?((10)|(2\d{1})|([3-9]\d{2}))-)?[1-9]\d{6,7}(-\d{3,4})?$/',
            'idcard' => '/^([0-9]{15}|[0-9]{17}[0-9a-z])$/i',
            'password' => '/^[.a-z_0-9-!@#$%\^&*()]$/ui',
        );
        // 检查是否有内置的正则表达式
        if (isset($validate[$rule])) {
            $rule = $validate[$rule];
        }
        return preg_match($rule, $value) === 1;
    }

}
