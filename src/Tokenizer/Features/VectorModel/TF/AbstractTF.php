<?php
/**
 * Interface for implementing Term Frequency
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features\VectorModel\TF;

use Tokenizer\Token;
use Tokenizer\Text;


interface AbstractTF {
    public function calculate(Token $token, Text $text);

    public function clearCache();
}