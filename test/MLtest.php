<?php

include("../bootstrap.php");

error_reporting(E_ALL);

//Основной тест
$config = parse_ini_file("../settings.ini", true);
$tokenizer = new Tokenizer\Tokenizer($config);

$tokenizer->tokenizeText("Мама мыла раму. Для этого ей потребовалось много мыла");