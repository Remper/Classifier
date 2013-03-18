<?php
/**
 * Parse films from trecs2011 without splitting the sentences
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

include("../bootstrap.php");

use Tokenizer\Tokenizer;
use Tokenizer\Mystem;
use Tokenizer\Solver;
use Tokenizer\Database;
use Tokenizer\Text;
use Tokenizer\Sentence;

error_reporting(E_ALL);

//Замеряем начальное время
$start_time = microtime(true);

//Открыть лог
$config = parse_ini_file("../settings.ini", true);
$log = new \Log(\LogType::LEARNER, ".." . $config['log']['dir'], true);

$log->writeLog("System ready, starting parsing Imhonet-films");

//Основной тест
$tokenizer = new Tokenizer($config);

//Открываем документ
$xml = new XMLReader();
$xml->open("../imhonet-films.xml", "utf-8");
$log->writeLog("XML Opened successfully, parsing started");

$texts = 0;
$xml->read(); $xml->read(); $xml->next(); $xml->next(); $xml->next(); $xml->read();

while ($xml->nodeType != 0) {
    if ($xml->nodeType == XMLReader::ELEMENT) {
        if ($xml->name != "row")
            break;

        $xml->read();
        $row = array(
            "opinion" => 0,
            "text" => ""
        );

        while ($xml->nodeType != 0) {
            if ($xml->nodeType == XMLReader::ELEMENT) {
                if ($xml->name != "value")
                    break;

                switch ($xml->getAttribute("columnNumber")) {
                    case "0":
                        $row["opinion"] = $xml->readInnerXml();
                        break;
                    case "4":
                        $row["text"] = $xml->readInnerXml();
                        break;
                    default:
                }
            }
            $xml->next();
        }

        $text = new Text($row["text"], 0, $row["opinion"]);
        $text->save();
        $paragraphs = $text->split();

        $sentences = array();
        foreach ($paragraphs as $paragraph) {
            $paragraph->save();
            $sentences = array_merge($sentences, $paragraph->split());
        }

        foreach ($sentences as $sentence) {
            $sentence->save();
        }

        $texts++;
    } else {
        $xml->next();
    }
}

$log->writeLog("Texts parsed: " . $texts);
$log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");