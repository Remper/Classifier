<?php

include("../bootstrap.php");
header('Content-type: text/html; charset=utf-8');

//Замеряем начальное время
$start_time = microtime(true);

//Получить настройки базы данных
$settings = parse_ini_file("../settings.ini", true);

//Создать инстанс базы данных и подключиться
$db = \Tokenizer\Database::getDB();
$db->connect($settings['database']['login'], $settings['database']['pass'], $settings['database']['db']);

//Открыть лог
$log = new \Log(\LogType::LEARNER, ".." . $settings['log']['dir']);

$log->writeLog("System ready, starting Learner");

var_dump($db->getRawData(116592));

echo "Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds\n";
$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");