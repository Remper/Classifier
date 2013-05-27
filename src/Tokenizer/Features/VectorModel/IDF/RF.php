<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features\VectorModel\IDF;

use Tokenizer\Database;
use Tokenizer\Features\VectorModel\Cache;
use Tokenizer\Text;
use Tokenizer\Token;

class RF implements AbstractIDF {
    /**
     * @var Cache
     */
    private $cache;
    private $valuableTextsCount;

    public function __construct()
    {
        $this->cache = new Cache(Cache::TOKEN);
        $this->valuableTextsCount = 0;
    }

    public function calculate(Token $token)
    {
        if ($this->cache->isMatch($token)) {
            return $this->cache->getValue($token);
        }

        $this->cacheValuableTextCount();

        $dbinstance = Database::getDB();
        $value = log(2 + $this->valuableTextsCount / $dbinstance->getTextsCountWithToken($token));
        $this->cache->addValue($token, null, $value);
        return $value;
    }

    /**
     * @param \Tokenizer\Features\VectorModel\Cache[] $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return \Tokenizer\Features\VectorModel\Cache[]
     */
    public function getCache()
    {
        return $this->cache;
    }

    public function fillCache($token, $count)
    {
        $this->cacheValuableTextCount();
        $value = log(2 + $this->valuableTextsCount / $count);
        $this->cache->addValue($token, null, $value);
    }

    private function cacheValuableTextCount() {
        if ($this->valuableTextsCount == 0) {
            $dbinstance = Database::getDB();
            $this->valuableTextsCount = $dbinstance->getValuableTextsCount();
        }
    }
}