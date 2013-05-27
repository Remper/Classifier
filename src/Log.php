<?php

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Класс для ведения логов
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Log {
	//Хендлер файла
	private $handler;
    private $verbose;
    private $output;

    /**
     * Конструктор лог-файла
     *
     * @param LogType $type Тип лога
     * @param string $filedir Папка с логами
     * @param Symfony\Component\Console\Output\OutputInterface $output
     * @param bool $verbose Выводить ли лог в консоль
     */
	function __construct($type, $filedir, OutputInterface $output, $verbose = false) {
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
        $this->output = $output;
        $this->verbose = $verbose;
	}
	
	/**
	 * Записать что-то в лог
	 * 
	 * @param string $message
	 */
	public function writeLog($message) {
		$message = date("[H:i:s] ") . $message;
		fwrite($this->handler, $message . "\n");
        if ($this->verbose) {
            $this->output->writeln($message);
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