#!/usr/bin/env php
<?php

/**
 * Entry point for diploma
 */

error_reporting(E_ALL);
date_default_timezone_set("UTC");
ini_set('memory_limit', '2048M');

require_once 'vendor/autoload.php';

use Command\SizeAccuracyCommand;
use Command\TFIDFCommand;
use Command\WriteReportCommand;
use Symfony\Component\Console\Application;
use Tokenizer\Tokenizer;

$application = new Application("Diploma", "1.0");
$application->add(new TFIDFCommand);
$application->add(new SizeAccuracyCommand);
$application->add(new WriteReportCommand);
$application->run();