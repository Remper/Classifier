<?php

namespace Tokenizer;

/**
 * Класс-сущность токен
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Token {
	//Токен
	private $token;
	//Вектор признаков для этого токена
	private $vector;
	//Коэффициент
	private $coeff;
	
	/**
	 * Конструктор
	 * 
	 * @param string $token Текст токена
	 * @param string $vector Вектор признаков
	 * @param int $coeff Коэффициент
	 */
	function __construct($token, $vector, $coeff) {
    	$this->token = $token;
		$this->vector = $vector;
		$this->coeff = $coeff;
   	}
	
	/**
	 * Существует ли заданный токен в базе данных
	 * 
	 * @param string $token Токен
	 * @return bool Результат запроса
	 */
	public static function isValidToken($token) {
		$db = Tokenizer\Database::getDB();
		try {
			$db->reconnect();
			return $db->isTokenExists($token);
		} catch (Exception $e) {
			return false;
		}
	}
	
	/**
	 * Получить массив коэффициентов для всех известных векторов
	 * 
	 * @return array Ассоциативный массив коэффициентов vector => coeff
	 */
	public static function getTokenCoeff() {
		$db = Tokenizer\Database::getDB();
		try {
			$db->reconnect();
			return $db->getTokenCoeff();
		} catch (Exception $e) {
			return array();
		}
	}
}

?>