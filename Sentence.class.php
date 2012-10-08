<?php

namespace Tokenizer;

/**
 * Класс-сущность предложение
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Sentence {
	//Текст предложения
	private $text;
	
	/**
	 * Конструктор
	 * 
	 * @param string $text текст предложения
	 */
	function __construct($text) {
    	$this->text = $text;
   	}
	
	/**
	 * Разбить предложение на токены
	 * 
	 * @param array $exceptions заведомо неправильные токены, дающие неправильный мёд
	 * @param array $prefixes известные префиксы для слов
	 * @return array список токенов
	 */
	public function split($exceptions, $prefixes) {
		//Инициализируем коэффициенты
		$coeff = Token::getTokenCoeff();
	    $result = array();
	    $token = '';
		
		//Непонятная пока уличная магия с нормализацией текста
	    $txt = $this->text . "  ";
	
		//Цикл по всем символам текста
	    for ($i = 0; $i < mb_strlen($txt, 'UTF-8'); ++$i) {
	    	//Берём текущий символ и некоторый его контекст [i-1, i+2]
	        $prevchar  = ($i > 0 ? mb_substr($txt, $i-1, 1, 'UTF-8') : '');
	        $char      =           mb_substr($txt, $i-0, 1, 'UTF-8');
	        $nextchar  =           mb_substr($txt, $i+1, 1, 'UTF-8');
	        $nnextchar =           mb_substr($txt, $i+2, 1, 'UTF-8');
	
	        //Переменные для сохранения цепочки, которая может быть токеном
	        $chain = $chain_left = $chain_right = '';
	        $odd_symbol = '';
			//Определение небуквенных символов в слове ([i, i+1])
	        if (Classificator::is_hyphen($char) || Classificator::is_hyphen($nextchar)) {
	            $odd_symbol = '-';
	        }
	        elseif (preg_match('/([\.\/\?\=\:&"!\+\(\)])/u', $char, $match) || preg_match('/([\.\/\?\=\:&"!\+\(\)])/u', $nextchar, $match)) {
	            $odd_symbol = $match[1];
	        }
			//Если найден корректный небуквенный символ
	        if ($odd_symbol) {
	        	//Идём назад до пробела или непонятной неведомой хрени
	            for ($j = $i; $j >= 0; --$j) {
	                $t = mb_substr($txt, $j, 1, 'UTF-8');
	                if (($odd_symbol == '-' && (Classificator::is_cyr($t) || Classificator::is_hyphen($t) || $t === "'")) ||
	                    ($odd_symbol != '-' && !Classificator::is_space($t))) {
	                    $chain_left = $t.$chain_left;
	                } else {
	                    break;
	                }
					//Обрезаем наш небуквенный символ, если он оказался в цепочке
	                if (mb_substr($chain_left, -1) === $odd_symbol) {
	                    $chain_left = mb_substr($chain_left, 0, -1);
	                }
	            }
				//Идём вперёд до пробела или непонятной неведомой хрени
	            for ($j = $i+1; $j < mb_strlen($txt, 'UTF-8'); ++$j) {
	                $t = mb_substr($txt, $j, 1, 'UTF-8');
	                if (($odd_symbol == '-' && (Classificator::is_cyr($t) || Classificator::is_hyphen($t) || $t === "'")) ||
	                    ($odd_symbol != '-' && !Classificator::is_space($t))) {
	                    $chain_right .= $t;
	                } else {
	                    break;
	                }
					//Обрезаем наш небуквенный символ, если он оказался в цепочке
	                if (mb_substr($chain_right, 0, 1) === $odd_symbol) {
	                    $chain_right = mb_substr($chain_right, 1);
	                }
	            }
				//Собираем всё в единую цепочку
	            $chain = $chain_left.$odd_symbol.$chain_right;
	        }
	
			//Просчитываем вектор признаков для символа?
	        $vector = array_merge(Classificator::char_class($char), Classificator::char_class($nextchar),
	            array(
	                Classificator::is_number($prevchar),
	                Classificator::is_number($nnextchar),
	                ($odd_symbol == '-' ? Classificator::is_dict_chain($chain): 0),
	                ($odd_symbol == '-' ? Classificator::is_suffix($chain_right) : 0),
	                Classificator::is_same_pm($char, $nextchar),
	                (($odd_symbol && $odd_symbol != '-') ? Classificator::looks_like_url($chain, $chain_right) : 0),
	                (($odd_symbol && $odd_symbol != '-') ? Classificator::is_exception($chain, $exceptions) : 0),
	                ($odd_symbol == '-' ? Classificator::is_prefix($chain_left, $prefixes) : 0),
	                (($odd_symbol == ':' && $chain_right !== '') ? Classificator::looks_like_time($chain_left, $chain_right) : 0)
	        ));
	        $vector = implode('', $vector);
	
	        if (isset($coeff[bindec($vector)])) {
	            $sum = $coeff[bindec($vector)];
	        } else {
	            $sum = 0.5;
	        }
			var_dump($vector);
			var_dump($char);
			var_dump($chain);
	        $token .= $char;
	
	        if ($sum > 0) {
	            $token = trim($token);
	            if ($token !== '')
					array_push($result, new Token($token, $vector, $sum));
	            $token = '';
	        }
	    }
	    return $result;
	}
	
	////
	// Геттеры
	////
	
	/**
	 * Получить текст предложения
	 */
	public function getText() {
		return $this->text;
	}
}

?>