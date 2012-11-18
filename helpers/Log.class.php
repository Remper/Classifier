<?php

class Log {
	//Хендлер файла
	private $handler;
	
	/**
	 * Конструктор лог-файла
	 * 
	 * @param LogType $type Тип лога
	 * @param string $filedir Папка с логами
	 */
	function __construct($type, $filedir) {
		$filename = $filedir . date("h.i.d.m.y-");
		switch ($type) {
			case LogType::IMPORT:
				$filename.= "import";
			break;
			default:
			case LogType::TOKENIZER:
				$filename.= "tokenize";
			break;
		}
		$this->handler = fopen($filename . ".log", "w");
	}
	
	/**
	 * Записать что-то в лог
	 * 
	 * @param string $message
	 */
	public function writeLog($message) {
		$message = date("[h:i:s] ") . $message . "\n";
		fwrite($this->handler, $message);
	}
	
	public function __destruct() {
		fclose($this->handler);
	}
}

class LogType {
	const IMPORT = 1;
	const TOKENIZER = 2;
}
