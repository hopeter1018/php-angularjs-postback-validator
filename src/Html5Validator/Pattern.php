<?php

namespace Hopeter1018\AngularjsPostbackValidator\Html5Validator;

class Pattern extends \Hopeter1018\AngularjsPostbackValidator\BaseValidator
{
    /**
     *
     * @var int
     */
    private $pattern = 0;

    public function __construct($html, \QueryPath\DOMQuery $input)
    {
        $this->pattern = $input->attr('pattern');
    }

    /**
     * 
     * @param string $value
     * @return boolean
     */
    public function validate($value, $posted)
    {
        return preg_match($this->pattern, $value);
    }

}
