<?php

namespace Tokenizer;

/**
 * Класс-сущность токен
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Token extends Entity implements TRPiece {
    //ID токена
    private $id;

    //ID предложения-родителя
    private $senid;

    //ID леммы
    private $lemma_id;

    //ID формы этой леммы
    private $form_id;

    //Текст токена
    private $text;

    //Порядок в предложении
    private $order;

    //Проверен ли токен
    private $checked;

    //Метод которым было разбито предложение
    private $method;

    private $text_id;

    public function __construct($text, $senid, $order, $lemma_id = 0, $form_id = 0, $checked = true, $method = Methods::CORPUS) {
        $this->text = $text;
        $this->senid = $senid;
        $this->order = $order;
        $this->lemma_id = $lemma_id;
        $this->form_id = $form_id;
        $this->checked = $checked;
        $this->method = $method;
        $this->text_id = 0;
    }

    /**
     * Сохранить токен в базу данных
     *
     * @param int $senid ID предложения
     * @return int ID токена в базе
     */
    public function save($senid = 0)
    {
        if ($this->isSaved())
            return false;

        if ($this->senid == 0) {
            if ($senid == 0)
                return false;

            $this->senid = $senid;
        }

        $dbinstance = Database::getDB();
        return $this->id = $dbinstance->saveToken($this);
    }

    public function isChecked()
    {
        return $this->checked;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getLemmaId()
    {
        return $this->lemma_id;
    }

    public function getUniqueId()
    {
        return $this->lemma_id+$this->form_id*10000000+1;
    }

    public function equals(Token $token)
    {
        return $this->getUniqueId() == $token->getUniqueId();
    }

    public function getFormId()
    {
        return $this->form_id;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getParentId()
    {
        return $this->senid;
    }

    public function setParentId($id)
    {
        $this->senid = $id;
    }

    /**
     * @param mixed $text_id
     */
    public function setTextId($text_id)
    {
        $this->text_id = $text_id;
    }

    /**
     * @return mixed
     */
    public function getTextId()
    {
        return $this->text_id;
    }
}

class Methods {
    const CORPUS = 1;
    const ML = 2;
    const RULES = 3;
}

?>