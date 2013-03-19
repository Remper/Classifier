<?php

namespace Tokenizer;

/**
 * Ambiguity solver
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */
class Solver
{
    const WEB = 0;
    const LIBRARY = 1;
    const CACHE = 2;

    protected $type;
    protected $save;

    /**
     * Construct
     *
     * @param int $type How to access AOT solver
     * @param boolean $save Should we save raw data?
     */
    public function __construct($type = self::WEB, $save = true) {
        $this->type = $type;
        $this->save = $save;
    }

    /**
     * Solve ambiguity
     *
     * @param Sentence $sentence Original sentence
     * @param Array $ambiguity Ambiguity variants
     * @return Token[] sentence with solved ambiguity
     */
    public function solve($sentence, $ambiguity) {
        //Database
        $db = Database::getDB();

        //Getting raw graph
        switch ($this->type) {
            case self::WEB:
                $graph = $this->askWeb($sentence);
                break;
            default:
            case self::LIBRARY:
                $graph = $this->askLib($sentence);
                break;
            case self::CACHE:
                $graph = $db->getRawData($sentence->getSenid());
                if ($graph == false) {
                    $graph = "";
                } else {
                    $graph = $graph["data"];
                }
                break;
        }

        //Parsing graph
        $graph = $this->parseGraph($graph);

        //Solving ambiguity
        $result = array();
        foreach ($ambiguity as $ambtok) {
            if (count($ambtok) == 1) {
                $result[] = $ambtok[0]->convertToToken($sentence->getSenid(), count($result));
                continue;
            }

            if (!isset($ambtok[0])) {
                var_dump($ambiguity);
                exit(0);
            }
            $word = $ambtok[0];
            $pos = array_search(mb_strtoupper($word->getText(), "UTF-8"), $graph[1]);

            if ($pos === false) {
                //Плохой случай — не получили новой информации
                $result[] = $ambtok[0]->convertToToken($sentence->getSenid(), count($result));
                continue;
            }

            $grammems = $graph[5][$pos] == "" ? array() : explode(",", trim($graph[5][$pos], ","));

            //Парсим часть речи
            $filter = new SolvFilter($graph[4][$pos], $grammems);
            $cands = array_filter($ambtok, array($filter, "filterCandidate"));

            if (count($cands) == 0) {
                //Плохой случай — всё пришло в когнитивный диссонанс
                $result[] = $ambtok[0]->convertToToken($sentence->getSenid(), count($result));
                continue;
            }

            $res = array_shift($cands);
            $result[] = $res->convertToToken($sentence->getSenid(), count($result));
        }

        return $result;
    }

    /**
     * Parse graph
     *
     * @param string $graph raw graph data
     * @return array Parsed data
     */
    private function parseGraph($graph) {
        $matches = array();
        preg_match_all("/#label ([^ #]+)( [^#]*)?#Morphology ([^ ]+) =   ([^ ]+) ([^$]+)?/i", $graph, $matches);
        return $matches;
    }

    /**
     * Get graph from AOT lib
     *
     * @param Sentence $sentence Original sentence
     * @return string Graph information
     */
    private function askLib($sentence) {
        return "meow";
    }

    /**
     * Get graph from AOT web
     *
     * @param Sentence $sentence Original sentence
     * @return string Graph information
     */
    public function askWeb($sentence) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://aot.ru/cgi-bin/translate.cgi');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "TemplateFile=../wwwroot/demo/graph.html&submit1=test&action=graph&russian="
            .mb_convert_encoding($sentence->getText(), "Windows-1251", "UTF-8"));

        $out = curl_exec($curl);
        $pos = strpos($out, '<param name="graph" value="')+27;
        $endpos = strpos($out, '">', $pos);
        $out = mb_convert_encoding(substr($out, $pos, $endpos-$pos), "UTF-8", "Windows-1251");

        if ($this->save) {
            $db = Database::getDB();
            $db->addRawData($sentence->getSenid(), $out);
        }

        curl_close($curl);

        return $out;
    }
}

class SolvFilter {
    private $part;
    private $gramemms;
    private $forbidden;

    public function __construct($part, $grammems) {
        $this->part = $this->parsePart($part);
        $this->gramemms = array_map(array($this, "mapGrammems"), $grammems);
        $this->forbidden = array("nomn", "gent", "datv", "accs", "ablt", "loct", "voct", "masc", "femn", "neut", "sing", "plur");
        $this->forbidden = array_diff($this->forbidden, $this->gramemms);
    }

    /**
     * Filter candidate forms
     *
     * @param Form $cand
     * @return bool is it good
     */
    public function filterCandidate($cand) {
        if (strpos($cand->getGrammems(), $this->part) === false) {
            return false;
        }

        foreach ($this->forbidden as $forbidden) {
            if (strpos($cand->getGrammems(), $forbidden) !== false) {
                return false;
            }
        }

        return true;
    }

    public function mapGrammems($gram) {
        return $this->parseGrammem($gram);
    }

    /**
     * Get OpenCorpora POST grammem from AOT part
     *
     * @param string $part AOT part
     * @return string OpenCorpora POST grammem
     */
    private function parsePart($part) {
        return strtr($part, array(
            "С" => "NOUN",
            "П" => "ADJF",
            "МС" => "NPRO",
            "Г" => "VERB",
            "ПРИЧАСТИЕ" => "PRTF",
            "ДЕЕПРИЧАСТИЕ" => "GRND",
            "ИНФИНИТИВ" => "INFN",
            "МС-ПРЕДК" => "NPRO",
            "МС-П" => "ADJF",
            "ЧИСЛ" => "NUMR",
            "ЧИСЛ-П" => "NUMR",
            "Н" => "ADVB",
            "ПРЕДК" => "PRED",
            "ПРЕДЛ" => "PREP",
            "СОЮЗ" => "CONJ",
            "МЕЖД" => "INTJ",
            "ЧАСТ" => "PRCL",
            "ВВОДН" => "PRCL",
            "КР_ПРИЛ" => "ADJS",
            "КР_ПРИЧАСТИЕ" => "PRTS"
        ));
    }

    /**
     * Get OpenCorpora grammem from AOT grammem
     *
     * @param string $grammem AOT
     * @return string OpenCorpora
     */
    private function parseGrammem($grammem) {
        return strtr($grammem, array(
            "но" => "inan",
            "од" => "anim",
            "мр" => "masc",
            "жр" => "femn",
            "ср" => "neut",
            "им" => "nomn",
            "рд" => "gent",
            "дт" => "datv",
            "вн" => "accs",
            "тв" => "ablt",
            "пр" => "loct",
            "зв" => "voct",
            "ед" => "sing",
            "мн" => "plur"
        ));
    }
}