<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\DependencyInjection;

use Kilix\Bundle\ApiCoreBundle\DependencyInjection\KilixApiCoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class KilixApiCoreExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testDefault()
    {
        $container = new ContainerBuilder();
        $loader = new KilixApiCoreExtension();
        $loader->load(array(array()), $container);

        $this->assertEquals('/usr/bin/aglio', $container->getParameter('kilix_api_core.aglio_bin'));
        $this->assertEquals('/usr/local/bin/drafter', $container->getParameter('kilix_api_core.blueprint_parser_bin'));
        $this->assertEquals('/usr/local/bin/apiary2postman', $container->getParameter('kilix_api_core.apiary2postman_bin'));
        $this->assertEquals('/usr/local/bin/blueman', $container->getParameter('kilix_api_core.blueman_bin'));
    }
}
