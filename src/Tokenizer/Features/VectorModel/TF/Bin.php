<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features\VectorModel\TF;


use Tokenizer\Text;
use Tokenizer\Token;

class Bin implements AbstractTF {

    public function calculate(Token $token, Text $text)
    {
        return 1.0;
    }

    public function clearCache()
    {

    }
}