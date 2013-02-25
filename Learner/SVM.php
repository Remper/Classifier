<?php

namespace Learner;

/**
 * Класс для обучения на основе метода опорных векторов (SVM)
 *
 * @author Yaroslav Nechaev <remper@me.com>
 */
class SVM
{
    const LINEAR = 0;
    const POLY = 1;
    const SIGMOID = 3;
    const RBF = 2;

    protected $gamma;
    protected $cost;
    protected $kernel;
    protected $svm;
    protected $model;

    /**
     * Конструктор
     *
     * @param int $kernel Ядро
     * @param float $cost Параметр настройки
     * @param float $gamma Параметр гамма для полиномиального (POLY), сигмоидного (SIGMOID) или функции Гаусса (RBF) ядра
     */
    public function __construct($kernel, $cost, $gamma) {
        $this->gamma = $gamma;
        $this->cost = $cost;
        $this->kernel = $kernel;
        $this->model = null;

        //Инициализируем SVM
        $this->svm = new \SVM();
        //Записываем настройки
        $settings = array(
            \SVM::OPT_KERNEL_TYPE => $kernel,
            \SVM::OPT_C => $cost
        );
        if ($kernel != LINEAR) {
            $settings[\SVM::OPT_GAMMA] = $gamma;
        }
        $this->svm->setOptions($settings);
    }

    /**
     * Обучить SVM по выборке
     *
     * @param $problem Данные
     */
    public function train($problem) {
        $this->model = $this->svm->train($problem);
    }

    /**
     * Предсказать класс
     *
     * @param $value Значение
     * @return bool|int Класс
     */
    public function predict($value) {
        if ($this->model == null)
            return false;

        return $this->model->predict($value);
    }

    /**
     * Провести скользящий контроль
     *
     * @param $problem Выборка
     * @param int $folds На сколько частей делить выборку
     * @return float Точность
     */
    public function crossvalidate($problem, $folds) {
        return $this->svm->crossvalidate($problem, $folds);
    }

    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getKernel()
    {
        return $this->kernel;
    }

    public function setSvm($svm)
    {
        $this->svm = $svm;
    }

    public function getSvm()
    {
        return $this->svm;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }
}
