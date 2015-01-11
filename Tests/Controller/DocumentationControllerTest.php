<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class DocumentationControllerTest extends WebTestCase
{
    public function testRoutingApiParameters()
    {
        $client = static::createClient();

        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/web/doc');

        $client->request('GET', '/doc', array());
        $response = $client->getResponse();
        $this->assertEquals('api doc generated', $response->getContent());
    }
}
