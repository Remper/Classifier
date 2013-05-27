<?php

namespace Tokenizer;

/**
 * Набор функций для классификации символов/строк
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Classificator {
	public static function char_class($char) {
	    $ret = 
	        self::is_cyr($char)           ? '0001' :
	        (self::is_space($char)        ? '0010' :
	        (self::is_dot($char)          ? '0011' :
	        (self::is_pmark($char)        ? '0100' :
	        (self::is_hyphen($char)       ? '0101' :
	        (self::is_number($char)       ? '0110' :
	        (self::is_latin($char)        ? '0111' :
	        (self::is_bracket1($char)     ? '1000' :
	        (self::is_bracket2($char)     ? '1001' :
	        (self::is_single_quote($char) ? '1010' :
	        (self::is_slash($char)        ? '1011' :
	        (self::is_colon($char)        ? '1100' : '0000')))))))))));
	    return str_split($ret);
	}
	
	public static function is_space($char) {
	    return preg_match('/^\s$/u', $char);
	}
	
	public static function is_hyphen($char) {
	    return (int)($char == '-');
	}
	
	public static function is_slash($char) {
	    return (int)($char == '/');
	}
	
	public static function is_dot($char) {
	    return (int)($char == '.');
	}
	
	public static function is_colon($char) {
	    return (int)($char == ':');
	}
	
	public static function is_single_quote($char) {
	    return (int)($char == "'");
	}
	
	public static function is_same_pm($char1, $char2) {
	    return (int)($char1===$char2);
	}
	
	public static function is_cyr($char) {
	    $re_cyr = '/\p{Cyrillic}/u';
	    return preg_match($re_cyr, $char);
	}
	
	public static function is_latin($char) {
	    $re_lat = '/\p{Latin}/u';
	    return preg_match($re_lat, $char);
	}
	
	public static function is_number($char) {
	    return (int)is_numeric($char);
	}
	
	public static function is_pmark($char) {
	    $re_punctuation = '/[,!\?;"\xAB\xBB]/u';
	    return preg_match($re_punctuation, $char);
	}
	
	public static function is_bracket1($char) {
	    $re_bracket = '/[\(\[\{\<]/u';
	    return preg_match($re_bracket, $char);
	}
	
	public static function is_bracket2($char) {
	    $re_bracket = '/[\)\]\}\>]/u';
	    return preg_match($re_bracket, $char);
	}
	
	public static function is_dict_chain($chain) {
	    if (!$chain) return 0;
	    return (int)(Token::isValidToken(mb_strtolower($chain, 'UTF-8')) > 0);
	}
	
	public static function is_suffix($s) {
	    return (int)in_array($s, array('то', 'таки', 'с', 'ка', 'де'));
	}
	
	public static function looks_like_url($s, $suffix) {
	    if (!$suffix || substr($s, 0, 1) === '.' || mb_strlen($s) < 5)
	        return 0;
	    $re1 = '/^\W*https?\:\/\/?/u';
	    $re2 = '/^\W*www\./u';
	    $re3 = '/.\.(?:[a-z]{2,3}|ру|рф)\W*$/iu';
	    if (preg_match($re1, $s) || preg_match($re2, $s) || preg_match($re3, $s)) {
	        return 1;
	    }
	    return 0;
	}
	
	public static function looks_like_time($left, $right) {
	    $left = preg_replace('/^[^0-9]+/u', '', $left);
	    $right = preg_replace('/[^0-9]+$/u', '', $right);
	
	    if (!preg_match('/^[0-9][0-9]?$/u', $left) || !preg_match('/^[0-9][0-9]$/u', $right))
	        return 0;
	
	    if ($left < 24 && $right < 60)
	        return 1;
	
	    return 0;
	}
	
	public static function is_exception($s, $exc) {
	    $s = mb_strtolower($s);
	    if (in_array($s, $exc))
	        return 1;
	    if (!preg_match('/^\W|\W$/u', $s))
	        return 0;
	    $s = preg_replace('/^[^A-Za-zА-ЯЁа-яё0-9]+/u', '', $s);
	    if (in_array($s, $exc))
	        return 1;
	    while (preg_match('/[^A-Za-zА-Яа-яЁё0-9]$/u', $s)) {
	        $s = preg_replace('/[^A-Za-zА-ЯЁа-яё0-9]$/u', '', $s);
	        if (in_array($s, $exc))
	            return 1;
	    }
	    return 0;
	}
	
	public static function is_prefix($s, $prefixes) {
	    if (in_array(mb_strtolower($s, 'UTF-8'), $prefixes))
	        return 1;
	    return 0;
	}
}

?>