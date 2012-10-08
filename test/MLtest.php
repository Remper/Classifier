<?php

include("../bootstrap.php");

$sen = new Tokenizer\Sentence("Роняет лес багряный свой убор");
$test = $sen->split(array(), array());
var_dump($test);

?>