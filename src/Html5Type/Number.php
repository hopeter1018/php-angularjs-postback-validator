<?php

namespace Hopeter1018\AngularjsPostbackValidator\Html5Type;

class Number extends \Hopeter1018\AngularjsPostbackValidator\BaseValidator
{

    public function __construct($html, \QueryPath\DOMQuery $input)
    {
        return null;
    }

    public function validate($value, $posted)
    {
        return $value === '' or $value === null or is_numeric($value);
    }

}
