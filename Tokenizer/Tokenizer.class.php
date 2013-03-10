<?php

namespace Tokenizer;

/**
 * Класс-контроллер для разбивания текста на токены
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Tokenizer {
    const BASIC = 0;
    const MYSTEM = 1;

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
    	//$this->token_exceptions = array_map('mb_strtolower', file(apath($settings['token']['exceptions']), FILE_IGNORE_NEW_LINES));
    	//$this->token_prefixes = file(apath($settings['token']['prefixes']), FILE_IGNORE_NEW_LINES);
		$this->dbsettings = $settings['database'];
		//Получаем инстанс базы данных
		$this->db = Database::getDB();
		$this->db->connect($this->dbsettings["login"], $this->dbsettings["pass"], $this->dbsettings["db"]);
   	}
	
	/**
	 * Токенизировать текст
	 * 
	 * @param string $text текст для токенизациии
     * @param int $method метод токенизации
	 * @return bool статус токенизации
	 */
	public function tokenizeText($text, $method = self::MYSTEM) {
		$text = new Text($text);
		if (!$text->save())
			return false;
		//Разбиваем текст на параграфы
		$paragraphs = $text->split();
		//Сохраняем параграфы в базу данных, разбиваем параграфы на предложения
		$sentences = array();
		foreach ($paragraphs as $paragraph) {
			try {
				$paragraph->save();
                $sentences = array_merge($sentences, $paragraph->split());
			} catch (\Exception $e) {
				//Обработка ошибки сохранения
			}
		}

		//Сохраняем предложения в базу данных, разбиваем предложения на токены
		$tokens = array();
		foreach ($sentences as $sentence) {
			try {
				$sentence->save();
                switch ($method) {
                    case self::BASIC:
                        $tokens= array_merge($tokens, $sentence->split());
                    break;
                    case self::MYSTEM:
                        $mystem = new Mystem(Mystem::AMBIG_YES);
                        $tokens = array_merge($tokens, $mystem->run($sentence));
                    break;
                }
			} catch (\Exception $e) {
				//Обработка ошибки сохранения
			}
		}
		//var_dump($tokens);
		//Сохраняем токены
		//foreach ($tokens as $token)
			//$token->save();
	}
}

?>