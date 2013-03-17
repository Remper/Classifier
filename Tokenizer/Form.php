<?php

namespace Tokenizer;

/**
 * Form of the lemma
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */
class Form
{
    protected $lemmaid;
    protected $formid;
    protected $text;
    protected $grammems;

    public function __construct($lemmaid, $formid, $text, $grammems) {
        $this->lemmaid = $lemmaid;
        $this->formid = $formid;
        $this->text = $text;
        $this->grammems = $grammems;
    }

    public function setFormid($formid)
    {
        $this->formid = $formid;
    }

    public function getFormid()
    {
        return $this->formid;
    }

    public function setGrammems($grammems)
    {
        $this->grammems = $grammems;
    }

    public function getGrammems()
    {
        return $this->grammems;
    }

    public function setLemmaid($lemmaid)
    {
        $this->lemmaid = $lemmaid;
    }

    public function getLemmaid()
    {
        return $this->lemmaid;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

    /**
     * Convert form to the corresponding token
     *
     * @param int $senid
     * @param int $order
     * @return Token
     */
    public function convertToToken($senid, $order) {
        return new Token($this->text, $senid, $order, $this->lemmaid, $this->formid);
    }
}
