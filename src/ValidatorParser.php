<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\AngularjsPostbackValidator;

use stdClass;

/**
 * Description of ValidatorParser
 *
 * @version $id$
 * @author peter.ho
 */
class ValidatorParser
{

// <editor-fold defaultstate="collapsed" desc="Configs">

    /**
     * html element kept for strip_tags
     * @see strip_tags
     * @var string[]
     */
    private static $queryHtmlTags = array(
        'form', 'input', 'select', 'textarea'
    );

    /**
     * selectors mapping for validation<br />
     * querypath selector => array(type, class, attr)
     * 
     * @var array[]
     */
    private static $selectors = array(
        // HTML 5 checking
        'input[type="number"]' => array('type', 'Html5Type\Number'),
        'input[type="url"]' => array('type', 'Html5Type\Url'),
        'input[type="email"]' => array('type', 'Html5Type\Email'),
//        'input[type="radio"]' => array('type', 'Html5Type\Radio'),
//        'input[type="checkbox"]' => array('type', 'Html5Type\Checkbox'),

        // Angular defaults
        'input[required]' => array('attr', 'Html5Validator\Required', 'required'),
        'input[ng-required]' => array('attr', 'Html5Validator\NgRequired', 'ng-required'),
        'input[pattern]' => array('attr', 'Html5Validator\Pattern', 'pattern'),
        'input[ng-minlength]' => array('attr', 'Html5Validator\MinLength', 'ng-minlength'),
        'input[ng-maxlength]' => array('attr', 'Html5Validator\MaxLength', 'ng-maxlength'),
        'input[min]' => array('attr', 'Html5Validator\Min', 'min'),
        'input[max]' => array('attr', 'Html5Validator\Max', 'max'),

        // Custom Directives
    );

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Parse functions">

    /**
     * Return the list of allowed tag in strip_tags format.
     * @link http://php.net/manual/en/function.strip-tags.php
     * @return string <form><input>
     */
    private static function getQueryHtmlTagsForStripTags()
    {
        return '<div><' . implode('><', self::$queryHtmlTags) . '>';
    }

    /**
     * Return the html src from the given path with PHP tag and HTML comment striped.
     * @param string $filename path of the html src
     * @return string|html
     */
    private static function getHtmlSrc($filename)
    {
        $html = str_replace(
            array('<script', '</script'),
            array('<div', '</div'),
            file_get_contents($filename)
        );
        return strip_tags($html, self::getQueryHtmlTagsForStripTags());
    }

    /**
     * Return the list of selectors in 1 line
     * 
     * @param string $selectorParent html wrapper of form elements
     * @return string \QueryPath selector string
     */
    private static function getCssSelectors($selectorParent)
    {
        return $selectorParent . " ". implode(",$selectorParent ", array_keys(self::$selectors));
    }

    /**
     * Return the list of selectors in 1 line
     * 
     * @param string $selectorParent html wrapper of form elements
     * @return string \QueryPath selector string
     */
    public static function getXpathSelectors($selectorParent)
    {
        return $selectorParent . "//". implode(
            "|$selectorParent//",
            array_map(
                function($selectorStr) {
                    return str_replace("[", "[@", $selectorStr);
                },
                array_keys(self::$selectors)
            )
        );
    }

    /**
     * Check and loop replace ng-repeat before parsing the html
     * 
     * @param string $htmlSrc
     * @param array|stdClass $data
     * @return string
     */
    public static function replaceNgRepeat($htmlSrc, $data)
    {
        $qp = \QueryPath::withHTML($htmlSrc);
        /* @var $qp \QueryPath\DOMQuery */

        //  avoid nexted ng-repeat.
        $ngRepeats = $qp->find("[ng-repeat]:not([ng-repeat] [ng-repeat])");
        /* @var $ngRepeats \QueryPath\DOMQuery */
        foreach ($ngRepeats as $ngRepeat) {
            /* @var $ngRepeat \QueryPath\DOMQuery */
            $eleHtml = $ngRepeat->html();
            list($each, $src) = explode('in', $ngRepeat->attr('ng-repeat'));

            $array = ValidatorProcess::getPostedValue($data, $src);
            $toReplace = "";
            if (count($array) > 0) {
                foreach ($array as $index => $item) {
                    $toReplace .= str_replace(
                        array(trim($each) . ".", $ngRepeat->attr('ng-repeat'), 'ng-repeat=""'),
                        array(trim($src) . "[{$index}].", "", ''),
                        $eleHtml
                    );
                }
            }
            $ngRepeat->replaceWith($toReplace);
        }
        return (strstr($qp->xhtml(), ' ng-repeat="'))
            ? static::replaceNgRepeat($qp->xhtml(), $data)
            : $qp->xhtml();
    }

