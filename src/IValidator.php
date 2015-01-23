<?php

namespace Hopeter1018\AngularjsPostbackValidator;

interface IValidator
{

    public function validate($value, $posted);

    public function toJsonArray();

}
