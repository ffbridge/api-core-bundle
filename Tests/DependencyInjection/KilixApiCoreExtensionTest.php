<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\DependencyInjection;

use Kilix\Bundle\ApiCoreBundle\DependencyInjection\KilixApiCoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class KilixApiCoreExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testDefault()
    {
        $container = new ContainerBuilder();
        $loader = new KilixApiCoreExtension();
        $loader->load(array(array()), $container);

        $this->assertEquals('/usr/bin/aglio', $container->getParameter('kilix_api_core.aglio_bin'));
    }
}
