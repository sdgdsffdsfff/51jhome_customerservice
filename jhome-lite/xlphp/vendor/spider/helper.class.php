<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of content
 *
 * @author xlp
 */
set_time_limit(0);

class spiderHelper {

    public $strCut = null;
    public $replace = null;
    public $clearJs = true; //是否清除javas代码
    public $clearForm = false; //是否清除表单
    public $clearIframe = false; //是否清除Iframe页面
    public $referer = '';
    public $cookie = '';
    public $cookiejar = '';
    public $post = '';
    public $userAgent = null;

    function __construct() {
        V('spider/phpQuery', false);
    }

    public function _getUrlHtml($url) {
        return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
    }

    public function JsonReturn($info, $data = null, $status = 0) {
        echo json_encode(array('status' => $status, 'info' => $info, 'data' => $data));
        exit;
    }

    public function _getData($url, $changeCode = true, $clearCode = true) {
        $getcontent = $this->getcontnet($url);
        $content = '';
        if ($changeCode) {
            $getcontent = autoCharset($getcontent, 'gbk', 'utf-8');
        }
        if ($this->strCut) {
            $getcontent = $this->strCut($getcontent, $this->strCut[0], $this->strCut[1]);
        }
        if ($clearCode) {
            $content = clearStrSpace($this->clear($getcontent));
        }
        if ($content) {//避免因清理造成内容丢失
            $getcontent = $content;
            unset($content);
        }
        $ostr = array('charset=GBK', 'charset=gb2312','&');
        $rstr = array('charset=utf-8', 'charset=utf-8','&amp;');
        if ($this->replace) {
            foreach ($this->replace as $key => $val) {
                $ostr[] = $key;
                $rstr[] = $val;
            }
        }
        $getcontent = str_replace($ostr, $rstr, $getcontent);
//        echo $getcontent;exit;
        $this->_setDocument($getcontent);
        return $getcontent;
    }

    public function _setDocument($getcontent) {
        phpQuery::$documents = array();
        phpQuery::newDocument($getcontent);
        phpQuery::$defaultCharset = 'UTF-8';
    }

    /**
     * 获取远程数据
     * @access public
     * @param  $geturl string 远程请求URL
     * @return  String
     */
    public function getcontnet($url = '', $referer = '', $post = '', $cookie = '', $cookiejar = '') {
        if (empty($url)) {
            showError('缺少URL信息');
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        if (!$this->userAgent) {
            $this->userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:8.0) Gecko/20100101 Firefox/8.0';
        } else {
            $this->userAgent = 'Baiduspider+(+http://www.baidu.com/search/spider.htm)';
        }
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        $post && $this->post = $post;
        if (!$this->post) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:application/x-www-form-urlencoded"));
            curl_setopt($curl, CURLOPT_ENCODING, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //信息以文件流的形式返回，而不是直接输出。
        $referer && $this->referer = $referer;
        if ($this->referer) {
            curl_setopt($curl, CURLOPT_REFERER, $this->referer);
        } else {
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        }
        if ($this->post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->post, '', '&'));
        } else {
            curl_setopt($curl, CURLOPT_POST, 0);
        }
        $cookie && $this->cookie = $cookie;
        if ($this->cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $this->cookie);
        }
        $cookiejar && $this->cookiejar = $cookiejar;
        if ($this->cookiejar) {
            $cookiepath = getcwd() . './' . $this->cookiejar;
            curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiepath);
            curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath);
        }
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $content = curl_exec($curl);
        if (curl_errno($curl)) {
            $content = '';
            if (isset($_GET['debug'])) {
                echo '<pre><b>错误:</b><br />' . curl_error($curl);
            }
        }
        curl_close($curl);
        // }
        return $content;
    }

    function clear($content) {
        //清除一些不必要的元素.
        $rep = array('/<style.*style>/isU', '/<!--.*-->/isU', '/<noscript.*noscript>/isU', '/<iframe.*iframe>/isU', '/<link.*>/isU', '/<embed.*embed>/isU', '/<marquee.*marquee>/isU');
        if ($this->clearJs) {
            $rep[] = '/<script.*script>/isU';
        }
        if ($this->clearForm) {
            $rep[] = '/<form.*form>/isU';
        }
        if ($this->clearIframe) {
            $rep[] = '/<iframe.*iframe>/isU';
        }
        $content = preg_replace($rep, '', $content);
        return str_replace(array('content="text/html; charset=gbk"', '<meta charset="gbk">', 'content="text/html; charset=GBK"', 'charset=GBK', '<meta charset="gbk" />'), '', $content);
    }

//分割内容
    function strCut($str, $start, $end) {//取出第一个匹配,先分割再替换
        $str = strstr($str, $start);
        return substr($str, strlen($start), strpos($str, $end) - strlen($start));
    }

}

?>
