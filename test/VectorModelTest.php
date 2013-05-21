<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

include("../bootstrap.php");

use Tokenizer\Features\VectorModel;
use Tokenizer\Tokenizer;
use Tokenizer\Mystem;
use Tokenizer\Solver;
use Tokenizer\Database;
use Tokenizer\Sentence;
use Tokenizer\Text;
use Tokenizer\Token;
use Learner\Learner;
use Learner\SVM;

error_reporting(E_ALL);
date_default_timezone_set("UTC");
ini_set('memory_limit', '2048M');

//Замеряем начальное время
$start_time = microtime(true);

//Открыть лог
$config = parse_ini_file("../settings.ini", true);
$log = new \Log(\LogType::LEARNER, ".." . $config['log']['dir'], true);

$log->writeLog("System ready, starting Learner");

$tokenizer = new Tokenizer($config);


$vm = new VectorModel();

$dbinstance = Database::getDB();

$log->writeLog("Caching IDF");

$count = 0;
$tokens = $dbinstance->getTokensFromValuableTexts(0, 500000);
while (count($tokens) != 0) {
    $tokens = $dbinstance->getTokensFromValuableTexts($count, 500000);

    $log->writeLog("Memory limit: " . ini_get('memory_limit') . "B. Used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");

    $idf = $vm->getScheme()->getIdf();
    foreach ($tokens as $token) {
        $idf->fillCache($token["token"], $token["count"]);
        $count++;
        if ($count % 5000 == 0) {
            $log->writeLog($count . " parsed");
        }
    }

    $tokens = null;

    $log->writeLog("Memory used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");
}

$log->writeLog("Calculating TFIDF");

$count = 0;
$texts = $dbinstance->getAllValuableTexts(0, 1000);
$filename = "model_new.txt";
$hand = fopen($filename, "w");
while (count($texts) != 0) {
    $texts = $dbinstance->getAllValuableTexts($count, 1000);

    foreach ($texts as $text) {
        fwrite($hand, $text->getOpinion() > 6 ? 1: -1);
        $vector = $vm->calculateFeatures($text);
        ksort($vector);
        foreach($vector as $key => $value) {
            fwrite($hand, " " . $key . ":" . number_format($value, 4, ".", " "));
        }
        fwrite($hand, "\n");

        $count++;
        if ($count % 100 == 0) {
            $log->writeLog($count . " parsed");
        }
    }
    $log->writeLog("Memory used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");
}
fclose($hand);

$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");