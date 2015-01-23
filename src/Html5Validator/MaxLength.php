<?php

namespace Hopeter1018\AngularjsPostbackValidator\Html5Validator;

class MaxLength extends \Hopeter1018\AngularjsPostbackValidator\BaseValidator
{
    /**
     *
     * @var int
     */
    private $maxLength = 0;

    public function __construct($html, \QueryPath\DOMQuery $input)
    {
        $this->maxLength = $input->attr('maxlength');
    }

    /**
     * 
     * @param string $value
     * @return boolean
     */
    public function validate($value, $posted)
    {
        return strlen($value) <= $this->maxLength;
    }

}
