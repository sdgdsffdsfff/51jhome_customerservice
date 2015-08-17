<?php
/**
 * cssmin.php - A simple CSS minifier.
 * <code>
 * include("cssmin.class.php");
 * file_put_contents("path/to/target.css", CSSMin::minify(file_get_contents("path/to/source.css")));
 * </code>
 * @package 	cssmin
 * @author 		Joe Scylla <joe.scylla@gmail.com>
 * @copyright 	2008 Joe Scylla <joe.scylla@gmail.com>
 * @license 	http://opensource.org/licenses/mit-license.php MIT License
 * @version 	1.0 (2008-01-31)
 */
class CSSMin
	{
	/**
	 * Minifies stylesheet definitions
	 *
	 * @param 	string	$v	Stylesheet definitions as string
	 * @return 	string		Minified stylesheet definitions
	 */
	public static function minify($v) 
		{
		$v = trim($v);
		$v = str_replace("\r\n", "\n", $v);
        $search = array("/\/\*[\d\D]*?\*\/|\t+/", "/\s+/", "/\}\s+/");
        $replace = array(null, " ", "}\n");
		$v = preg_replace($search, $replace, $v);
		$search = array("/\\;\s/", "/\s+\{\\s+/", "/\\:\s+\\#/", "/,\s+/i", "/\\:\s+\\\'/i", "/\\:\s+([0-9]+|[A-F]+)/i");
        $replace = array(";", "{", ":#", ",", ":\'", ":$1");
        $v = preg_replace($search, $replace, $v);
        $v = str_replace("\n", null, $v);
    	return $v;	
  		}
	}
?>