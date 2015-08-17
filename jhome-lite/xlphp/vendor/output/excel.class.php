<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of excelApi
 * 使用方法：
  $xls = new Excel_Xml('UTF-8',false,'测试');
  $data = array(
  array('名称','地址'),
  array('百度','www.baidu.com')
  );
  $xls->write('导出文件名',$data);
 * @author xlp
 */
class excelApi {

    private $header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
    private $footer = "</Workbook>";
    private $lines = array();
    private $sEncoding;
    private $bConvertTypes;
    private $sWorksheetTitle;

    public function __construct($sEncoding = 'UTF-8', $bConvertTypes = false) {
        $this->bConvertTypes = $bConvertTypes;
        $this->setEncoding($sEncoding);
    }

    public function setEncoding($sEncoding = 'UTF-8') {
        $this->sEncoding = $sEncoding;
    }

    public function write($filename = '', $data = array(), $sWorksheetTitle = 'Table1', $in = 'UTF8', $out = 'gbk') {
        if (!$filename || !$data) {
            $this->halt('导出文件名和数据不能为空');
        }
        $this->setWorksheetTitle($sWorksheetTitle, $in, $out);
        foreach ($data as $v) {
            $this->addRow($v);
        }
        $this->generateXML($filename, $in, $out);
    }

    private function setWorksheetTitle($title, $in, $out) {
        $this->sWorksheetTitle = substr(preg_replace("/[\\\|:|\/|\?|\*|\[|\]]/", "", autoCharset($title, $in, $out)), 0, 31);
    }

    private function generateXML($filename = 'excel-report', $in = 'UTF8', $out = 'gbk') {
        header("Content-Type: application/vnd.ms-excel; charset=" . $this->sEncoding);
        header("Content-Disposition: inline; filename=\"" . autoCharset($filename, $in, $out) . ".xls\"");
        echo stripslashes(sprintf($this->header, $this->sEncoding)),
        "\n<Worksheet ss:Name=\"" . $this->sWorksheetTitle . "\">\n<Table>\n",
        implode('', $this->lines),
        "</Table>\n</Worksheet>\n",
        $this->footer;
    }

    private function addRow($array) {
        $cells = '';
        foreach ($array as $v) {
            $type = 'String';
            if ($this->bConvertTypes === true && is_numeric($v)) {
                $type = 'Number';
            }
            $v = htmlentities($v, ENT_COMPAT, $this->sEncoding);
            $cells .= "<Cell><Data ss:Type=\"$type\">" . $v . "</Data></Cell>\n";
        }
        $this->lines[] = "<Row>\n" . $cells . "</Row>\n";
    }

    private function halt($msg) {
        showError($msg);
    }

}
