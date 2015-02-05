<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Command;

use Kilix\Bundle\ApiCoreBundle\Command\GenerateBlueprintCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class GenerateBlueprintCommandTest extends WebTestCase
{
    public function setUp()
    {
        static::bootKernel();
    }

    public function testExecute()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/blueprint.json');

        $application = new Application(static::$kernel);
        $application->add(new GenerateBlueprintCommand());

        $command = $application->find('api:generate:blueprint');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--replace' => true,
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#Blueprint generated to '.$rootDir.'/doc/blueprint.json#', $commandTester->getDisplay());
        $this->assertFileExists($rootDir.'/doc/blueprint.json');
    }

    public function testExecuteCustomOutput()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove(array(
            $rootDir.'/doc/build',
        ));

        $application = new Application(static::$kernel);
        $application->add(new GenerateBlueprintCommand());

        $command = $application->find('api:generate:blueprint');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'output' => 'doc/build/blueprint.json',
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#Blueprint generated to '.$rootDir.'/doc/build/blueprint.json#', $commandTester->getDisplay());
        $this->assertFileExists($rootDir.'/doc/build/blueprint.json');

        $fs->remove(array(
            $rootDir.'/doc/build',
        ));
    }

    public function testExecuteFailed()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/blueprint.json');

        $application = new Application(static::$kernel);
        $application->add(new GenerateBlueprintCommand());

        $command = $application->find('api:generate:blueprint');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'input' => 'doc/foobar.md',
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#Blueprint generation failed :\s*Main Input file '.$rootDir.'/doc/foobar.md doesn\'t exists#', $commandTester->getDisplay());
        $this->assertFileNotExists($rootDir.'/doc/blueprint.json');
    }
}
