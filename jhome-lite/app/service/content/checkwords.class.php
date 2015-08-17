<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of checkwords
 * 敏感词过滤
 * @author xlp
 */
/*
  用法：
  T('content/checkwords');
  $content=checkwords::check($content,true);
 */
class checkwords {

    static $result = array();

    /**
     * 检测敏感词
     * @param string $content 需要检测的文本
     * @param bool $replace 是否需要替换内容
     * @return bool
     */
    static function check(&$content, $replace = true) {
        $words = C('checkwords'); //导入敏感词库
        if (!$replace && !empty($words) && !empty($words['banned']) && is_array($words['banned'])) {
            foreach ($words['banned'] as $bannedWords) {
                if (preg_match_all($bannedWords, $content, $matches)) {
                    self::$result = array(
                        'word' => array_unique($matches[0]),
                        'content' => self::highlight($content, $bannedWords)
                    );
                    return false;
                }
            }
        } elseif ($replace && !empty($words) && !empty($words['filter'])) {
            $i = 0;
            $limitNum = 1000;
            while ($findWords = array_slice($words['filter']['find'], $i, $limitNum)) {
                if (empty($findWords)) {
                    break;
                }
                $replaceWords = array_slice($words['filter']['replace'], $i, $limitNum);
                $i += $limitNum;
                $content = preg_replace($findWords, $replaceWords, $content);
            }
        }
        return $content;
    }

//高亮显示敏感词内容
    static public function highlight($content, $wordsRegex) {
        return preg_replace($wordsRegex, '<span style="color:#f00;">\\1</span>', $content);
    }

}
