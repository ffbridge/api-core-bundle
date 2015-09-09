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
                    'blueprint_parser_bin' => '/usr/local/bin/drafter',
                    'apiary2postman_bin' => '/usr/local/bin/apiary2postman',
                    'blueman_bin' => '/usr/local/bin/blueman',
                    'default_postman_converter' => 'blueman',
                    'replacements' => array(),
                    'content_type_listener' => array(
                        'decoders' => array(
                            'json' => 'kilix_api_core.decoder.json',
                        ),
                    ),
                ),
            ),
            // dataset #1
            array(
                array(
                    'kilix_api_core' => array(
                        'default_postman_converter' => 'apiary2postman',
                        'replacements' => array(
                            '%api_url%' => 'https://core.easi.local',
                        ),
                        'content_type_listener' => array(
                            'decoders' => array(
                                'json' => 'kilix_api_core.decoder.json',
                            ),
                        ),
                    ),
                ),
                array(
                    'aglio_bin' => '/usr/bin/aglio',
                    'blueprint_parser_bin' => '/usr/local/bin/drafter',
                    'apiary2postman_bin' => '/usr/local/bin/apiary2postman',
                    'blueman_bin' => '/usr/local/bin/blueman',
                    'default_postman_converter' => 'apiary2postman',
                    'replacements' => array(
                        '%api_url%' => 'https://core.easi.local',
                    ),
                    'content_type_listener' => array(
                        'decoders' => array(
                            'json' => 'kilix_api_core.decoder.json',
                        ),
                    ),
                ),
            ),
        );
    }
}
