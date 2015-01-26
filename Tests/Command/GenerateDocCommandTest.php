<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Command;

use Kilix\Bundle\ApiCoreBundle\Command\GenerateDocCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class GenerateDocCommandTest extends WebTestCase
{
    public function setUp()
    {
        static::bootKernel();
    }

    public function testExecute()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/web/doc');

        $application = new Application(static::$kernel);
        $application->add(new GenerateDocCommand());

        $command = $application->find('api:generate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ), array('verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE));

        $this->assertRegExp('#API Documentation generated to '.$rootDir.'/web/doc/index.html#', $commandTester->getDisplay());
        $this->assertFileExists($rootDir.'/web/doc/index.html');
    }

    public function testExecuteTemplateUnknown()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/web/doc');

        $application = new Application(static::$kernel);
        $application->add(new GenerateDocCommand());

        $command = $application->find('api:generate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '-t' => 'foobar',
        ));

        $this->assertFileExists($rootDir.'/web/doc/index.html');
        $this->assertRegExp('#API Documentation generated to '.$rootDir.'/web/doc/index.html#', $commandTester->getDisplay());
    }

    public function testExecuteInexistentInputFile()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/web/doc');

        $application = new Application(static::$kernel);
        $application->add(new GenerateDocCommand());

        $command = $application->find('api:generate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'command' => $command->getName(),
                'input' => 'foobar.md',
            ));

        $this->assertFileNotExists($rootDir.'/web/doc/index.html');
        $this->assertRegExp('#API Documentation generation failed :\s*Main Input file '.$rootDir.'/foobar.md doesn\'t exists#', $commandTester->getDisplay());
    }

    public function testExecuteWithoutBundlesScan()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/web/doc');

        $application = new Application(static::$kernel);
        $application->add(new GenerateDocCommand());

        $command = $application->find('api:generate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'command' => $command->getName(),
                '--no-scan' => true,
            ), array(
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ));

        $this->assertFileExists($rootDir.'/web/doc/index.html');
        $this->assertRegExp('#API Documentation generated to '.$rootDir.'/web/doc/index.html#', $commandTester->getDisplay());
    }

    public function testExecuteWithBundlesScanInexistentResourcesDir()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/web/doc');

        $application = new Application(static::$kernel);
        $application->add(new GenerateDocCommand());

        $command = $application->find('api:generate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'command' => $command->getName(),
                '--resources-dir' => 'doc/blueprint',
            ));

        $this->assertFileExists($rootDir.'/web/doc/index.html');
        $this->assertRegExp('#API Documentation generated to '.$rootDir.'/web/doc/index.html#', $commandTester->getDisplay());
    }

    public function testExecuteAglioFailed()
    {
        $rootDir = realpath(static::$kernel->getRootDir().'/../');

        $fs = new Filesystem();
        $fs->remove($rootDir.'/web/doc');

        $application = new Application(static::$kernel);

        $application->add(new GenerateDocCommand());

        $command = $application->find('api:generate:doc');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'command' => $command->getName(),
                '--no-scan' => true,
                'input' => 'doc/foobar.txt',
            ));

        $this->assertFileNotExists($rootDir.'/web/doc/index.html');
        $this->assertRegExp('#API Documentation generation failed :\s*Aglio Error#', $commandTester->getDisplay());
    }
}
