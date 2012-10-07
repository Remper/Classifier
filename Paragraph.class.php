<?php

namespace Tokenizer;

/**
 * Класс-сущность параграф
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Paragraph {
	//Текст параграфа
	private $text;
	
	/**
	 * Конструктор
	 * 
	 * @param string $text текст параграфа
	 */
	function __construct($text) {
    	$this->text = $text;
   	}
	
	/**
	 * Разбить параграф на предложения
	 * 
	 * @return array список предложений
	 */
	public function split() {
		//Разбиваем по переносу строки
		$splitText = preg_split('/[\r\n]+/', $this->text);
		//Фильтруем пустые предложения и возвращаем результат
		$result = array();
		foreach ($splitText as $paragraph)
			if (preg_match('/\S/', $paragraph))
				array_push($result, new Tokenizer\Sentence($paragraph));
			
		return $result;
	}
	
	////
	// Геттеры
	////
	
	/**
	 * Получить текст параграфа
	 */
	public function getText() {
		return $this->text;
	}
}

?>