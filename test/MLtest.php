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

$sen = new Tokenizer\Sentence("Роняет контр-лес багряный свой убор");
$test = $sen->split(array(), array());
var_dump($test);

?>
	</body>
</html>