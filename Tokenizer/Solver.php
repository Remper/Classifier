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
     */
    public function solve($sentence, $ambiguity) {
        if ($this->type == self::WEB) {
            $graph = $this->askWeb($sentence);
        } else {
            $graph = $this->askLib($sentence);
        }

        var_dump($graph);
        exit(0);
        return $graph;
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
    private function askWeb($sentence) {
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
