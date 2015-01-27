<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Command;

use Kilix\Bundle\ApiCoreBundle\Command\GeneratePostmanCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class GeneratePostmanCommandTest extends WebTestCase
{
    public function setUp()
    {
        static::bootKernel();
    }

    public function testExecute()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/postman.collection');

        $application = new Application(static::$kernel);
        $application->add(new GeneratePostmanCommand());

        $command = $application->find('api:generate:postman');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#API Postman collection generated to '.$rootDir.'/doc/postman.collection#', $commandTester->getDisplay());
        $this->assertFileExists($rootDir.'/doc/postman.collection');
    }

    /**/
    public function testExecuteCustomOutput()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove(array(
            $rootDir.'/doc/build'
        ));

        $application = new Application(static::$kernel);
        $application->add(new GeneratePostmanCommand());

        $command = $application->find('api:generate:postman');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'output' => 'doc/build/postman.collection',
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#API Postman collection generated to '.$rootDir.'/doc/build/postman.collection#', $commandTester->getDisplay());
        $this->assertFileExists($rootDir.'/doc/build/postman.collection');

        $fs->remove(array(
            $rootDir.'/doc/build'
        ));
    }

    /**/
    public function testExecuteFailed()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/postman.collection');

        $application = new Application(static::$kernel);
        $application->add(new GeneratePostmanCommand());

        $command = $application->find('api:generate:postman');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'input' => 'doc/foobar.md'
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#API Postman collection generation failed :\s*Main Input file '.$rootDir.'/doc/foobar.md doesn\'t exists#', $commandTester->getDisplay());
        $this->assertFileNotExists($rootDir.'/doc/postman.collection');
    }
    /**/
}
