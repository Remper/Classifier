<?php

namespace Tokenizer;

/**
 * Класс-сущность параграф
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Paragraph extends Entity implements TRPiece, TRWhole {
	//Порядок в тексте
	private $order;
	//ID родителя-текста
	private $textid;
	
	/**
	 * Конструктор
	 * 
	 * @param string $text текст параграфа
	 * @param int $order порядок в тексте
	 * @param int $textid ID текста (необязательно)
	 * @param int $parid ID параграфа (необязательно)
	 */
	function __construct($text, $order, $textid = 0, $parid = 0) {
    	$this->text = $text;
		$this->order = $order;
		$this->id = $parid;
		$this->textid = $textid;
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
				array_push($result, new Sentence($paragraph));
			
		return $result;
	}
	
	/**
	 * Сохранить параграф в базу данных
	 * 
	 * @param int $textid ID текста-родителя
	 * @return int ID свежесохранённого параграфа
	 */
	public function save($textid = 0) {
		if ($this->isSaved())
			return false;
		
		if ($this->textid == 0) {
			if ($textid == 0)
				return false;
			
			$this->textid = $textid;
		}
		
		$dbinstance = Database::getDB();
		return $this->parid = $dbinstance->saveParagraph($this);
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
	
	/**
	 * Получить порядок в тексте
	 */
	public function getOrder() {
		return $this->order;
	}
	
	/**
	 * Получить ID родителя
	 */
	public function getParentId() {
		return $this->textid;
	}
	
	/**
	 * Назначить родителя
	 */
	public function setParentId($textid) {
		if ($this->isSaved() && $this->textid)
			return false;
		
		$this->textid = $textid;
	}
	
	/**
	 * Сохранён ли параграф в базу данных
	 */
	public function isSaved() {
		return (bool) $this->parid;
	}
	
	/**
	 * Получить массив всех предложений параграфа
	 * 
	 * @return array массив предложений, либо false, если параграф не сохранён в базу данных
	 */
	public function getSentences() {
		if (!$this->isSaved())
			return false;
		
		$dbinstance = Database::getDB();
		return array_map(array($this, 'initSentence'), $dbinstance->getSentences($this->parid)); 
	}
	
	////
	// Колбеки
	////
	
	public function initSentence($sentence) {
		return new Sentence($sentence['text'], $sentence['order'], $sentence['par_id'], $sentence['id']);
	}
}

?>