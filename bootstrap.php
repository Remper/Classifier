<?php
//Файл-обёртка для всего
require("Tokenizer/bootstrap.php");
require("Learner/bootstrap.php");
require("Features/bootstrap.php");
require("helpers/Log.class.php");

function apath($path) {
	return $_SERVER['DOCUMENT_ROOT'] . $path;
}

