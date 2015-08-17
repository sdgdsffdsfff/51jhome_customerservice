<?php
abstract class DES {

	static public function encode( $encrypt, $key ){
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);
		$passcrypt = mcrypt_encrypt(MCRYPT_DES ,$key, $encrypt, MCRYPT_MODE_ECB, $iv);
		$encode = base64_encode($passcrypt);

		return $encode;
	}

	static public function decode( $decrypt, $key ){  
	   $decoded = base64_decode($decrypt);
	   $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);
	   $decrypted = mcrypt_decrypt(MCRYPT_DES ,$key, $decoded, MCRYPT_MODE_ECB, $iv);

	   return $decrypted;
	}

}