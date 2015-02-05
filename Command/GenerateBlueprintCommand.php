<?php

namespace Kilix\Bundle\ApiCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class GenerateBlueprintCommand
 * @package Kilix\Bundle\ApiCoreBundle\Command
 */
class GenerateBlueprintCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('api:generate:blueprint')
            ->setAliases(
                array(
                    'generate:api:blueprint',
                )
            )
            ->setDescription('generate Blueprint Abstract Syntax Tree from API blueprint markdown with snowcrash')
            ->addArgument('input', InputArgument::OPTIONAL, 'main or first blueprint markdown file', 'doc/api_doc.md')
            ->addArgument('output', InputArgument::OPTIONAL, 'output Blueprint file', 'doc/blueprint.json')
            ->addOption(
                'replace',
                'r',
                InputOption::VALUE_NONE,
                'replace patterns'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Blueprint Format',
                'json'
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
                The <info>%command.name%</info> command generate Blueprint JSON.

<info>php %command.full_name% doc/api_doc.md doc/blueprint.json</info>

generate documentation by file concatenation from bundles Resources/<comment>doc/api</comment> directories, <info>doc/api_head_doc.md</info> is used as blueprint header

<info>php %command.full_name% --bundles --resources-dir=doc/blueprint doc/api_head_doc.md doc/blueprint.json</info>

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
            if (!$fs->exists($mainBlueprint)) {
                $mainBlueprint = $projectDir.'/'.$mainBlueprint;
                if (!$fs->exists($mainBlueprint)) {
                    throw new \InvalidArgumentException('Main Input file '.$mainBlueprint.' doesn\'t exists');
                }
            }

            $useBundles = !$input->getOption('no-scan');
            $useReplace = $input->getOption('replace');
            $resourceDir = $input->getOption('resources-dir');

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(
                    'Generate Blueprint from '.($useBundles ? 'main' : '').' file <info>'.$mainBlueprint.'</info>'
                );
            }

            $outputHtml = $projectDir.'/'.$input->getArgument('output');
            $format = $input->getOption('format');

            $output->writeln($blueprintManager->generateBlueprint($mainBlueprint, $outputHtml, $format, $useBundles, $useReplace, $resourceDir, $output));

            $output->writeln('Blueprint generated to <info>'.$outputHtml.'<info>');
        } catch (\Exception $e) {
            $output->writeln('<errorBlueprint generation failed : </error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}
