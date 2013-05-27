<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features\VectorModel\IDF;

use Tokenizer\Text;
use Tokenizer\Token;

class None implements AbstractIDF {

    public function calculate(Token $token)
    {
        return 1.0;
    }
}