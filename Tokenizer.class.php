<?php

namespace Tokenizer;

/**
 * Класс-контроллер для разбивания текста на токены
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Tokenizer {
	private $token_exceptions;
	private $token_prefixes;
	private $dbsettings;
	private $db;
	
	/**
	 * Конструктор
	 * 
	 * @param array $settings настройки токенизатора
	 * {
	 *    token: {
	 *       exceptions: "",
	 *       prefixes: ""
	 *    },
	 *    database: {
	 *       login: "",
	 *       pass: "",
	 *       db: ""
	 *    }
	 * }
	 */
	function __construct($settings) {
		//Инициализируем настройки
    	$this->token_exceptions = array_map('mb_strtolower', file($settings['token']['exceptions'], FILE_IGNORE_NEW_LINES));
    	$this->token_prefixes = file($settings['token']['prefixes'], FILE_IGNORE_NEW_LINES);
		$this->dbsettings = $settings['database'];
		//Получаем инстанс базы данных
		$this->db = Tokenizer\Database::getDB();
		$this->db->connect($this->dbsettings["login"], $this->dbsettings["pass"], $this->dbsettings["db"]);
   	}
	
	/**
	 * Токенизировать текст
	 * 
	 * @param string $text текст для токенизациии
	 * @return bool статус токенизации
	 */
	public function tokenizeText($text) {
		//Чистим текст
		$text = $this->filterText($text);
		//Разбиваем текст на параграфы
		$paragraphs = $this->splitText($text);
		//Разбиваем параграфы на предложения
		$sentences = array_map(array($this, 'splitParagraph'), $paragraphs);
		//Разбиваем предложения на токены
		$tokens = array_map(array($this, 'splitSentence'), $sentences);
		
		//Сохраняем текст в базу данных
		$this->save($text, $paragraphs, $sentences, $tokens);
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
	
	/**
	 * Разбить текст на параграфы
	 * 
	 * @param string $text текст
	 * @return array Параграфы
	 */
	private function splitText($text) {
		//Разбиваем по двойному переносу строки
		$splitText = preg_split('/\r?\n\r?\n\r?/', $text);
		//Фильтруем пустые параграфы и возвращаем параграфы
		$result = array();
		foreach ($splitText as $paragraph)
			if (preg_match('/\S/', $paragraph))
				array_push($result, new Tokenizer\Paragraph($paragraph));
			
		return $result;
	}
	
	/**
	 * Сохранить размеченный текст в базу данных
	 * 
	 * @param string $text
	 * @param string $paragraphs
	 * @param string $sentences
	 * @param string $tokens
	 * @return bool Результат сохранения
	 */
	private function save($text, $paragraphs, $sentences, $tokens) {
		try {
			$this->db->reconnect();
			$id = $this->db->saveText($text);
			//$paragraphs->save($id);
			//$sentences->save($id);
			//$tokens->save($id);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
	
	/////
	// Колбеки
	/////
	public function splitParagraph($paragraph) {
		return $paragraph->split();
	}
	
	public function splitSentence($sentence) {
		return $sentence->split($this->token_exceptions, $this->token_prefixes);
	}
}

?>