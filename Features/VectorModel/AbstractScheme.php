<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features\VectorModel;

use Tokenizer\Text;
use Tokenizer\Token;

interface AbstractScheme {
    public function calculate(Token $token, Text $text);
}