<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\AngularjsPostbackValidator;

/**
 * Description of DirectiveValidator
 *
 * @version $id$
 * @author peter.ho
 */
final class NgRequiredProcess
{

    /**
     * Check if the string is able to be tokenized
     * 
     * @staticvar \JTokenizer $a
     * @param string $string
     * @return int
     */
    public static function isJsTokenizable($string)
    {
        static $jTokenizer = null;
        if ($jTokenizer === null) {
            $jTokenizer = new \JTokenizer(false, true);
        }
        return count($jTokenizer->get_all_tokens($string));
    }

    /**
     * Replace all javascript-model-string to php-variable-chain
     * 
     * @param string $string
     * @return string
     */
    private static function replacePhp($string)
    {
        $fieldParts = preg_split('/[\.]/', $string);
        $pointer = "$" . array_shift($fieldParts);
        foreach ($fieldParts as $fieldPart) {
            if ($fieldPart === '') {
                continue;
            }
            $pointer .= "->{$fieldPart}";
        }
        return $pointer;
    }

    /**
     * Translate comparsion from php to javascript<br />
     * Mainly used for [ng-required]
     * 
     * @todo Think how to plug into Twig template
     * @param string $phpCompare
     * @param array|\stdClass $posted
     * @return string
     */
    public static function phpToJsCompare($phpCompare, $posted)
    {
        $matches = array ();
        preg_match_all("/\{([^}]+)\}/", $phpCompare, $matches);
        return preg_replace_callback("/\{([^}]+)\}/", function($matches) use ($posted) {
            if (!ValidatorProcess::getPostedValue($posted, $matches[1])) {
                $result = $matches[1];
            } elseif (static::isJsTokenizable($matches[1])) {
                $result = static::replacePhp($matches[1]);
            } else {
                throw new \Exception("Expression contains invalid parts.");
            }
            return $result;
        }, $phpCompare);
    }

}
