<?php

namespace Kilix\Bundle\ApiCoreBundle\Decoder;

use Symfony\Component\DependencyInjection\ContainerAware;

class DecoderProvider extends containerAware
{
    private $decoders;
    
    /**
     * Constructor.
     *
     * @param array $decoders List of key (format) value (service ids) of decoders
     */
    public function __construct(array $decoders)
    {
        $this->decoders = $decoders;
    }

    /**
     * Verifies a format is supported.
     *
     * @param string $format
     *
     * @return bool
     */
    public function supports($format)
    {
        return isset($this->decoders[$format]);
    }

    /**
     * Provides a docder for a supported format  
     *
     * @param string $format
     *
     * @return \Kilix\Bundle\ApiCoreBundle\DecoderInterface
     * @throws \InvalidArgumentException
     */
    public function getDecoder($format)
    {
        if (!$this->supports($format)) {
            throw new \InvalidArgumentException(
                sprintf("Format '%s' is not supported by %s.", $format, __CLASS__)
            );
        }
        return $this->container->get($this->decoders[$format]);
    }
}