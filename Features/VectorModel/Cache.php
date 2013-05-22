<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features\VectorModel;

use Tokenizer\Text;
use Tokenizer\Token;

class Cache {
    const FULL = 0;
    const TOKEN = 1;

    /**
     * @var CacheItem[]
     */
    private $cache;
    private $type;

    public function __construct($type) {
        $this->type = $type;
        $this->cache = array();
    }

    /**
     * @param Token $token
     * @param Text|null $text\
     * @return bool
     */
    public function isMatch($token, $text = null) {
        $id = $token->getUniqueId();

        if (!isset($this->cache[$id]))
            return false;

        $cache = $this->cache[$id];

        if ($this->type == self::TOKEN)
            return true;

        if ($text->getText() != $cache->text)
            return false;

        return true;
    }

    /**
     * @param Token $token
     * @param Text|null $text
     * @return int
     */
    public function getValue($token, $text = null) {
        return $this->cache[$token->getUniqueId()]->value;
    }

    /**
     * @param Token $token
     * @param Text|null $text
     */
    public function addValue($token, $text, $value) {
        $this->cache[$token->getUniqueId()] = new CacheItem($text == null ? null : $text->getText(), $value);
    }
}

class CacheItem {
    public $text;
    public $value;

    public function __construct($text = null, $value) {
        $this->text = $text;
        $this->value = $value;
    }
}