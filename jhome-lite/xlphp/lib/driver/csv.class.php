<?php

if (!defined('IN_XLP')) {
    exit('Access Denied');
}

/**
 * Description of csv
 *
 * @author xlp
 * load('csv');
  $myCsv = new csv();
  $myCsv->read('data/user.csv');
  $myCsv->write(array('UID', '姓名', '手机号', '中奖码', '类型', '时间', '状态'), $list, "prize_" . date('y-m-d'));
 */
class csv {

    public $filename;
    public $datafile;
    public $Row;
    public $max;
    public $delimiter;
    public $fieldname;
    public $mode;
    public $nl = PHP_EOL;

    function __construct($delimiter = ',', $mode = 'w') {
        if (!$delimiter) {
            $delimiter = ',';
        }
        $this->delimiter = $delimiter;
        $this->mode = $mode;
    }

    /*
     * 读取CSV文件
     * @param $filePath string 数据文件路径
     * @param $in string 导入数据的编码
     * @param $out string 导出数据的编码
     */

    public function read($filePath = '', $in = 'gbk', $out = 'utf-8') {
        if (!$filePath || !is_file($filePath)) {
            $this->halt('读取文件不能为空');
        }
        $handle = fopen($filePath, 'r');
        $result = $this->input_csv($handle, $in, $out); //解析csv 
        fclose($handle); //关闭指针
        return $result;
    }

    /*
     * 输出CSV文件
     * @param $title array 输出展示的标题列
     * @param $data array 输出数据
     * @param $name string 输出文件名
     * @param $in string 导入数据的编码
     * @param $out string 导出数据的编码
     */

    public function write($title, $data, $name, $in = 'utf-8', $out = 'gbk') {
        header("Content-type: application/RFC822");
        header('Content-Disposition: attachment; filename=' . autoCharset($name, $in, $out) . '.csv');
        echo $this->printline(autoCharset($title, $in, $out));
        echo $this->printcsv($data, $in, $out);
    }

    private function printcell($str) {
        $v = $str;
        if (strpos($str, "\"") || strpos($str, $this->delimiter) || strpos($str, $this->nl)) {
            $v = str_replace("\"", "\"\"", $str);
        }
        $v = '"' . $v . '"';
        return $v;
    }

    private function printline($line) {
        $l = array();
        if (!is_array($line)) {
            $this->halt('Not a array! (printline)');
        }
        $line_head = array_slice($line, 0, -1);
        $line_tail = array_slice($line, -1, 1);
        foreach ($line_head as $cell) {
            $l[] = $this->printcell($cell) . $this->delimiter;
        }
        foreach ($line_tail as $cell) {
            $l[] = $this->printcell($cell) . $this->nl;
        }
        return implode('', $l);
    }

    private function printcsv($lines, $in, $out) {
        $contents = array();
        if (!is_array($lines)) {
            $this->halt('Not a array! (printcsv)');
        }
        foreach ($lines as $line) {
            $contents[] = $this->printline(autoCharset($line, $in, $out));
        }
        return implode('', $contents);
    }

    private function input_csv($handle, $in, $out) {
        $outData = array();
        $n = 0;
        while ($data = fgetcsv($handle, 10000)) {
            $num = count($data);
            for ($i = 0; $i < $num; $i++) {
                $outData[$n][$i] = autoCharset($data[$i], $in, $out);
            }
            $n++;
        }
        return $outData;
    }

    private function halt($msg) {
        showError($msg);
    }

}
