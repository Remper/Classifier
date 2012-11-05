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
    	$this->text = $this->filterText($text);;
		$this->id = $textid;
   	}
	
	/**
	 * Разбить параграф на предложения
	 * 
	 * @return array список предложений
	 */
	public function split() {
		//Разбиваем по двойному переносу строки
		$splitText = preg_split('/\r?\n\r?\n\r?/', $text);
		//Фильтруем пустые параграфы и возвращаем параграфы
		$result = array();
		foreach ($splitText as $paragraph)
			if (preg_match('/\S/', $paragraph))
				array_push($result, new Tokenizer\Paragraph($paragraph, count($result), $this->id));
			
		return $result;
	}
	
	/**
	 * Сохранить текст
	 * 
	 * @return int ID параграфа
	 */
	public function save() {
		if ($this->isSaved())
			return $this->id;
		
		if ($this->textid == 0) {
			if ($textid == 0)
				return false;
			
			$this->textid = $textid;
		}
		
		$dbinstance = Database::getDB();
		return $this->parid = $dbinstance->saveText($this);
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
		if ($this->isSaved())
			return false;
	}
	
	/**
	 * Очистить текст от плохих символов
	 * 
	 * @param string $text текст для очистки
	 * @return string очищенный текст
	 */
	private function filterText($text) {
		//Запрещённые символы
		$forbid = array(769, //модификатор диакритических символов
			173, //символ возможного переноса
			8192, 8193, 8194, 8195, 8196, 8197, 8198, 8199, 8200, 8201, 8202, 8203, 8237, 8239, 8288, 12288 //Пробелы/символы работы с кареткой
		);
		//Пробегаемся по всему тексту и фильтруем плохие символы
		$result = "";
	    for ($i = 0; $i < mb_strlen($text, 'UTF-8'); ++$i) {
	        $char = mb_substr($text, $i, 1, 'UTF-8');
	        if (!in_array(uniord($char), $forbid)) 
	        	$result .= $char;
	    }
		
		return $result;
	}
}

?>