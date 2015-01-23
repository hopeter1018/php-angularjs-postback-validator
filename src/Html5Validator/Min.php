<?php

namespace Hopeter1018\AngularjsPostbackValidator\Html5Validator;

class Min extends \Hopeter1018\AngularjsPostbackValidator\BaseValidator
{
    /**
     *
     * @var int
     */
    private $min = 0;

    public function __construct($html, \QueryPath\DOMQuery $input)
    {
        $this->min = $input->attr('min');
    }

    /**
     * 
     * @param int|double $value
     * @return boolean
     */
    public function validate($value, $posted)
    {
        return $value === '' or $value === null or $value >= $this->min;
    }

}
