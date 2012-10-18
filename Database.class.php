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
	//Соединение
	private $conn;

//////
// Функции обслуживания соединения
//////
	
	/**
	 * Вернуть экземпляр класса
	 * 
	 * @return Singleton
	 */
    public static function getDB() {
        if ( is_null(self::$instance) ) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
	
	/**
	 * Подключиться к базе данных
	 * 
	 * @param string $user Имя пользователя
	 * @param string $pass Пароль
	 * @param string $db DSN для обращения
	 * @throws Exception
	 */
    public function connect($user, $pass, $db) {
    	$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;
		
		try {
			//Инициализируем объект базы данных
			$this->conn = new PDO($this->db,  $this->user, $this->pass);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//Устанавливаем дефолтную кодировку
			$sth = $this->db->query('SET NAMES utf8');
		} catch (PDOException $e) {
			throw new Exception("Невозможно подключиться к базе данных");
		}
	}
	
	/**
	 * Проверить подключение к базе
	 * 
	 * @return bool Подключены ли мы к базе
	 */
	public function isConnected() {
		try {
			$sth = $this->db->query('SHOW TABLES');
		} catch (PDOException $e) {
			return false;
		}
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
		$result = $this->ExecuteQuery("
			SELECT
				`vector`, `coeff`
			FROM `coeffs`
		");
		if (!($result = $result->fetch(PDO::FETCH_ASSOC)))
			throw new Exception("Can't work without data");
		
		return $result;
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
		$result = $this->ExecuteQuery("
			SELECT
				count(*) AS ccc
			FROM `tokens`
			WHERE `lemma_id` <> 0 AND `text` = :token
			", array(
				array(":token", $token, PDO::PARAM_STR)
			)
		);
		$result = $result->fetch(PDO::FETCH_ASSOC);
		return (bool) $result['ccc'];
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
		$result = $this->ExecuteQuery("
			SELECT
				count(*) AS ccc
			FROM `tokens`
			WHERE `lemma_id` <> 0 AND `text` = :token
			", array(
				array(":token", $token, PDO::PARAM_STR)
			)
		);
		$result = $result->fetch(PDO::FETCH_ASSOC);
		return (bool) $result['ccc'];
	}
	
	/**
	 * Сохранить параграф в базу данных
	 * 
	 * @param string $paragraph Параграф для сохранения
	 * @param int $textid ID текста-родителя
	 * @return int ID параграфа
	 * @throws Exception
	 */
	public function saveParagraph($paragraph, $textid) {
		$this->ExecuteQuery("
			INSERT INTO
				`paragraphs`
			(`id`, `text_id`, `order`, `text`)
			VALUES (NULL, :textid, :order, :text)
			", array(
				array(":textid", $textid, PDO::PARAM_INT),
				array(":order", $paragraph->getOrder(), PDO::PARAM_INT),
				array(":text", $paragraph->getText(), PDO::PARAM_STR)
			)
		);
	}
	
	/**
	 * Сохранить приложение в базу данных
	 * 
	 * @param string $sentence Приложение для сохранения
	 * @param int $parid ID параграфа-родителя
	 * @return int ID предложения
	 * @throws Exception
	 */
	public function saveSentence($sentence, $parid) {
		$this->ExecuteQuery("
			INSERT INTO
				`sentences`
			(`id`, `par_id`, `order`, `text`)
			VALUES (NULL, :parid, :order, :text)
			", array(
				array(":parid", $parid, PDO::PARAM_INT),
				array(":order", $sentence->getOrder(), PDO::PARAM_INT),
				array(":text", $sentence->getText(), PDO::PARAM_STR)
			)
		);
	}
	
	/**
	 * Сохранить токен в базу данных
	 * 
	 * @param string $paragraph Токен для сохранения
	 * @param int $senid ID предложения-родителя
	 * @return int ID токена
	 * @throws Exception
	 */
	public function saveToken($token, $senid) {
		$this->ExecuteQuery("
			INSERT INTO
				`token`
			(`id`, `sen_id`, `order`, `text`, `lemma_id`, `checked`, `method`)
			VALUES (NULL, :senid, :order, :text, :lemma_id, :checked, :method)
			", array(
				array(":parid", $senid, PDO::PARAM_INT),
				array(":order", $token->getOrder(), PDO::PARAM_INT),
				array(":text", $token->getText(), PDO::PARAM_STR),
				array(":lemma_id", $token->getLemmaId(), PDO::PARAM_INT),
				array(":checked", (int) $token->isChecked(), PDO::PARAM_INT),
				array(":method", $token->getMethod(), PDO::PARAM_INT)
			)
		);
	}
	
//////
// Служебная фигня
//////
	
	/**
	 * Выполнить запрос в базу данных
	 * 
	 * @param string $query Запрос к базе
	 * @param array $params Параметры запроса
	 * @return PDOStatement Объект запроса в базу
	 * @throws PDOException
	 */
	private function ExecuteQuery($query, $params) {
		$dbo = $this->db->prepare($query);
		foreach($params as $param) {
			$dbo->bindValue($param[0], $param[1], $param[2]);
		}
		$dbo->execute();
		
		return $dbo;
	}
	
    private function __construct() {}
    private function __clone()     {}
    private function __wakeup()    {}
}

?>