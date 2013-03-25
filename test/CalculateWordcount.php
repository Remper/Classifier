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
$log = new \Log(\LogType::LEARNER, ".." . $config['log']['dir'], true);

$log->writeLog("System ready, starting Learner");

$tokenizer = new Tokenizer($config);
$dbinstance = Database::getDB();

$log->writeLog("System initialized");

$texts = $dbinstance->getAllTexts(0, 100);
$count = 0;

while (count($texts) != 0) {
    foreach ($texts as $text) {
        $query = "SELECT count(*) AS `count` FROM tokens WHERE text_id = :id";
        $result = $dbinstance->ExecuteQuery($query, array(
           array(":id", $text["id"], \PDO::PARAM_INT)
        ));

        $result = $result->fetch(\PDO::FETCH_ASSOC);
        $query = "UPDATE texts SET wordcount = :count WHERE id = :id";
        $result = $dbinstance->ExecuteQuery($query, array(
            array(":count", $result["count"], \PDO::PARAM_INT),
            array(":id", $text["id"], \PDO::PARAM_INT)
        ));
    }
    $count += 100;
    $texts = $dbinstance->getAllTexts($count, 100);
    $log->writeLog("100 texts parsed");
}

$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");