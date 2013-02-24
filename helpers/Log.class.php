<?php

/**
 * Класс для ведения логов
 * @author Ярослав Нечаев <mail@remper.ru>
 */
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
		$filename = $filedir . date("Y-m-d-h.i-");
		switch ($type) {
			case LogType::IMPORT:
				$filename.= "import";
			break;
            case LogType::CORPUS:
                $filename.= "corpus";
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
    const CORPUS = 3;
}
