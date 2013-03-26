<?php
/**
 * Test learner from file
 *
 * Date: 26.03.13
 * Time: 15:12
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

$cost = 1;
$gamma = 1;
for ($cost = 1; $cost > -10; $cost--) {
    for ($gamma = 1; $gamma > -10; $gamma--) {
        $normalized = fopen("model.txt", "r");

        $learner = new Learner(Learner::SVM, array(
            "kernel" => SVM::RBF,
            "cost" => pow(2, $cost),
            "gamma" => pow(2, $gamma)
        ));
        $learner = $learner->getLearner();
        $result = $learner->crossvalidate($normalized, 10);
        unset($learner);
        fclose($normalized);

        $log->writeLog("Result: (" . pow(2, $cost) . ", " . pow(2, $gamma) . ") " . $result);
    }
}

$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");