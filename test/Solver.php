<?php

include("../bootstrap.php");
header('Content-type: text/html; charset=utf-8');

//Тест токенизатора
$sen = new Tokenizer\Sentence("Роняет контр-лес багряный свой убор", 0);
$test = $sen->split(array(), array());
//var_dump($test);

//Основной тест
$config = parse_ini_file("../settings.ini", true);
$tokenizer = new Tokenizer\Tokenizer($config);

