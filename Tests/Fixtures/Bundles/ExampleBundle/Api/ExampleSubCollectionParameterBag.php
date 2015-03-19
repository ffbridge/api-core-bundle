<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Fixtures\Bundles\ExampleBundle\Api;

use Kilix\Bundle\ApiCoreBundle\Request\ApiParameterBag;

class ExampleSubCollectionParameterBag extends ApiParameterBag
{
    public function getFilteredKeys()
    {
        return array('page', 'filters');
    }
}
