<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Command;

use Kilix\Bundle\ApiCoreBundle\Command\ConcatenateBlueprintCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class ConcatenateBlueprintCommandTest extends WebTestCase
{
    public function setUp()
    {
        static::bootKernel();
    }

    public function testExecute()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/api_doc_full.md');

        $application = new Application(static::$kernel);
        $application->add(new ConcatenateBlueprintCommand());

        $command = $application->find('api:concatenate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#Blueprint markdown Concatenated to '.$rootDir.'/doc/api_doc_full.md#', $commandTester->getDisplay());
        $this->assertFileExists($rootDir.'/doc/api_doc_full.md');
    }

    public function testExecuteWithReplace()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/api_doc_full.md');

        $application = new Application(static::$kernel);
        $application->add(new ConcatenateBlueprintCommand());

        $command = $application->find('api:concatenate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--replace' => true,
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#Blueprint markdown Concatenated to '.$rootDir.'/doc/api_doc_full.md#', $commandTester->getDisplay());
        $this->assertFileExists($rootDir.'/doc/api_doc_full.md');
    }

    public function testExecuteCustomOutput()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove(array(
            $rootDir.'/doc/api_doc_full.md',
            $rootDir.'/doc/build/api_doc_full.md',
        ));

        $application = new Application(static::$kernel);
        $application->add(new ConcatenateBlueprintCommand());

        $command = $application->find('api:concatenate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'output' => 'doc/build/api_doc_full.md',
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#Blueprint markdown Concatenated to '.$rootDir.'/doc/build/api_doc_full.md#', $commandTester->getDisplay());
        $this->assertFileExists($rootDir.'/doc/build/api_doc_full.md');

        $fs->remove(array(
            $rootDir.'/doc/api_doc_full.md',
            $rootDir.'/doc/build/api_doc_full.md',
        ));
    }

    public function testExecuteFailed()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/doc/api_doc_full.md');

        $application = new Application(static::$kernel);
        $application->add(new ConcatenateBlueprintCommand());

        $command = $application->find('api:concatenate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'input' => 'doc/foobar.md',
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#Blueprint markdown Concatening failed :\s*Main Input file '.$rootDir.'/doc/foobar.md doesn\'t exists#', $commandTester->getDisplay());
        $this->assertFileNotExists($rootDir.'/doc/api_doc_full.md');
    }
}
