<?php
//Файл-обёртка для всего
require("Tokenizer/bootstrap.php");
require("Learner/bootstrap.php");

function apath($path) {
	return $_SERVER['DOCUMENT_ROOT'] . $path;
}

?>