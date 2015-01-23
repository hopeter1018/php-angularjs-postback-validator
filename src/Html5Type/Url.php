<?php

namespace Hopeter1018\AngularjsPostbackValidator\Html5Type;

class Url extends \Hopeter1018\AngularjsPostbackValidator\BaseValidator
{

    public function __construct($html, \QueryPath\DOMQuery $input)
    {
        return null;
    }

    public function validate($value, $posted)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

}
