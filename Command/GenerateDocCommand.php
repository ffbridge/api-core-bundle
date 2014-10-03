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
            ->setAliases(array(
                'generate:api:doc',
            ))
            ->setDescription('generate HTML API documentation from API blueprint markdown with Aglio')
            ->addArgument('input', InputArgument::OPTIONAL, 'main or first blueprint markdown file', 'doc/api_doc.md')
            ->addArgument('output', InputArgument::OPTIONAL, 'output HTML file', 'web/doc/index.html')
            ->addOption('bundles', 'b',InputOption::VALUE_NONE, 'scan and concatenate blueprint files in bundles ressources directories')
            ->addOption('resources-dir', 'd',InputOption::VALUE_REQUIRED, 'directory in bundle ressource to concatenate', 'doc/api')
            ->addOption('template', 't', InputOption::VALUE_REQUIRED, 'template ', 'default')
            ->setHelp(<<<EOF
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
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $fs = new Filesystem();

            $mainBlueprint = $input->getArgument('input');

            $projectDir = realpath($this->getContainer()->get('kernel')->getRootDir().'/..');

            if(!$fs->exists($mainBlueprint)) {
                $mainBlueprint = $projectDir.'/'.$mainBlueprint;
                if(!$fs->exists($mainBlueprint)) {
                    throw new \InvalidArgumentException('Main Input file '.$mainBlueprint.' doesn\'t exists');
                }
            }

            $useBundles = $input->getOption('bundles');
            $resourceDir = $input->getOption('resources-dir');

            $inputBlueprint = $useBundles ? $this->concatDocFiles($mainBlueprint, $resourceDir, $projectDir, $output) : $mainBlueprint;

            $outputHtml = $projectDir.'/'.$input->getArgument('output');
            $outputHtmlDir = dirname($outputHtml);

            if (!$fs->exists($outputHtmlDir)) {
                $fs->mkdir($outputHtmlDir);
            }

            $template = $input->getOption('template');
            if (!in_array($template, $this->getAvailableTemplates())) {
                $template = 'default';
            }

            $output->writeln($this->executeAglioCommand('-t '.$template.' -i '.$inputBlueprint.' -o '.$outputHtml)->getOutput());

            if ($useBundles && $mainBlueprint != $inputBlueprint) {
                $fs->remove($inputBlueprint);
            }

            $output->writeln('API Documentation generated to <info>'.$outputHtml.'<info>');
        } catch(\Exception $e) {
            $output->writeln('<error>API Documentation generation failed : </error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }

    /**
     * @param string $mainFile
     * @param string $resourceDir
     * @param string $projectDir
     * @param OutputInterface $output
     * @return string
     */
    protected function concatDocFiles($mainFile, $resourceDir, $projectDir, OutputInterface $output)
    {
        $fs = new Filesystem();
        $finder = new Finder();

        $bundles = $this->getContainer()->get('kernel')->getBundles();
        $dirToScan = array();
        foreach ($bundles as $bundle) {
            $dir = $bundle->getPath().'/Resources/'.$resourceDir;
            if ($fs->exists($dir)) {
                $dirToScan[] = $dir;
            }
        }

        if (empty($dirToScan)) {
            return $mainFile;
        }

        $finder
            ->files()
            ->name('*.md')
            ->sortByName();

        $finder->in($dirToScan);

        $tempFile = tempnam(sys_get_temp_dir(), 'api');
        if($fs->exists($mainFile))
        {
            file_put_contents($tempFile, file_get_contents($mainFile));
        }

        foreach ($finder as $file)
        {
            $relativePath  = $fs->makePathRelative($file->getRealpath() ,$projectDir);
            $output->writeln('concatenate '.$relativePath);
            file_put_contents(
                $tempFile,
                "\n\n<!-- From file".$relativePath." -->\n\n".$file->getContents()
                , FILE_APPEND);
        }

        $fs->rename($tempFile, $tempFile.'.md');

        return $tempFile.'.md';
    }

    /**
     * @return array
     */
    protected function getAvailableTemplates()
    {
        $process = $this->executeAglioCommand('-l');
        return explode("\n", trim(str_ireplace('Templates:', '', $process->getOutput())));
    }

    /**
     * @param string $command
     * @return Process
     */
    protected function executeAglioCommand($command)
    {
        $aglioBin = $this->getContainer()->getParameter('kilix_api_core.aglio_bin');

        $process = new Process($aglioBin.' '.$command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process;
    }
}