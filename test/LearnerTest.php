<?php
/**
 * Test learner
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
$log = new \Log(\LogType::LEARNER, ".." . $config['log']['dir'], true);

$log->writeLog("System ready, starting Learner");

$learner = new Learner(Learner::SVM, array(
   "kernel" => SVM::RBF,
   "cost" => 2,
   "gamma" => 0.25
));
$learner = $learner->getLearner();

$tokenizer = new Tokenizer($config);

$log->writeLog("System initialized");

//Выбираем все тексты
$dbinstance = Database::getDB();
$texts = $dbinstance->getAllValuableTexts(0, 10);
$maximum = $dbinstance->getHighestWordcount();
$count = 0;

$normalized = array();
foreach ($texts as $text) {
    $class = $text["opinion"] > 5 ? 1: -1;
    $normalized[] = array(
        $class,
    );

    var_dump($dbinstance->getTokensByTextID($text["id"]));
}


//var_dump($learner->crossvalidate($normalized, 5));

$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");