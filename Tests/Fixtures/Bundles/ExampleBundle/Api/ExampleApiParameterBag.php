<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Fixtures\Bundles\ExampleBundle\Api;

use Kilix\Bundle\ApiCoreBundle\Request\ApiParameterBag;

class ExampleApiParameterBag extends ApiParameterBag
{
    public function getFilteredType()
    {
        return array(
            static::PARAMETERS_TYPE_QUERY,
            static::PARAMETERS_TYPE_REQUEST,
        );
    }

    public function getFilteredKeys()
    {
        return array('page', 'max', 'sort', 'created_at');
    }
}
