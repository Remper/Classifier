<?php

require("../Tokenizer/Database.class.php");
require("../helpers/Log.class.php");


//Замеряем начальное время
$start_time = microtime(true);

//Получить настройки базы данных
$settings = parse_ini_file("../settings.ini", true);

//Создать инстанс базы данных и подключиться
$db = \Tokenizer\Database::getDB();
$db->connect($settings['database']['login'], $settings['database']['pass'], $settings['database']['db']);

//Открыть лог
$log = new \Log(\LogType::IMPORT, ".." . $settings['log']['dir'], true);

$log->writeLog("System ready, starting to gather data");

//Получаем первую порцию данных
$start = 0;
$limit = 100;
$count = 0;
$sens = $db->getAllSentences($start, $limit);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://aot.ru/cgi-bin/translate.cgi');
curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
curl_setopt($curl, CURLOPT_POST, true);

while (count($sens) != 0) {
    foreach ($sens as $sen) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, "TemplateFile=../wwwroot/demo/graph.html&submit1=test&action=graph&russian="
            .mb_convert_encoding($sen["text"], "Windows-1251", "UTF-8"));
        $out = curl_exec($curl);
        $pos = strpos($out, '<param name="graph" value="')+27;
        $endpos = strpos($out, '">', $pos);
        $out = mb_convert_encoding(substr($out, $pos, $endpos-$pos), "UTF-8", "Windows-1251");
        $db->addRawData($sen["id"], $out);
        $count++;
    }
    $start += 100;
    $sens = $db->getAllSentences($start, $limit);

    $log->writeLog($count . " sentences parsed");
}

curl_close($curl);

//Выводим статистику
$log->writeLog("Sentences parsed: " . $count);
$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");