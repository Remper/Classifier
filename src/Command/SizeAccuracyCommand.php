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
use Tokenizer\Features\VectorModel\TF\TF;
use Tokenizer\Features\VectorModel\TFIDF;
use Tokenizer\Tokenizer;


class SizeAccuracyCommand extends Command {
    protected function configure()
    {
        $this
            ->setName('diploma:sizeAccuracy')
            ->setDescription('Проследить зависимость точности от размера выборки')
            ->addArgument(
                'tf',
                InputArgument::OPTIONAL,
                'Какую TF использовать'
            )
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
        $log = new \Log(\LogType::LEARNER, "." . $config['log']['dir'], $output, true);

        $log->writeLog("System ready, starting Learner");

        $tokenizer = new Tokenizer($config);

        $vm = new VectorModel();
        $tftype = $input->getArgument('tf');
        if (!$tftype)
            $tftype = "tf";
        $tftype = strtolower($tftype);

        switch ($tftype) {
            default:
            case "tf":
                $vm->getScheme()->setTf(TFIDF::TF_TF);
                break;
            case "bin":
                $vm->getScheme()->setTf(TFIDF::TF_BIN);
                break;
            case "log":
                $vm->getScheme()->setTf(TFIDF::TF_LOG);
                break;
            case "tflog":
                $vm->getScheme()->setTf(TFIDF::TF_TFLOG);
                break;
        }
        $log->writeLog("Local weight: ".$tftype);

        $dbinstance = Database::getDB();

        $log->writeLog("Caching IDF");

        $count = 0;
        $tokens = $dbinstance->getTokensFromValuableTexts(0, 500000);
        $log->writeLog("Memory limit: " . ini_get('memory_limit') . "B. Used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");
        $idf = $vm->getScheme()->getIdf();
        while (count($tokens) != 0) {
            foreach ($tokens as $token) {
                $idf->fillCache($token["token"], $token["count"]);
                $count++;
                if ($count % 5000 == 0) {
                    $log->writeLog($count . " parsed");
                }
            }

            $tokens = null;
            $log->writeLog("Memory used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");
            $tokens = $dbinstance->getTokensFromValuableTexts($count, 500000);
        }

        $log->writeLog("Calculating TFIDF");

        $count = 0;
        $positive = 0;
        $texts = $dbinstance->getAllValuableTexts(0, 500);
        $sizes = array(
             50, 100, 200, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000, 2500, 4000, 5000, 7000, 9000, 11000, 12000, 100500
        );
        $weights = array();
        $files = array();
        foreach ($sizes as $size) {
            $files[$size] = fopen("models/model_". $tftype ."_idf_". $size .".txt", "w");
        }
        while (count($texts) != 0) {
            foreach ($texts as $text) {
                $label  = "-1";
                if ($text->getOpinion() > 6) {
                    $positive++;
                    $label = "+1";
                }
                foreach ($files as $size => $file) {
                    fwrite($file, $label);
                }

                $vector = $vm->calculateFeatures($text);
                ksort($vector);
                foreach($vector as $key => $value) {
                    foreach ($files as $size => $file) {
                        fwrite($file, " " . $key . ":" . number_format($value, 4, ".", " "));
                    }
                }
                foreach ($files as $size => $file) {
                    fwrite($file, "\n");
                    if ($size <= $count + 1) {
                        fclose($file);
                        unset($files[$size]);
                        $weights[$size] = $this->getWeights($count, $positive);
                        $log->writeLog("Model model_".$tftype."_idf_". $size .".txt calculated");
                    }
                }
                unset($vector);

                $count++;
                if ($count % 100 == 0) {
                    $log->writeLog($count . " parsed");
                }
            }

            $texts = null;
            $vm->getScheme()->clearCache();
            $log->writeLog("Memory used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");
            $texts = $dbinstance->getAllValuableTexts($count, 500);
        }
        foreach ($files as $size => $file) {
            fclose($file);
            $weights[$size] = $this->getWeights($count, $positive);
        }

        $log->writeLog("Cleaning up");

        $texts = null;
        $vm = null;
        $files = null;

        $log->writeLog("Memory used: " . number_format((memory_get_usage()/1024)/1024, 1, ".", " ") . "MB");

        foreach ($sizes as $size) {
            $log->writeLog("Starting LIBLINEAR cross-validation for size: " . $size);
            $log->writeLog("Weights: " . number_format($weights[$size][0], 2, ".", " ") . " " . number_format($weights[$size][1], 2, ".", " "));
            $types = array(1,3,4);
            foreach ($types as $typeKey => $typeValue) {
                $log->writeLog($typeValue . ": " . exec("train -s ". $typeValue ." -c 4 -e 0.1 -v 5 -w+1 ". $weights[$size][0] ." -w-1 ". $weights[$size][1] ." models/model_".$tftype."_idf_". $size .".txt"));
            }
        }

        $log->writeLog("Done in: " . number_format(microtime(true) - $start_time, 4, ".", " ") . " seconds");
    }

}