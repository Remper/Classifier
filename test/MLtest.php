<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>ML-тест</title>
		<meta name="author" content="Yaroslav Nechaev" />
	</head>
	<body>
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

?>
	</body>
</html>