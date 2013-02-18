<?php

namespace Tokenizer;

/**
 * Класс-сущность граммема
 *
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Grammem {
    const BASIC_GRAMMEM = 1;

    protected $id;
    protected $parent;
    protected $name;

    /**
     * Конструктор
     *
     * @param int $id
     * @param int $parent
     * @param string $name
     */
    public function __construct($id, $parent, $name) {
        $this->id = $id;
        $this->parent = $parent;
        $this->name = $name;
    }

    /**
     * Добавить граммему в базу данных
     *
     * @param string $name Имя грамемы
     * @param int $parent Родитель граммемы (необязательно)
     */
    public static function createGrammem($name, $parent = 0) {
        $dbinstance = Database::getDB();
        $id = $dbinstance->addGrammem($parent, $name);
        return new self($id, $parent, $name);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Получить базовые граммемы (с родителем POST)
     *
     * @return Grammem[] Массив граммем
     */
    public static function getBasicGrammems()
    {
        $dbinstance = Database::getDB();
        return $dbinstance->findGrammemsByParent(self::BASIC_GRAMMEM);
    }
}
