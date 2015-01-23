<?php

namespace Hopeter1018\AngularjsPostbackValidator\Html5Validator;

class Max extends \Hopeter1018\AngularjsPostbackValidator\BaseValidator
{
    /**
     *
     * @var int
     */
    private $max = 0;

    public function __construct($html, \QueryPath\DOMQuery $input)
    {
        $this->max = $input->attr('max');
    }

    /**
     * 
     * @param int|double $value
     * @return boolean
     */
    public function validate($value, $posted)
    {
        return $value === '' or $value === null or $value <= $this->max;
    }

}
