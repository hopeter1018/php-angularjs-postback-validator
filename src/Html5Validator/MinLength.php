<?php

namespace Hopeter1018\AngularjsPostbackValidator\Html5Validator;

class MinLength extends \Hopeter1018\AngularjsPostbackValidator\BaseValidator
{
    /**
     *
     * @var int
     */
    private $minLength = 0;

    public function __construct($html, \QueryPath\DOMQuery $input)
    {
        $this->minLength = $input->attr('minlength');
    }

    /**
     * 
     * @param string $value
     * @return boolean
     */
    public function validate($value, $posted)
    {
        return strlen($value) >= $this->minLength;
    }

}
