<?php

namespace Kilix\Bundle\ApiCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;

/**
 * Class GeneratePostmanCommand
 * @package Kilix\Bundle\ApiCoreBundle\Command
 */
class GeneratePostmanCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('api:generate:postman')
            ->setAliases(
                array(
                    'generate:api:postman',
                )
            )
            ->setDescription('generate API Postman configuration from API blueprint markdown with apiary2postman')
            ->addArgument('input', InputArgument::OPTIONAL, 'main or first blueprint markdown file', 'doc/api_doc.md')
            ->addArgument('output', InputArgument::OPTIONAL, 'output JSON Postman Collection configuration', 'doc/postman.collection')
            ->addOption(
                'replace',
                'r',
                InputOption::VALUE_NONE,
                'replace patterns'
            )
            ->addOption(
                'pretty',
                'p',
                InputOption::VALUE_NONE,
                'dump Postman JSON config as pretty JSON'
            )
            ->addOption(
                'no-scan',
                'c',
                InputOption::VALUE_NONE,
                'disable bundles scanning to concatenate blueprint files in bundles ressources directories'
            )
            ->addOption(
                'resources-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'directory in bundle ressource to concatenate',
                'doc/api'
            )
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command generate API Postman configuration.

<info>php %command.full_name% doc/api_doc.md doc/postman.collection</info>

EOF
            );
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $fs = new Filesystem();
            $blueprintManager = $this->getContainer()->get('kilix_api_core.blueprint_manager');

            $projectDir = realpath($this->getContainer()->get('kernel')->getRootDir().'/..');

            $mainBlueprint = $input->getArgument('input');
            if(!$fs->exists($mainBlueprint)) {
                $mainBlueprint = $projectDir.'/'.$mainBlueprint;
                if (!$fs->exists($mainBlueprint)) {
                    throw new \InvalidArgumentException('Main Input file '.$mainBlueprint.' doesn\'t exists');
                }
            }

            $useBundles = !$input->getOption('no-scan');
            $useReplace = $input->getOption('replace');
            $resourceDir = $input->getOption('resources-dir');
            $pretty = $input->getOption('pretty');

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(
                    'Generate API Postman collection from '.($useBundles ? 'main' : '').' file <info>'.$mainBlueprint.'</info>'
                );
            }

            $outputPostman = $projectDir.'/'.$input->getArgument('output');

            $output->writeln($blueprintManager->generatePostman($mainBlueprint, $outputPostman, $pretty, $useBundles, $useReplace, $resourceDir, $output));

            $output->writeln('API Postman collection generated to <info>'.$outputPostman.'<info>');
        } catch (\Exception $e) {
            $output->writeln('<error>API Postman collection generation failed : </error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}
