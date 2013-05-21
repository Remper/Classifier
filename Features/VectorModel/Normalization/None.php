<?php
/**
 * Some new PHP file
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */

namespace Tokenizer\Features\VectorModel\Normalization;


class None implements AbstractNormalization {

    public function calculate()
    {
        return 1.0;
    }
}