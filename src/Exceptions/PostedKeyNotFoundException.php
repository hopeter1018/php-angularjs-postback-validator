<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\AngularjsPostbackValidator\Exceptions;

use Exception;

/**
 * Description of PostedKeyNotFound
 *
 * @todo formatting
 * @version $id$
 * @author peter.ho
 */
class PostedKeyNotFoundException extends Exception
{

    /**
     * @todo formatting
     * @param type $posted
     * @param type $fieldName
     */
    public function __construct($posted, $fieldName)
    {
        ;
    }

}
