<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Kilix\Bundle\ApiCoreBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataForProcessedConfiguration
     */
    public function testProcessedConfiguration($configs, $expectedConfig)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $this->assertEquals($expectedConfig, $config);
    }

    public function dataForProcessedConfiguration()
    {
        return array(
            array(
                array(),
                array(
                    'aglio_bin' => '/usr/bin/aglio',
                )
            ),
        );
    }
}
