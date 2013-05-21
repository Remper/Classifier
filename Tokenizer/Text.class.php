<?php

namespace Tokenizer;

/**
 * Класс-сущность текст
 * @author Ярослав Нечаев <mail@remper.ru>
 */
class Text extends Entity implements TRWhole {
    protected $opinion;
    protected $wordcount;
    protected $text;
    protected $tokens;
    protected $id;

    /**
     * Конструктор
     *
     * @param string $text текст
     * @param int $textid ID текста (необязательно)
     * @param int $opinion Мнение пользователя о тексте (\Learned\Opinion)
     * @param int $wordcount Количество валидных токенов в тексте
     */
	function __construct($text, $textid = 0, $opinion = 0, $wordcount = 0) {
    	$this->text = $text;
		$this->id = $textid;
        $this->opinion = $opinion;
        $this->wordcount = $wordcount;
        $this->tokens = null;
   	}
	
	/**
	 * Разбить параграф на предложения
	 * 
	 * @return array список предложений
	 */
	public function split() {
		//Разбиваем по двойному переносу строки
		$splitText = preg_split('/\r?\n\r?\n\r?/', $this->text);
		//Фильтруем пустые параграфы и возвращаем параграфы
		$result = array();
		foreach ($splitText as $paragraph)
			if (preg_match('/\S/', $paragraph))
				array_push($result, new Paragraph($paragraph, count($result), $this->id));
			
		return $result;
	}
	
	/**
	 * Сохранить текст
	 * 
	 * @return int ID параграфа
	 */
	public function save() {
		if ($this->isSaved())
			return $this->id;
		
		$dbinstance = Database::getDB();
		return $this->id = $dbinstance->saveText($this);
	}
	
	////
	// Геттеры
	////
	
	/**
	 * Получить массив всех параграфов текста
	 * 
	 * @return array массив текстов, либо false, если текст не сохранён в базу данных
	 */
	public function getPieces() {
		if (!$this->isSaved())
			return false;
	}

    /**
     * Получить список всех токенов текста
     *
     * @return Token[] Список токенов
     */
    public function getTokens() {
        if (!$this->isSaved())
            return false;

        if ($this->tokens !== null)
            return $this->tokens;

        $dbinstance = Database::getDB();
        $this->tokens = $dbinstance->getTokensByTextID($this->id);
        return $this->tokens;
    }

    /**
     * Сохранён ли текст в базу данных
     */
    public function isSaved() {
        return (bool) $this->id;
    }

    public function setWordcount($wordcount)
    {
        $this->wordcount = $wordcount;
    }

    public function getWordcount()
    {
        return $this->wordcount;
    }

    public function setOpinion($opinion)
    {
        $this->opinion = $opinion;
    }

    public function getOpinion()
    {
        return $this->opinion;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getId()
    {
        return $this->id;
    }

    public function equals(Text $text)
    {
        return $text->getId() == $this->getId();
    }

	/**
	 * Очистить текст от плохих символов
	 * 
	 * @param string $text текст для очистки
	 * @return string очищенный текст
	 */
	private function filterText($text) {
		//Запрещённые символы
		$forbid = array(769, //модификатор диакритических символов
			173, //символ возможного переноса
			8192, 8193, 8194, 8195, 8196, 8197, 8198, 8199, 8200, 8201, 8202, 8203, 8237, 8239, 8288, 12288 //Пробелы/символы работы с кареткой
		);
		//Пробегаемся по всему тексту и фильтруем плохие символы
		$result = "";
	    for ($i = 0; $i < mb_strlen($text, 'UTF-8'); ++$i) {
	        $char = mb_substr($text, $i, 1, 'UTF-8');
	        if (!in_array(uniord($char), $forbid)) 
	        	$result .= $char;
	    }
		
		return $result;
	}
}

?>