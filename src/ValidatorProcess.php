<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\AngularjsPostbackValidator;

/**
 * Description of validate
 *
 * @todo zmsForm directive "server"
 * @author peter.ho
 */
class ValidatorProcess
{

    /**
     * Get the request-value from an angularjs-model-named key<br />
     * <ul>
     * <li>data[0].name => $posted->data[0]->name</li>
     * </ul>
     * 
     * @throws \Exception
     * @param \stdClass $posted
     * @param string $fieldname
     * @return string|array|\stdClass Description
     */
    public static function getPostedValue($posted, $fieldname)
    {
        $fieldParts = preg_split('/[\[\]\.]/', trim($fieldname));
        array_shift($fieldParts);
        $pointer = $posted;
        foreach ($fieldParts as $fieldPart) {
            if ($fieldPart === '') {
                continue;
            }
            if (is_object($pointer)) {
                $pointer = $pointer->{$fieldPart};
            } elseif (is_array($pointer)) {
                $pointer = $pointer[ trim($fieldPart, "'") ];
            } else {
                throw new Exceptions\PostedKeyNotFoundException($posted, $fieldname);
            }
        }
        return $pointer;
    }

    /**
     * Run the validations
     * 
     * @todo required/ng-required VS other validator
     * @param array|\stdClass $posted
     * @param array|array[] $fieldValidatorMap
     * @return boolean
     */
    public static function validate($posted, $fieldValidatorMap)
    {
        $result = array();
        if (count($fieldValidatorMap) === 0) {
            throw new Exceptions\ValidationRulesNotFoundException();
        } else {
            foreach ($fieldValidatorMap as $fieldname => $validators) {
                foreach ($validators as $json) {
                    $validator = unserialize($json[2]);
                    /* @var $validator IValidator|BaseValidator */
                    if (! $validator->validate(self::getPostedValue($posted, $fieldname), $posted)) {
                        $result[$fieldname]['$error'][ $json[1] ] = true;

                        $result[$fieldname]['$invalid'] = true;
                        $result[$fieldname]['$valid'] = false;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Return all validator result to angularjs
     * 
     * @param array $validateResult
     * @return array
     */
    public static function returnValidated($validateResult)
    {
        return array(
            'form' => $validateResult
        );
    }

    /**
     * Return single error to angularjs<br />
     * Useful for those validation from server.
     * 
     * @param string $fieldname
     * @param string $errorName
     * @return array
     */
    public static function returnSingleError($fieldname, $errorName) {
        return array(
            'form' => array(
                $fieldname => array(
                    '$error' => array($errorName => true,),
                    '$invalid' => true,
                    '$valid' => false,
                ),
            ),
        );
    }
}
