<?php

namespace Hopeter1018\AngularjsPostbackValidator;

abstract class BaseValidator implements IValidator
{

    /**
     * Return array of:
     * <ol>
     * <li>Namespace\ClassName</li>
     * <li>classname</li>
     * <li>serialized $this</li>
     * </ul>
     * 
     * @return array
     */
    public function toJsonArray()
    {
        return array (
            get_class($this),
            strtolower(basename(get_class($this))),
            serialize($this),
        );
    }

    /**
     * @param string $html
     * @param \QueryPath\DOMQuery $input
     */
    abstract public function __construct($html, \QueryPath\DOMQuery $input);

    /**
     * @param string|integer|double $value the ng-model value
     * @param array|\stdClass $posted the whole posted array
     */
    abstract public function validate($value, $posted);

}
