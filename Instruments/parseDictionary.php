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
 * Скрипт для перегона морфологии из XML в базу данных
 */
 
require("../Tokenizer/Database.class.php");

//Замеряем начальное время
$start_time = microtime(true);

//Получить настройки базы данных
$settings = parse_ini_file("../settings.ini", true);

//Создать инстанс базы данных и подключиться
$db = \Tokenizer\Database::getDB();
$db->connect($settings['database']['login'], $settings['database']['pass'], $settings['database']['db']);

//Открываем документ
$xml = new XMLReader();
$xml->open("../dict.opcorpora.xml", "utf-8");

//Перемещаемся на уровень лемм
$xml->read(); $xml->read();
$nodename = $xml->name;
while ($nodename != "lemmata") {
	$xml->next();
	$nodename = $xml->name;
}
$xml->read();

//Идём по всем леммам
$lcount = 0;
$fcount = 0;
while ($xml->next())
	if ($xml->nodeType == XMLReader::ELEMENT) {
		if ($xml->name != "lemma" || $lcount == 100000)
			break;
		$lcount++;
		//Парсим лемму
		//Указываем ID леммы и инициализируем ID формы
		$lid = $xml->getAttribute("id");
		$fid = 0;
		//Расширяем до ноды
		$element = $xml->expand();
		$lgram = array();
		foreach($element->childNodes as $node) {
			//Все не словоформные ноды отсекаем
			if ($node->nodeName != "f") {
				//Для ноды леммы собираем все граммемы
				if ($node->nodeName == "l")
					foreach($node->childNodes as $gram)
						array_push($lgram, $gram->attributes->getNamedItem("v")->nodeValue);
					
				continue;
			}
			
			$fcount++;
			//Собираем граммемы словоформы
			$grammems = array();
			foreach($node->childNodes as $gram)
				array_push($grammems, $gram->attributes->getNamedItem("v")->nodeValue);
			//Соединяем с граммемами леммы
			$grammems = json_encode(array_merge($grammems, $lgram));
			$text = $node->attributes->getNamedItem("t")->nodeValue;
			
			//Сохраняем в базу данных
			$db->addLemma($lid, $fid, $text, $grammems);
			$fid++;
		}
	}

//Выводим статистику
echo "Lemma count: " . $lcount;
echo "<br />Form count: " . $fcount;
echo "<br />Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds";
?>
	</body>
</html>