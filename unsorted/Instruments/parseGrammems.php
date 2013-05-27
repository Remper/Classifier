<?php
/**
 * Скрипт для перегона граммем из словаря в базу данных
 *
 * Date: 26.02.13
 * Time: 10:14
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

require("../Tokenizer/Database.class.php");
require("../Tokenizer/Grammem.class.php");
require("../helpers/Log.class.php");

//Замеряем начальное время
$start_time = microtime(true);

//Получить настройки базы данных
$settings = parse_ini_file("../settings.ini", true);

//Создать инстанс базы данных и подключиться
$db = \Tokenizer\Database::getDB();
$db->connect($settings['database']['login'], $settings['database']['pass'], $settings['database']['db']);

//Открыть лог
$log = new \Log(\LogType::IMPORT, ".." . $settings['log']['dir']);

//Открываем документ
$xml = new XMLReader();
$xml->open("../dict.opcorpora.xml", "utf-8");
$log->writeLog("XML Opened successfully, parsing started");

//Перемещаемся на уровень граммем
$xml->read(); $xml->read();
$xml->read(); $xml->read();

$grammemc = 0;

$grammems = array();
while ($xml->next())
	if ($xml->nodeType == XMLReader::ELEMENT) {
        if ($xml->name != "grammem")
            break;
        $grammemc++;

        $parent = $xml->getAttribute("parent");
        $name = $xml->readInnerXml();
        if ($parent == "") {
            $gr = \Tokenizer\Grammem::createGrammem($name);
        } else {
            $gr = \Tokenizer\Grammem::createGrammem($name, $grammems[$parent]);
        }
        $grammems[$name] = $gr->getId();
    }

echo "Grammems parsed: " . $grammemc . "\n";
$log->writeLog("Grammems parsed: " . $grammemc);
echo "Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds\n";
$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");
