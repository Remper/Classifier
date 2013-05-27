<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features;

use Tokenizer\Features\VectorModel\AbstractScheme;
use Tokenizer\Text;
use Tokenizer\Token;

class VectorModel {
    const TFIDF = "TFIDF";

    /**
     * @var AbstractScheme
     */
    private $scheme;

    public function __construct($scheme = self::TFIDF) {
        $this->setScheme($scheme);
    }

    public function setScheme($scheme)
    {
        $schemeName = __CLASS__ . "\\" . $scheme;
        $this->scheme = new $schemeName();
    }

    /**
     * @return AbstractScheme
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    public function calculateFeatures(Text $text) {
        $features = array();
        foreach ($text->getTokens() as $token) {
            $features[$token->getUniqueId()] = $this->scheme->calculate($token, $text);
        }
        unset($features[1]);
        return $features;
    }
}