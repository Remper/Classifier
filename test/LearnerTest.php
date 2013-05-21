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

$tokenizer = new Tokenizer($config);

$log->writeLog("System initialized");

//Выбираем все тексты
$dbinstance = Database::getDB();
$texts = $dbinstance->getAllValuableTexts(0, 10000);
$maximum = $dbinstance->getHighestWordcount();
$count = 0;

$normalized = array();
foreach ($texts as $text) {
    $class = $text["opinion"] > 5 ? 1: -1;
    $tokens = $dbinstance->getTokensByTextID($text["id"]);
    $freq = array();
    $freq[0] = $class;
    foreach ($tokens as $token) {
        $handler = $token["lemma_id"]+$token["form_id"]*10000000;
        if ($handler == 0)
            $handler = 1;
        if (!isset($freq[$handler]))
            $freq[$handler] = 0;
        $freq[$handler]++;
    }
    $keys = array_keys($freq);
    for ($i = 0; $i < count($keys); $i++) {
        if ($keys[$i] != 0)
            $freq[$keys[$i]] = $freq[$keys[$i]] / count($tokens);
    }

    $normalized[] = $freq;

    $count++;
    if ($count % 50 == 0)
        $log->writeLog("Prepared " . $count . " texts");
}
$filename = "model.txt";
$hand = fopen($filename, "w");
foreach($normalized as $norma) {
    fwrite($hand, $norma[0]);
    ksort($norma);
    foreach($norma as $key => $value) {
        if ($key != 0)
            fwrite($hand, " " . $key . ":" . number_format($value, 4, ".", " "));
    }
    fwrite($hand, "\n");
}
fclose($hand);

/*
$cost = 1;
$gamma = 1;
for ($cost = 1; $cost > -10; $cost--) {
    for ($gamma = 1; $gamma > -10; $gamma--) {
        $learner = new Learner(Learner::SVM, array(
            "kernel" => SVM::RBF,
            "cost" => pow(2, $cost),
            "gamma" => pow(2, $gamma)
        ));
        $learner = $learner->getLearner();
        $result = $learner->crossvalidate($normalized, 5);
        unset($learner);

        $log->writeLog("Result: (" . pow(2, $cost) . ", " . pow(2, $gamma) . ") " . $result);
    }
}*/

$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");