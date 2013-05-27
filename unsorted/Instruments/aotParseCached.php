<?php
/**
 * Solver using AOT from cache
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

include("../bootstrap.php");

use Tokenizer\Tokenizer;
use Tokenizer\Mystem;
use Tokenizer\Solver;
use Tokenizer\Database;
use Tokenizer\Sentence;

error_reporting(E_ALL);

//Замеряем начальное время
$start_time = microtime(true);

//Открыть лог
$config = parse_ini_file("../settings.ini", true);
$log = new \Log(\LogType::LEARNER, ".." . $config['log']['dir'], true);

$log->writeLog("System ready, starting Learner");

//Основной тест
$tokenizer = new Tokenizer($config);

//Инициализируем солвер и токенизатор
$solver = new Solver(Solver::CACHE);
$mystem = new Mystem(Mystem::AMBIG_YES, $solver);
$dbinstance = Database::getDB();

$log->writeLog("System initialized");

//Извлекаем все предложения
$target = $dbinstance->countForAllSentWithoutTokens();
if ($target == 0) {
    $log->writeLog("No cache available");
    exit(0);
}
$log->writeLog($target . " sentences to be calculated");

$count = 0;
$sentences = $dbinstance->getAllSentWithoutTokens($count, 1000);

while (count($sentences) != 0) {
    foreach ($sentences as $sentence) {
        $sen = new Sentence($sentence["text"], $sentence["order"], $sentence["par_id"], $sentence["id"]);
        $result = $mystem->run($sen);

        if (($result == null) || ($result == false)) {
            $log->writeLog("Bad data for sentence with ID: " . $sen->getSenid());
            $count++;
            continue;
        }

        foreach ($result as $token) {
            $token->setTextId($sentence["text_id"]);
            $token->save();
        }

        $count++;
    }
    $sentences = null;
    $log->writeLog($count . " sentences parsed");
    $sentences = $dbinstance->getAllSentWithoutTokens($count, 1000);
}

$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");