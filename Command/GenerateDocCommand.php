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
 * Class GenerateDocCommand
 * @package Kilix\Bundle\ApiCoreBundle\Command
 */
class GenerateDocCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('api:generate:doc')
            ->setAliases(
                array(
                    'generate:api:doc',
                )
            )
            ->setDescription('generate HTML API documentation from API blueprint markdown with Aglio')
            ->addArgument('input', InputArgument::OPTIONAL, 'main or first blueprint markdown file', 'doc/api_doc.md')
            ->addArgument('output', InputArgument::OPTIONAL, 'output HTML file', 'web/doc/index.html')
            ->addOption(
                'bundles',
                'b',
                InputOption::VALUE_NONE,
                'scan and concatenate blueprint files in bundles ressources directories'
            )
            ->addOption(
                'resources-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'directory in bundle ressource to concatenate',
                'doc/api'
            )
            ->addOption('template', 't', InputOption::VALUE_REQUIRED, 'template ', 'default')
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command generate API Documentation.

<info>php %command.full_name% doc/api_doc.md web/doc/index.html</info>

generate documentation with default template to <comment>web/doc/index.html</comment>

<info>php %command.full_name% --template=slate doc/api_doc.md web/doc/index.html</info>

generate documentation with slate template

<info>php %command.full_name% --bundles doc/api_head_doc.md web/doc/index.html</info>

generate documentation by file concatenation from bundles Resources/<comment>doc/api</comment> directories, <info>doc/api_head_doc.md</info> is used as blueprint header

<info>php %command.full_name% --bundles --resources-dir=doc/blueprint doc/api_head_doc.md web/doc/index.html</info>

generate documentation by file concatenation from bundles ressources/<comment>doc/blueprint</comment> directories

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
            $aglio = $this->getContainer()->get('kilix_api_core.aglio');

            $projectDir = realpath($this->getContainer()->get('kernel')->getRootDir().'/..');

            $mainBlueprint = $input->getArgument('input');
            if(!$fs->exists($mainBlueprint)) {
                $mainBlueprint = $projectDir.'/'.$mainBlueprint;
                if (!$fs->exists($mainBlueprint)) {
                    throw new \InvalidArgumentException('Main Input file '.$mainBlueprint.' doesn\'t exists');
                }
            }

            $useBundles = $input->getOption('bundles');
            $resourceDir = $input->getOption('resources-dir');

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(
                    'Generate API documentation from '.($useBundles ? 'main' : '').' file <info>'.$mainBlueprint.'</info>'
                );
            }

            $outputHtml = $projectDir.'/'.$input->getArgument('output');
            $template = $input->getOption('template');

            $output->writeln($aglio->generateDoc($mainBlueprint, $outputHtml, $useBundles, $resourceDir, $template, $output));

            $output->writeln('API Documentation generated to <info>'.$outputHtml.'<info>');
        } catch (\Exception $e) {
            $output->writeln('<error>API Documentation generation failed : </error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}