    /**
     * Check and loop replace ng-repeat before parsing the html
     * 
     * @param string $htmlSrc
     * @param array|stdClass $data
     * @return string
     */
    public static function replaceNgRepeatXpath($htmlSrc, $data)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($htmlSrc);
        $xpath = new \DOMXpath($doc);
        $elements = $xpath->query("//*[@ng-repeat and not(ancestor::*[@ng-repeat])]");
        /* @var $element \DOMNodeList */
        foreach ($elements as $element) {
            /* @var $element \DOMNode */
            $eleHtml = $doc->saveXML($element);
            list($each, $src) = explode('in', $element->getAttribute('ng-repeat'));

            $array = ValidatorProcess::getPostedValue($data, $src);
            $toReplace = "";
            if (count($array) > 0) {
                foreach ($array as $index => $item) {
                    $toReplace .= str_replace(
                        array(trim($each) . ".", $element->getAttribute('ng-repeat'), 'ng-repeat=""'),
                        array(trim($src) . "[{$index}].", "", ''),
                        $eleHtml
                    );
                }
            }

            $temp = new \DOMDocument;
            $temp->loadXML("<root>{$toReplace}</root>");
            $tempRoot = $temp->getElementsByTagName('root');
            $newElements = $tempRoot->item(0)->childNodes;
            foreach ($newElements as $index => $newElement) {
                $element->parentNode->insertBefore($doc->importNode($newElement, true), $element);
            }
            $element->parentNode->removeChild($element);
        }
        return (strstr($doc->saveXML(), ' ng-repeat="'))
            ? static::replaceNgRepeat($doc->saveXML(), $data)
            : $doc->saveXML();
    }

    /**
     * Parse and build the list of validator from the html source
     * 
     * @param string $filename
     * @param array|stdClass $data
     * @param string $selectorParent
     * @return array[]
     */
    public static function parseHtml($filename, $data, $selectorParent = 'form')
    {
        $htmlSrc = self::replaceNgRepeat(self::getHtmlSrc($filename), $data);

        $inputs = \QueryPath::withHTML(
            $htmlSrc,
            self::getCssSelectors($selectorParent),
            array (
                'convert_from_encoding' => 'utf-8',
                'convert_to_encoding' => 'utf-8',
            ));
        /* @var $inputs \QueryPath\DOMQuery[] */

        $fields = array();
        foreach ($inputs as $input) {
            $eleHtml = $input->html();
            foreach (self::$selectors as $selector) {
                $className = $selector[1];
                $htmlChecker = strtolower(basename($className));
                if (
                    (
                        ($selector[0] === 'attr' and $input->hasAttr($selector[2]))
                        or ($selector[0] === 'type' and strtolower($input->attr('type')) === $htmlChecker)
                    )
                ) {
                    if (class_exists($class = __NAMESPACE__ . '\\' . $className)
                        and ($obj = new $class($eleHtml, $input)) instanceof IValidator)
                    {
                        $fieldModel = $input->attr('ng-model');
                        if (! isset($fields[$fieldModel])) {
                            $fields[$fieldModel] = array();
                        }
                        $fields[$fieldModel][] = $obj->toJsonArray();
                    } elseif (APP_IS_DEV) {
                        throw new \Exception("Can't find validator class.");
                    }
                }
            }
        }
        return $fields;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Misc">

    /**
     * String level function to get attribute from a string of html tag
     * 
     * @param string $elementHtml
     * @param string $attrName
     * @return string
     */
    public static function getAttr($elementHtml, $attrName)
    {
        $attrStart = $attrName . '="';
        $len = strlen($attrStart);
        $start = strpos($elementHtml, $attrStart);
        $length = strpos(substr($elementHtml, $start + $len), '"');
        return substr($elementHtml, $start + $len, $length);
    }

// </editor-fold>

}
