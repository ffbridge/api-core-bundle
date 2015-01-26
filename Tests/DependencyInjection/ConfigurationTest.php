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
            // dataset #0
            array(
                array(),
                array(
                    'aglio_bin' => '/usr/bin/aglio',
                    'snowcrash_bin' => '/usr/local/bin/snowcrash',
                    'apiary2postman_bin' => '/usr/local/bin/apiary2postman',
                    'replacements' => array(),
                )
            ),
            // dataset #1
            array(
                array(
                    'kilix_api_core' => array(
                        'replacements' => array(
                            '%api_url%' => 'https://core.easi.local',
                        ),
                    ),
                ),
                array(
                    'aglio_bin' => '/usr/bin/aglio',
                    'snowcrash_bin' => '/usr/local/bin/snowcrash',
                    'apiary2postman_bin' => '/usr/local/bin/apiary2postman',
                    'replacements' => array(
                        '%api_url%' => 'https://core.easi.local',
                    ),
                )
            ),
        );
    }
}
