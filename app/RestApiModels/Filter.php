<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 1/6/16
 * Time: 12:48
 */

namespace App\RestApiModels;


class Filter
{
    protected $index, $operator, $value;

    /**
     * Filter constructor.
     * @param $index
     * @param $operator
     * @param $value
     */
    public function __construct($index, $operator, $value)
    {
        $this->index = $index;
        $this->operator = $operator;
        $this->setValue($value);
    }


    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param mixed $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        if(is_numeric($value)) {
            if (is_int($value)) {
                $value = (int)$value;
            }
            $value = (float)$value;
        }
        $this->value = $value;
    }


}