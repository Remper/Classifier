<?php

namespace Tokenizer;

/**
 * Синглтон для работы с базой данных
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Database {
    protected static $instance;  //Инстанс
    //Кешированные реквизиты
    private $user = '';
	private $pass = '';
	private $db = '';
	
	/**
	 * Вернуть экземпляр класса
	 * 
	 * @return Singleton
	 */
    public static function getDB() {
        if ( is_null(self::$instance) ) {
            self::$instance = new Singleton;
        }
        return self::$instance;
    }
	
	/**
	 * Подключиться к базе данных
	 * 
	 * @param string $user Имя пользователя
	 * @param string $pass Пароль
	 * @param string $db База данных для обращения
	 * @throws Exception
	 */
    public function connect($user, $pass, $db) {
    	$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;
	}
	
	/**
	 * Проверить подключение к базе
	 * 
	 * @return bool Подключены ли мы к базе
	 */
	public function isConnected() {
		
	}
	
	/**
	 * Переподключиться, если соединение разорвано
	 * 
	 * @return bool результат переподключения
	 * @throws Exception
	 */
	public function reconnect() {
		if (!$this->isConnected()) {
			if ($user === '')
				return false;
			
			$this->connect($this->user, $this->pass, $this->db);
		}
		return true;
	}
	
	//////
	// Геттеры
	//////
	
	/**
	 * Получить массив коэффициентов для всех известных векторов
	 * 
	 * @return array Ассоциативный массив коэффициентов vector => coeff
	 * @throws Exception
	 */
	public function getTokenCoeff() {
		return array();
	}
	
	//////
	// Функции поиска
	//////
	
	/**
	 * Существует ли заданный токен в базе
	 * 
	 * @param string $token Токен
	 * @return bool Результат запроса
	 * @throws Exception
	 */
	public function isTokenExists($token) {
		return true;
	}
	
	//////
	// Функции сохранения сущностей в базу
	//////
	
	/**
	 * Сохранить текст в базу данных
	 * 
	 * @param string $text текст для сохранения
	 * @return int ID текста
	 * @throws Exception
	 */
	public function saveText($text) {
		
	}
	
	/**
	 * Сохранить параграф в базу данных
	 * 
	 * @param string $paragraph Параграф для сохранения
	 * @throws Exception
	 */
	public function saveParagraph($paragraph) {
		
	}
	
	/**
	 * Сохранить приложение в базу данных
	 * 
	 * @param string $sentence Приложение для сохранения
	 * @throws Exception
	 */
	public function saveSentence($sentence) {
		
	}
	
	/**
	 * Сохранить токен в базу данных
	 * 
	 * @param string $paragraph Токен для сохранения
	 * @throws Exception
	 */
	public function saveToken($token) {
		
	}
	
    private function __construct() {}
    private function __clone()     {}
    private function __wakeup()    {}
}

?>