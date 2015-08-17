<?php

abstract class AES {

    static public function encode( $str, $key ) {
        /*
         * add PKCS7 padding
         */
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad   = $block - (strlen($str) % $block);
        $str  .= str_repeat(chr($pad), $pad);

        return base64_encode( mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_ECB) );
    }

    static public function decode( $str, $key ) {
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($str), MCRYPT_MODE_ECB);
        /*
         * remove PKCS7 padding
         */
        $dec_s2    = strlen($decrypted);
        $padding   = ord($decrypted[$dec_s2 - 1]);
        $decrypted = substr($decrypted, 0, -$padding);

        return  $decrypted;
    }
}
