<?php

include("../bootstrap.php");

error_reporting(E_ALL);

//Основной тест
$config = parse_ini_file("../settings.ini", true);
$tokenizer = new Tokenizer\Tokenizer($config);

$tokenizer->tokenizeText("Фильм неплохой, если бы я не читала книгу,
    оценила бы выше. Но Кинга в принципе экранизировать очень трудно
    так, чтобы передать все эмоции, которые вызывает книга. Фильм все
    же слабее.
");