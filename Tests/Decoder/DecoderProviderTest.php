<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Decoder;

use Kilix\Bundle\ApiCoreBundle\Decoder\DecoderProvider;
use Kilix\Bundle\ApiCoreBundle\Decoder\JsonDecoder;
use Symfony\Component\DependencyInjection\Container;

class DecoderProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DecoderProvider
     */
    protected $decoderProvider;

    public function setUp()
    {
        $container = new Container();
        $container->set('kilix_api_core.decoder.json', new JsonDecoder());
        $this->decoderProvider = new DecoderProvider(array('json' => 'kilix_api_core.decoder.json'));
        $this->decoderProvider->setContainer($container);
    }

    /**
     * @dataProvider providerSupports
     */
    public function testSupports($format, $expected)
    {
        $this->assertEquals($expected, $this->decoderProvider->supports($format));
    }

    public function providerSupports()
    {
        return array(
            array('json', true),
            array('html', false),
        );
    }

    public function testGetDecoder()
    {
        $this->assertInstanceOf('\Kilix\Bundle\ApiCoreBundle\Decoder\JsonDecoder', $this->decoderProvider->getDecoder('json'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Format 'markdown' is not supported by Kilix\Bundle\ApiCoreBundle\Decoder\DecoderProvider.
     */
    public function testGetDecoderInexistentFormat()
    {
        $this->decoderProvider->getDecoder('markdown');
    }
}
