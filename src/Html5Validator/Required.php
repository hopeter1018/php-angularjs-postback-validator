<?php

namespace Hopeter1018\AngularjsPostbackValidator\Html5Validator;

class Required extends \Hopeter1018\AngularjsPostbackValidator\BaseValidator
{
    private $ngRequired = null;
    
    public function __construct($html, \QueryPath\DOMQuery $input)
    {
    }

    /**
     * 
     * @param string $value
     * @return boolean
     */
    public function validate($value, $posted)
    {
        return isset($value) and strlen($value) > 0;
    }

}
