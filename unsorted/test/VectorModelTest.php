<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

include("../bootstrap.php");

use Tokenizer\Features\VectorModel;
use Tokenizer\Features\VectorModel\IDF\IDFP;
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

$log->writeLog("Caching IDF & IDFP");

$count = 0;
$tokens = $dbinstance->getTokensFromValuableTexts(0, 500000);
$log->writeLog("Memory limit: " . ini_get('memory_limit') . "B. Used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");
$idf = $vm->getScheme()->getIdf();
$idfp = new IDFP();
while (count($tokens) != 0) {
    foreach ($tokens as $token) {
        $idf->fillCache($token["token"], $token["count"]);
        $idfp->fillCache($token["token"], $token["count"]);
        $count++;
        if ($count % 5000 == 0) {
            $log->writeLog($count . " parsed");
        }
    }

    $tokens = null;
    $log->writeLog("Memory used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");
    $tokens = $dbinstance->getTokensFromValuableTexts($count, 500000);
}

$log->writeLog("Calculating TFIDF");

$count = 0;
$positive = 0;
$texts = $dbinstance->getAllValuableTexts(0, 500);
$tfidf = fopen("model_tfidf.txt", "w");
$tfidfp = fopen("model_tfidfp.txt", "w");
while (count($texts) != 0) {
    foreach ($texts as $text) {
        $label  = "-1";
        if ($text->getOpinion() > 6) {
            $positive++;
            $label = "+1";
        }
        fwrite($tfidf, $label);
        fwrite($tfidfp, $label);

        $vm->getScheme()->setPrecomputedIdf($idf);
        $vector = $vm->calculateFeatures($text);
        ksort($vector);
        foreach($vector as $key => $value) {
            fwrite($tfidf, " " . $key . ":" . number_format($value, 4, ".", " "));
        }
        fwrite($tfidf, "\n");
        unset($vector);

        $vm->getScheme()->setPrecomputedIdf($idfp);
        $vector = $vm->calculateFeatures($text);
        ksort($vector);
        foreach($vector as $key => $value) {
            fwrite($tfidfp, " " . $key . ":" . number_format($value, 4, ".", " "));
        }
        fwrite($tfidfp, "\n");
        unset($vector);

        $count++;
        if ($count % 100 == 0) {
            $log->writeLog($count . " parsed");
        }
    }

    $texts = null;
    $vm->getScheme()->clearCache();
    $log->writeLog("Memory used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");
    $texts = $dbinstance->getAllValuableTexts($count, 500);
}
fclose($tfidf);
fclose($tfidfp);

$log->writeLog("Cleaning up");

$texts = null;
$idf = null;
$vm = null;

$log->writeLog("Memory used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");

if ($positive > $count-$positive) {
    $posRate = ($count-$positive) / $count;
    $negRate = 1 - $posRate;
} else {
    $negRate = $positive / $count;
    $posRate = 1 - $negRate;
}

$files = array(
    "TFxIDF" => "model_tfidf",
    "TFxIDFP" => "model_tfidfp"
);
foreach ($files as $key => $value) {
    $log->writeLog("Starting LIBLINEAR cross-validation for " . $key);

    $log->writeLog("Training set size: " . $count);
    $log->writeLog("Weights: " . number_format($posRate, 2, ".", " ") . " " . number_format($negRate, 2, ".", " "));
    $types = array(1,3,4);
    foreach ($types as $typeKey => $typeValue) {
        $log->writeLog($typeValue . ": " . exec("train -s ". $typeValue ." -c 4 -e 0.1 -v 5 -w+1 ". $posRate ." -w-1 ". $negRate ." ". $value .".txt"));
    }
}
foreach ($files as $key => $value) {
    $log->writeLog("Starting LIBSVM cross-validation for " . $key);

    $log->writeLog("Training set size: " . $count);
    $log->writeLog("Weights: " . number_format($posRate, 2, ".", " ") . " " . number_format($negRate, 2, ".", " "));
    $log->writeLog("Linear: " . exec("svm-train -h 0 -b 1 -s 0 -t 0 -v 5 -w1 ". $posRate ." -w-1 ". $negRate ." ". $value .".txt"));
    $log->writeLog("Saving model: " . exec("svm-train -h 0 -b 1 -s 0 -t 0 -w1 ". $posRate ." -w-1 ". $negRate ." ". $value .".txt " . $value . ".model"));
}

$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");