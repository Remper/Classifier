<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features\VectorModel\IDF;

use Tokenizer\Token;
use Tokenizer\Text;

interface AbstractIDF {
    public function calculate(Token $token);
}