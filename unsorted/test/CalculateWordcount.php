<?php
/**
 * Calculate wordcount for each text
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

include("../bootstrap.php");

use Tokenizer\Tokenizer;
use Tokenizer\Mystem;
use Tokenizer\Solver;
use Tokenizer\Database;
use Tokenizer\Sentence;
use Learner\Learner;
use Learner\SVM;

error_reporting(E_ALL);

//Замеряем начальное время
$start_time = microtime(true);

//Открыть лог
$config = parse_ini_file("../settings.ini", true);
$log = new \Log(\LogType::INSTRUMENT, ".." . $config['log']['dir'], true);

$log->writeLog("System ready, starting Learner");

$tokenizer = new Tokenizer($config);
$dbinstance = Database::getDB();

$log->writeLog("System initialized");

$count = 0;
$texts = $dbinstance->getTextsWithWordcounts($count, 1000);

while (count($texts) != 0) {
    foreach ($texts as $text) {
        $dbinstance->setTextWordcount($text["id"], $text["count"]);
        $count++;
    }
    $texts = $dbinstance->getTextsWithWordcounts($count, 1000);
    $log->writeLog("1000 texts parsed");
}

$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");