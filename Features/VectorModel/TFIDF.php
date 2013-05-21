<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features\VectorModel;


use Tokenizer\Features\VectorModel\IDF\AbstractIDF;
use Tokenizer\Features\VectorModel\Normalization\AbstractNormalization;
use Tokenizer\Features\VectorModel\TF\AbstractTF;
use Tokenizer\Text;
use Tokenizer\Token;

class TFIDF implements AbstractScheme {
    const TF_TF = "TF\\TF";
    const TF_ATF1 = "TF\\ATF1";
    const TF_BIN = "TF\\Bin";
    const TF_LOG = "TF\\Log";
    const TF_LOGN = "TF\\LOGN";
    const IDF_IDF = "IDF\\IDF";
    const IDF_IDFP = "IDF\\IDFP";
    const IDF_DELTA = "IDF\\DeltaIDF";
    const IDF_NONE = "IDF\\NONE";
    const IDF_RF = "IDF\\RF";
    const NRM_NONE = "Normalization\\None";

    /**
     * @var AbstractTF
     */
    private $tf;
    /**
     * @var AbstractIDF
     */
    private $idf;
    /**
     * @var AbstractNormalization
     */
    private $norm;
    /**
     * @var Cache
     */
    private $cache;

    public function __construct($tf = self::TF_TF, $idf = self::IDF_IDF, $normalization = self::NRM_NONE)
    {
        $this->setTf($tf);
        $this->setIdf($idf);
        $this->setNorm($normalization);
        $this->cache = new Cache(Cache::FULL);
    }

    public function calculate(Token $token, Text $text)
    {
        if ($this->cache->isMatch($token, $text)) {
            return $this->cache->getValue($token, $text);
        } else {
            $value = $this->tf->calculate($token, $text) * $this->idf->calculate($token) * $this->norm->calculate();
            $this->cache->addValue($token, $text, $value);
            return $value;
        }
    }

    public function setIdf($idf = self::IDF_IDF)
    {
        $idfName = __NAMESPACE__ . "\\" . $idf;
        $this->idf = new $idfName();
    }

    public function getIdf()
    {
        return $this->idf;
    }

    public function setNorm($norm = self::NRM_NONE)
    {
        $normName = __NAMESPACE__ . "\\" . $norm;
        $this->norm = new $normName();
    }

    public function getNorm()
    {
        return $this->norm;
    }

    public function setTf($tf = self::TF_TF)
    {
        $tfName = __NAMESPACE__ . "\\" . $tf;
        $this->tf = new $tfName();
    }

    public function getTf()
    {
        return $this->tf;
    }
}