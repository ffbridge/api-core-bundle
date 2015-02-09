<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class DocumentationControllerTest extends WebTestCase
{
    public function testShow()
    {
        $client = static::createClient();

        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/web/doc');

        $client->request('GET', '/doc', array());
        $response = $client->getResponse();
        $this->assertEquals('api doc generated', $response->getContent());
        $fs->remove($rootDir.'/web/doc');
    }

    public function testPostman()
    {
        $client = static::createClient();

        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/postman.json');

        $client->request('GET', '/doc/postman.json', array());
        $response = $client->getResponse();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $expected = 'Postman collection generated';
        $this->assertEquals($expected, $response->getContent());
        $fs->remove($rootDir.'/doc/postman.json');
    }

    public function testBlueprint()
    {
        $client = static::createClient();

        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/blueprint.json');

        $client->request('GET', '/doc/blueprint.json', array());
        $response = $client->getResponse();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $expected = 'blueprint AST generated';
        $this->assertEquals($expected, $response->getContent());

        $fs->remove($rootDir.'/doc/blueprint.json');
    }

    public function testSource()
    {
        $client = static::createClient();

        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/api_doc_full.md');

        $client->request('GET', '/doc/source.md', array());
        $response = $client->getResponse();
        $this->assertEquals('text/x-markdown; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = file_get_contents(__DIR__.'/../Fixtures/doc/api_doc_full.md');
        $this->assertEquals($expected, $response->getContent());

        $fs->remove($rootDir.'/doc/api_doc_full.md');
    }
}
