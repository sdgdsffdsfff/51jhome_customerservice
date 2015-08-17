<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of action
 * 控制器继承基类
 * @author xlp
 */
class action {

    protected $tVar = array(); //模板输出变量
    public $isSetDefine = false;
    public $returnJson = true;

    function __construct() {
        if (get_magic_quotes_gpc()) {
            $_GET = $this->sec($_GET);
            $_POST = $this->sec($_POST);
            $_COOKIE = $this->sec($_COOKIE);
        }
    }

    /*
     * 为模板注册变量
     * @param $name string or array 需要注册的变量，可以是数组
     * @param $value string 变量值
     * @return 无
     * @example $this->assign('title','xlphp');
     * @example $this->assign(array('title'=>'xlphp','id'=>12));
     */

    function assign($name, $value = '') {
        if (is_array($name)) {
            $this->tVar = array_merge($this->tVar, $name);
        } elseif (is_object($name)) {
            foreach ($name as $key => $val) {
                $this->tVar[$key] = $val;
            }
        } else {
            $this->tVar[$name] = $value;
        }
    }

    /*
     * 对类内不可访问的成员赋值
     * @param $name string 变量名
     * @param $value string or array 变量值
     * @return 将变量插入到模板变量表中
     */
    /*
      function __set($name, $value) {
      $this->assign($name, $value);
      }

      function __get($name) {
      return isset($this->$name)?$this->$name:null;
      }
     */
    /*
     * 输出模板
     * @param $templates string 模板名称
     * @param $path string 模板目录
     * @return 解析后的模板内容
     * @example $this->display('index');
     * @example $this->display('header','public');
     */

    function display($templates = '', $path = '') {
        // 模板阵列变量分解成为独立变量
        if ($this->tVar) {
            extract($this->tVar, EXTR_OVERWRITE);
        }
        /*
         * 声明前台所需常量
         */
        if (!$this->isSetDefine) {
            define('TPL_PATH', APP_PATH . 'tpl/' . C('System', 'skin') . '/');
            define('PUBLIC_PATH', (G('main_path') ? SITE_PATH : WEB_PATH) . 'statics/' . C('System', 'skin') . '/');
            define('IMG_PATH', PUBLIC_PATH . 'images/');
            define('JS_PATH', PUBLIC_PATH . 'js/');
            define('CSS_PATH', PUBLIC_PATH . 'css/');
            define('LOCAL_PUBLIC_PATH', WEB_PATH . 'statics/' . C('System', 'skin') . '/');
            load('view.fun');
            $this->isSetDefine = true;
        }
        if (!$templates) {
            $templates = C('System', 'm');
        }
        if (!$path) {
            $path = C('System', 'c');
        }
        return include getTpl($templates, $path);
    }

    /*
     * 输出模板解析后的数据流，不会直接输出，可用于ajax返回
     * @param $templates string 模板名称
     * @param $path string 模板目录
     * @return 解析后的模板内容
     * @example $this->getFetch('index');
     * @example $this->getFetch('header','public');
     */

    function getFetch($templates = '', $path = '') {
        ob_start();
        $this->display($templates, $path);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /*
     * 输出JSON格式的数据，可用于ajax返回
     * @param $info string 状态信息
     * @param $data string or array 返回的数据容器
     * @param $status int 信息状态标记，一般约定0:失败,1:成功
     * @return 直接输出json字符串并终止运行
     * @example $this->JsonReturn('操作失败');
     * @example $this->JsonReturn('ok',null,1);
     */

    function JsonReturn($info = '', $data = null, $status = 0) {
        if ($this->returnJson) {
            exit(json_encode(array('status' => $status, 'info' => $info, 'data' => $data)));
        } else {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = null;
            return $status ? showInfo($info) : showError($info);
        }
    }

    /*
     * 取得所有模板变量
     * @param 无
     * @return array
     */

    function getAllVar() {
        return $this->tVar;
    }

    /*
     * 取得纯数字类型的GET参数
     * @param $field string 接受字段名
     * @param $val int 默认值
     * @return int
     */

    function _getid($field = '', $val = 0) {
        return $this->_field_id('GET', $field, $val);
    }

    /*
     * 取得纯数字类型的POST参数
     * @param $field string 接受字段名
     * @param $val int 默认值
     * @return int
     */

    function _postid($field = '', $val = 0) {
        return $this->_field_id('POST', $field, $val);
    }

    /*
     * 取得经过安全处理后的的GET参数，支持传递数组
     * @param $field string 接受字段名
     * @param $val string 默认值
     * @param $fun string 自定义处理函数，默认值可以在config_inc.php中设置
     * @return string
     */

    function _get($field = '', $val = '', $fun = '') {
        return $this->_field('GET', $field, $val, $fun);
    }

    /*
     * 取得经过安全处理后的的POST参数，支持传递数组
     * @param $field string 接受字段名
     * @param $val string 默认值
     * @param $fun string 自定义处理函数，默认值可以在config_inc.php中设置
     * @return string
     */

    function _post($field = '', $val = '', $fun = '') {
        return $this->_field('POST', $field, $val, $fun);
    }

    /*
     * 用指定的函数递归处理参数，支持传递数组
     * @param $array array or string 待处理的参数
     * @param $fun string 自定义处理函数
     * @return array or string
     */

    function sec(&$array, $fun = 'stripslashes') {
        if (empty($array)) {
            return '';
        }
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $array[$k] = $this->sec($v, $fun);
            }
        } else {
            $array = trim($fun($array));
        }
        return $array;
    }

//私有方法
    private function _field_id($type = 'GET', $field = '', $val = null) {
        if ($type == 'GET') {
            return isset($_GET[$field]) && $_GET[$field] && checkNum($_GET[$field]) ? $_GET[$field] : $val;
        } elseif (isset($_POST[$field]) && $_POST[$field] && checkNum($_POST[$field])) {
            return $_POST[$field];
        }
        return $val;
    }

    private function _field($type = 'GET', $field = '', $val = null, $fun = '') {
        if (!$field) {
            return null;
        }
        $fun = $fun ? $fun : C('System', 'filter');
        if ($type == 'GET') {
            return isset($_GET[$field]) && $_GET[$field] ? $this->sec($_GET[$field], $fun) : $val;
        } elseif (isset($_POST[$field]) && $_POST[$field]) {
            return $this->sec($_POST[$field], $fun);
        }
        return $val;
    }

}
