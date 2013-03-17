<?php
/**
 * Test solver using AOT from cache
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

//Извлекаем все предложения
$dbinstance = Database::getDB();
$sentences = $dbinstance->getAllSentences();

//Инициализируем солвер и токенизатор
$solver = new Solver(Solver::CACHE);
$mystem = new Mystem(Mystem::AMBIG_YES, $solver);

$log->writeLog("System initialized");
$count = 0;

foreach ($sentences as $sentence) {
    $sen = new Sentence($sentence["text"], $sentence["order"], $sentence["par_id"], $sentence["id"]);
    $result = $mystem->run($sen);
    foreach ($result as $token)
        $token->save();

    $count++;
    $log->writeLog("Sentence " . $count . " parsed");
}

$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");