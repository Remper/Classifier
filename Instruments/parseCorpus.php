<?php
/**
 * Скрипт для перегона корпуса из XML в базу данных
 */
 
require("../Tokenizer/Database.class.php");
require("../Tokenizer/Entity.class.php");
require("../Tokenizer/Text.class.php");
require("../Tokenizer/Paragraph.class.php");
require("../Tokenizer/Sentence.class.php");
require("../Tokenizer/Token.class.php");
require("../helpers/Log.class.php");

//Замеряем начальное время
$start_time = microtime(true);

//Получить настройки базы данных
$settings = parse_ini_file("../settings.ini", true);

//Создать инстанс базы данных и подключиться
$db = \Tokenizer\Database::getDB();
$db->connect($settings['database']['login'], $settings['database']['pass'], $settings['database']['db']);

//Открыть лог
$log = new \Log(\LogType::CORPUS, ".." . $settings['log']['dir']);

//Открываем документ
$xml = new XMLReader();
$xml->open("../annot.opcorpora.xml", "utf-8");
$log->writeLog("XML Opened successfully, parsing started");

$texts = 0;
$xml->read(); $xml->read();
//Парсим тексты
$texts = 0;
$count = 0;

while ($xml->nodeType != 0)
	if ($xml->nodeType == XMLReader::ELEMENT) {
		if ($xml->name != "text")
			break;
        $curtext = array(
            "text" => "",
            "id" => $texts,
            "paragraphs" => array()
        );
		
		$xml->read(); 
		while ($xml->name != "paragraphs")
			$xml->next();
		
		$xml->read(); $xml->next();
		if ($xml->name == "paragraphs") {
			$xml->next();
			continue;
		}
		
		while ($xml->nodeType != 0)
			if ($xml->nodeType == XMLReader::ELEMENT) {
				if ($xml->name != "paragraph")
					break;

                $curpar = array(
                    "text" => "",
                    "id" => count($curtext['paragraphs']),
                    "sentences" => array()
                );
				
				$xml->read(); $xml->next();
				while ($xml->nodeType != 0)
					if ($xml->nodeType == XMLReader::ELEMENT) {
						if ($xml->name != "sentence")
							break;

                        $cursent = array(
                            "text" => "",
                            "id" => count($curpar['sentences']),
                            "tokens" => array()
                        );

						$xml->read(); $xml->next();
                        $cursent["text"] = $xml->readInnerXml();
						$count++;
						$xml->next();$xml->next();
                        $xml->read();$xml->next();

                        while ($xml->nodeType != 0)
                            if ($xml->nodeType == XMLReader::ELEMENT) {
                                if ($xml->name != "token")
                                    break;
                                //Внутренности token
                                $curtoken = array(
                                    "text" => "",
                                    "id" => count($cursent["tokens"]),
                                    "grammems" => array(),
                                    "lemma_id" => null
                                );
                                $xml->read(); $xml->read(); $xml->read();
                                $curtoken["lemma_id"] = $xml->getAttribute("id");
                                $curtoken["text"] = $xml->getAttribute("t");

                                $xml->read();
                                while ($xml->nodeType != 0) {
                                    if ($xml->nodeType == XMLReader::ELEMENT) {
                                        if ($xml->name != "g")
                                            break;

                                        array_push($curtoken["grammems"], $xml->getAttribute("v"));
                                        $xml->next();
                                    } else
                                        $xml->next();
                                }

                                array_push($cursent["tokens"], $curtoken);

                                while ($xml->name == "v")
                                    $xml->next();
                            } else
                                $xml->next();
                        array_push($curpar['sentences'], $cursent);
                        set_time_limit(30);
					} else
						$xml->next();

                foreach ($curpar['sentences'] as $sent) {
                    $curpar["text"] .= $sent["text"] . " ";
                }
                array_push($curtext['paragraphs'], $curpar);
			} else
				$xml->next();

        foreach ($curtext['paragraphs'] as $par) {
            $curtext["text"] .= $par["text"] . " ";
        }

        //Сохраняем текст в базу данных
        $dbinstance = \Tokenizer\Database::getDB();
        $realid = $dbinstance->saveText($curtext["text"]);
        foreach ($curtext['paragraphs'] as $par) {
            $realpar = new \Tokenizer\Paragraph($par["text"], $par["id"], $realid);
            $realpar->save();

            foreach ($par['sentences'] as $sent) {
                $realsent = new \Tokenizer\Sentence($sent["text"], $sent["id"], $realpar->getParid());
                $realsent->save();

                foreach ($sent['tokens'] as $tok) {
                    $realtok = new \Tokenizer\Token($tok["text"], $realsent->getSenid(), $tok["id"], $tok["lemma_id"]);
                    $realtok->save();
                }
            }
        }

        $texts++;
        if ($texts % 500 == 0) {
            $log->writeLog($texts . " texts parsed");
            echo $texts . " texts parsed\n";
        }
	} else
		$xml->next();
echo "Sentences parsed: " . $count . "\n";
$log->writeLog("Sentences parsed: " . $count);
echo "Texts parsed: " . $texts . "\n";
$log->writeLog("Texts parsed: " . $texts);

?>