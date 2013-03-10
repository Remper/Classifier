<?php

namespace Tokenizer;

/**
 * Class for tokenization via mystem
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */
class Mystem
{
    const AMBIG_YES = 0;
    const AMBIG_NO = 1;

    protected $ambiguity;

    /**
     * Constructor
     *
     * @param int $ambiguity Should we treat ambiguity
     */
    public function __construct($ambiguity = self::AMBIG_NO) {
        $this->ambiguity = $ambiguity;
    }

    /**
     * Run tokenization flor given sentence
     *
     * @param Sentence $sentence
     * @return Token[] Result in array of tokens
     */
    public function run($sentence) {
        //Разбиваем по точке, переносов строк быть не должно
        $q = iconv("utf-8", "windows-1251", $sentence->getText());
        exec('echo "'.$q.'" | ../helpers/mystem -ni', $tokens);

        $parsedTokens = array();
        foreach ($tokens as $token) {
            $parsedTokens[] = $this->parseToken(iconv("windows-1251", "utf-8", $token));
        }

        if ($this->ambiguity == self::AMBIG_NO) {
            $result = array();
            foreach ($parsedTokens as $token) {
                $result[] = $token[0];
            }
        } else {
            $sol = new Solver();
            $result = $sol->solve($sentence, $parsedTokens);
        }

        return $result;
    }

    /**
     * Parse token
     *
     * @param string $input Mystem output
     * @return Form[] Array of forms
     */
    private function parseToken($input) {
        $part = 0;
        $result = array();
        for ($i = 0; $i < mb_strlen($input, 'UTF-8'); $i++) {
            $letter = mb_substr($input, $i, 1, 'UTF-8');
            switch ($letter) {
                case "{":
                    $part++;
                    break;
                case "|":
                    $part++;
                    break;
                case "}":
                    break;
                default:
                    if (!isset($result[$part]))
                        $result[$part] = $letter;
                    else
                        $result[$part] .= $letter;
            }
        }

        $dbinstance = Database::getDB();
        $forms = $dbinstance->findLemma($result[0]);

        return $forms;
    }
}
