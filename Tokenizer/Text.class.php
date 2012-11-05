<?php

namespace Tokenizer;

/**
 * Класс-сущность текст
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Text extends Entity implements TRWhole {
	/**
	 * Конструктор
	 * 
	 * @param string $text текст
	 * @param int $textid ID текста (необязательно)
	 */
	function __construct($text, $textid = 0) {
    	$this->text = $text;
		$this->id = $textid;
   	}
	
	/**
	 * Разбить параграф на предложения
	 * 
	 * @return array список предложений
	 */
	public function split() {
		
	}
	
	/**
	 * Сохранить текст
	 * 
	 * @return int ID параграфа
	 */
	public function save() {
		
	}
	
	////
	// Геттеры
	////
	
	/**
	 * Получить массив всех параграфов текста
	 * 
	 * @return array массив текстов, либо false, если текст не сохранён в базу данных
	 */
	public function getPieces() {
		
	}
}

?>