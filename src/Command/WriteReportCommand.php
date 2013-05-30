<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Tokenizer\Database;
use Tokenizer\Features\VectorModel;
use Tokenizer\Features\VectorModel\IDF\IDFP;
use Tokenizer\Tokenizer;


class WriteReportCommand extends Command {
    protected function configure()
    {
        $this
            ->setName('diploma:writeReport')
            ->setDescription('Просчитать все модели и выгрузить отчёт в gnuplot')
        ;
    }

    private function getWeights($count, $positive)
    {
        if ($positive > $count-$positive) {
            $posRate = ($count-$positive) / $count;
            $negRate = 1 - $posRate;
        } else {
            $negRate = $positive / $count;
            $posRate = 1 - $negRate;
        }

        return array($posRate, $negRate);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Замеряем начальное время
        $start_time = microtime(true);

        //Открыть лог
        $config = parse_ini_file("settings.ini", true);
        $log = new \Log(\LogType::INSTRUMENT, "." . $config['log']['dir'], $output, true);

        $log->writeLog("System ready, starting Learner");

        $dir = dir("models");
        $models = array();
        while (false !== ($entry = $dir->read())) {
            if (strpos($entry, "model") !== false) {
                $file = $entry;
                $entry = explode(".", $entry);
                $entry = explode("_", $entry[0]);
                if (count($entry) == 4) {
                    $key = $entry[1] . "." . $entry[2];
                    if (!isset($models[$key])) {
                        $models[$key] = array();
                    }
                    $models[$key][$entry[3]] = $file;
                }
            }
        }

        foreach ($models as $key => $model) {

            $report = fopen("reports/". $key .".txt", "w");
            fwrite($report, "#Accuracy	Size\n");
            ksort($model);
            foreach ($model as $size => $filename) {
                $file = fopen("models/".$filename, "r");
                $pos = 0;
                $count = 0;
                while ($string = fgets($file)) {
                    if (strpos($string, "+1") === 0) {
                        $pos++;
                    }
                    $count++;
                }
                fclose($file);
                $weights = $this->getWeights($count, $pos);
                $result = exec("train -s 1 -c 4 -e 0.1 -v 5 -w+1 ". $weights[0] ." -w-1 ". $weights[1] ." models/" . $filename);
                if (strpos($result, "Cross Validation") === 0) {
                    $log->writeLog("Weights: " . number_format($weights[0], 2, ".", " ") . " " . number_format($weights[1], 2, ".", " "));
                    $log->writeLog($key . " (" . $size . "): " . $result);

                    $acc = (float) strtr($result, array(
                        "Cross Validation Accuracy = " => "",
                        "%" => ""
                    ));
                    $acc = ($acc + 5) / 100;
                    fwrite($report, $acc . "	" . $size . "\n");
                } else {
                    $log->writeLog($key . " (" . $size . ") error: " . $result);
                }
            }
            fclose($report);
        }

        $log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");
    }
}