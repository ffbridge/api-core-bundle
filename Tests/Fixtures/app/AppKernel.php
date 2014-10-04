<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            // register the other bundles your tests depend on
            new Kilix\Bundle\ApiCoreBundle\KilixApiCoreBundle(),
            new \Kilix\Bundle\ApiCoreBundle\Tests\Fixtures\Bundles\ExampleBundle\ExampleBundle(),
            new \Kilix\Bundle\ApiCoreBundle\Tests\Fixtures\Bundles\ExampleBundle2\ExampleBundle2(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/KilixApiCoreBundle/cache';
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/KilixApiCoreBundle/logs';
    }
}
