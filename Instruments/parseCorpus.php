<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>ML-тест</title>
		<meta name="author" content="Yaroslav Nechaev" />
	</head>
	<body>
<?php
/**
 * Скрипт для перегона корпуса из XML в базу данных
 */
 
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
$log = new \Log(\LogType::IMPORT, ".." . $settings['log']['dir']);

//Открываем документ
$xml = new XMLReader();
$xml->open("../annot.opcorpora.xml", "utf-8");
$log->writeLog("XML Opened successfully, parsing started");

$texts = 0;
$xml->read(); $xml->read();
//Парсим тексты
$texts = array();
$count = 0;

while (true)
	if ($xml->nodeType == XMLReader::ELEMENT) {
		if ($xml->name != "text")
			break;
        $curtext = array(
            "text" => "",
            "id" => count($texts),
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
		
		while (true)
			if ($xml->nodeType == XMLReader::ELEMENT) {
				if ($xml->name != "paragraph")
					break;

                $curpar = array(
                    "text" => "",
                    "id" => count($curtext['paragraphs']),
                    "sentences" => array()
                );
				
				$xml->read(); $xml->next();
				while (true)
					if ($xml->nodeType == XMLReader::ELEMENT) {
						if ($xml->name != "sentence")
							break;

                        $cursent = array(
                            "text" => "",
                            "id" => count($curpar['sentences']),
                            "tokens" => array()
                        );
						
						if ($count % 1000 == 0)
							set_time_limit(30);
						$xml->read(); $xml->next();
                        $cursent["text"] = $xml->readInnerXml();
						$count++;
						$xml->next();$xml->next();
                        $xml->read();$xml->next();

                        while (true)
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
                                while (true)
                                    if ($xml->nodeType == XMLReader::ELEMENT) {
                                        if ($xml->name != "g")
                                            break;

                                        array_push($curtoken["grammems"], $xml->getAttribute("v"));

                                        $xml->next();
                                    } else
                                        $xml->next();

                                array_push($cursent["tokens"], $curtoken);

                                while ($xml->name == "v")
                                    $xml->next();
                            } else
                                $xml->next();
                        array_push($curpar['sentences'], $cursent);
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
        array_push($texts, $curtext);
            break;
	} else
		$xml->next();
echo "Sentences parsed: " . $count;
$log->writeLog("Sentences parsed: " . $count);
echo "<br />Texts parsed: " . count($texts);
$log->writeLog("Texts parsed: " . count($texts));

?>
	</body>
</html>