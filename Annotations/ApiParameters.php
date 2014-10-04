<?php

namespace Kilix\Bundle\ApiCoreBundle\Annotations;

/**
 * @Annotation
 */
class ApiParameters
{
    /**
     * @var string Fully Qualified class name of ApiParameterBag
     */
    public $bag;

    /**
     * @var bool
     */
    public $validation;
}
