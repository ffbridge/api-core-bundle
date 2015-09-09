<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Tools;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Kilix\Bundle\ApiCoreBundle\Tools\BlueprintManager;
use Symfony\Component\Filesystem\Filesystem;

class BlueprintManagerTest extends WebTestCase
{
    public function setUp()
    {
        static::bootKernel();
    }

    public function testConcatenateDoc()
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__.'/Fixtures/doc/blueprint.md');

        $manager = new BlueprintManager(
            static::$kernel,
            __DIR__.'/Fixtures/bin/aglio_fake aglio',
            __DIR__.'/Fixtures/bin/drafter_fake drafter',
            __DIR__.'/Fixtures/bin/apiary2postman_fake apiary2postman',
            array()
        );

        $target = $manager->concatenateDoc(__DIR__.'/Fixtures/doc/api_doc.md', __DIR__.'/Fixtures/doc/blueprint.md', 'doc/api');

        $this->assertEquals(__DIR__.'/Fixtures/doc/blueprint.md', $target);
        $this->assertFileExists($target);
    }
}
