<?php

namespace Learner;

/**
 * Класс для обучения
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */
class Learner
{
    const SVM = 0;
    const BAYES = 1;

    protected $method;
    protected $options;
    protected $learner;

    /**
     * Конструктор
     *
     * @param $method Метод обучения
     * @param $options Параметры обучения
     */
    public function __construct($method, $options) {
        $this->method = $method;
        $this->options = $options;

        //Parsing method
        switch ($method) {
            case SVM:
                $this->learner = new SVM($options["kernel"], $options["cost"], $options["gamma"]);
            break;
            case BAYES:

            break;
        }
    }

    public function setLearner($learner)
    {
        $this->learner = $learner;
    }

    public function getLearner()
    {
        return $this->learner;
    }

    public function getMethod()
    {
        return $this->method;
    }
}

class Opinion {
    const NEUTRAL = 0;
    const POSITIVE = 1;
    const NEGATIVE = -1;
}