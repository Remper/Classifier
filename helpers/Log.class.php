<?php

/**
 * Класс для ведения логов
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Log {
	//Хендлер файла
	private $handler;
    private $verbose;

    /**
     * Конструктор лог-файла
     *
     * @param LogType $type Тип лога
     * @param string $filedir Папка с логами
     * @param bool $verbose Выводить ли лог в консоль
     */
	function __construct($type, $filedir, $verbose = false) {
		$filename = $filedir . date("Y-m-d-H.i-");
        $types = array(
            LogType::IMPORT => "import",
            LogType::CORPUS => "corpus",
            LogType::TOKENIZER => "tokenizer",
            LogType::LEARNER => "learner",
            LogType::INSTRUMENT => "instrument"
        );
        $filename .= $types[$type];
		$this->handler = fopen($filename . ".log", "w");
        $this->verbose = $verbose;
	}
	
	/**
	 * Записать что-то в лог
	 * 
	 * @param string $message
	 */
	public function writeLog($message) {
		$message = date("[H:i:s] ") . $message . "\n";
		fwrite($this->handler, $message);
        if ($this->verbose) {
            echo $message;
        }
	}
	
	public function __destruct() {
		fclose($this->handler);
	}
}

class LogType {
	const IMPORT = 1;
	const TOKENIZER = 2;
    const CORPUS = 3;
    const LEARNER = 4;
    const INSTRUMENT = 5;
}