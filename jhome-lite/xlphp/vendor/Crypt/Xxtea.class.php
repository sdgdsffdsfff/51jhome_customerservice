<?php

/**
  +------------------------------------------------------------------------------
 * Xxtea 加密实现类
  +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Crypt
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Xxtea.class.php 2504 2011-12-28 07:35:29Z liu21st $
  +------------------------------------------------------------------------------
 */
class MyEncrypt {

    /**
      +----------------------------------------------------------
     * 加密字符串
      +----------------------------------------------------------
     * @access static
      +----------------------------------------------------------
     * @param string $str 字符串
     * @param string $key 加密key
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    public static function encrypt($str, $key) {
        if ($str == '') {
            return '';
        }
        $str = json_encode($str);
        $v = self::str2long($str, true);
        $k = self::str2long($key, false);
        $n = count($v) - 1;

        $z = $v[$n];
        $y = $v[0];
        $delta = 0x9E3779B9;
        $q = floor(6 + 52 / ($n + 1));
        $sum = 0;
        while (0 < $q--) {
            $sum = self::int32($sum + $delta);
            $e = $sum >> 2 & 3;
            for ($p = 0; $p < $n; $p++) {
                $y = $v[$p + 1];
                $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                $z = $v[$p] = self::int32($v[$p] + $mx);
            }
            $y = $v[0];
            $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $z = $v[$n] = self::int32($v[$n] + $mx);
        }
        return rawurlencode(base64_encode(self::long2str($v, false)));
    }

    /**
      +----------------------------------------------------------
     * 解密字符串
      +----------------------------------------------------------
     * @access static
      +----------------------------------------------------------
     * @param string $str 字符串
     * @param string $key 加密key
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    public static function decrypt($str, $key) {
        if ($str == '') {
            return '';
        }
        $str = base64_decode(rawurldecode($str));
        $v = self::str2long($str, false);
        $k = self::str2long($key, false);
        $n = count($v) - 1;
        if ($n==-1){return '';}
        $z = isset($v[$n]) ? $v[$n] : '';
        $y = isset($v[0]) ? $v[0] : '';
        $delta = 0x9E3779B9;
        $q = floor(6 + 52 / ($n + 1));
        $sum = self::int32($q * $delta);
        while ($sum != 0) {
            $e = $sum >> 2 & 3;
            for ($p = $n; $p > 0; $p--) {
                $z = isset($v[$p - 1]) ? $v[$p - 1] : '';
                $v[$p] = isset($v[$p]) ? $v[$p] : '';
                $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                $y = $v[$p] = self::int32($v[$p] - $mx);
            }
            $z = $v[$n];
            $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $y = $v[0] = self::int32($v[0] - $mx);
            $sum = self::int32($sum - $delta);
        }
        return json_decode(self::long2str($v, true), true);
    }

    /**
      +----------------------------------------------------------
     * 解密字符串
     *
      +----------------------------------------------------------
     * @access static
      +----------------------------------------------------------
     * @param string $str 字符串
     * @param string $key 加密key
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected static function long2str($v, $w) {
        $len = count($v);
        $s = array();
        for ($i = 0; $i < $len; $i++) {
            $s[$i] = pack("V", $v[$i]);
        }
        if ($w) {
            return substr(join('', $s), 0, $v[$len - 1]);
        } else {
            return join('', $s);
        }
    }

    protected static function str2long($s, $w) {
        $v = unpack("V*", $s . str_repeat("\0", (4 - strlen($s) % 4) & 3));
        $v = array_values($v);
        if ($w) {
            $v[count($v)] = strlen($s);
        }
        return $v;
    }

    protected static function int32($n) {
        while ($n >= 2147483648)
            $n -= 4294967296;
        while ($n <= -2147483649)
            $n += 4294967296;
        return (int) $n;
    }

}
