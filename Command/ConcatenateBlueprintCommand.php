<?php

namespace Kilix\Bundle\ApiCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ConcatenateBlueprintCommand
 * @package Kilix\Bundle\ApiCoreBundle\Command
 */
class ConcatenateBlueprintCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('api:concatenate:doc')
            ->setDescription('concatenate API blueprint markdown from each bundles')
            ->addArgument('input', InputArgument::OPTIONAL, 'main or first blueprint markdown file', 'doc/api_doc.md')
            ->addArgument('output', InputArgument::OPTIONAL, 'output Blueprint Markdown file', 'doc/api_doc_full.md')
            ->addOption(
                'resources-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'directory in bundle ressource to concatenate',
                'doc/api'
            )
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> concatenate API blueprint markdown.

<info>php %command.full_name% doc/api_doc.md doc/api_doc_full.md</info>

concatenate API blueprint markdown from bundles Resources/<comment>doc/api</comment> directories, <info>doc/api_head_doc.md</info> is used as blueprint header

<info>php %command.full_name% --resources-dir=doc/blueprint doc/api_head_doc.md doc/api_doc_full.md</info>

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

            $resourceDir = $input->getOption('resources-dir');

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(
                    'Concatenate blueprint markdown with Header file <info>'.$mainBlueprint.'</info>'
                );
            }

            $outputMD = $projectDir.'/'.$input->getArgument('output');

            $result = $blueprintManager->concatenateDoc($mainBlueprint, $outputMD, $resourceDir, $output);

            $output->writeln('Blueprint markdown Concatenated to <info>'.$result.'<info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Blueprint markdown Concatening failed : </error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}
