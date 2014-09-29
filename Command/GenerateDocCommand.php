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
            ->setDescription('generate HTML API documentation from API blueprint markdown with Aglio')
            ->addArgument('input', InputArgument::OPTIONAL, 'input blueprint markdown file', 'doc/api_doc.md')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Generate docs from which folder', 'doc')
            ->addArgument('output', InputArgument::OPTIONAL, 'output HTML file', 'web/doc/index.html')
            ->addOption('template', 't', InputOption::VALUE_REQUIRED, 'template ', 'default')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command generate API Documentation.

<info>php %command.full_name% doc/api_doc.md web/doc/index.html</info>

generate documentation with default template to <comment>web/doc/index.html</comment>

<info>php %command.full_name% -t slate doc/api_doc.md web/doc/index.html</info>

generate documentation with slate template
EOF
            )
        ;
    }

    protected function concatDocFiles(InputInterface $input)
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $directory = $input->getArgument('directory');
        $projectDir = realpath($this->getContainer()->get('kernel')->getRootDir().'/..');
        if(!$fs->exists($directory)) 
        {
            $directory = $projectDir.'/'.$directory;
            if(!$fs->exists($directory))
            {
                throw new \InvalidArgumentException('Input file '.$directory.' doesn\'t exists');
            }
        }
        $finalDocPath = $input->getArgument('input');
        $fs->remove($projectDir.'/'.$finalDocPath);
        foreach ($finder->in($directory)->files()->name('*.md')->notName(basename($finalDocPath))->sortByName() as $file)
        {
            $currentDocFileContent = $file->getContents();
            $separationSentences = PHP_EOL;
            $separationSentences .= '## FROM FILE : ' . $file->getRealpath();
            $separationSentences .= PHP_EOL;
            $separationSentences .= PHP_EOL;
            file_put_contents($projectDir.'/'.$finalDocPath, $separationSentences, FILE_APPEND);
            file_put_contents($projectDir.'/'.$finalDocPath, $currentDocFileContent, FILE_APPEND);
        }
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->concatDocFiles($input);
            $fs = new Filesystem();

            $projectDir = realpath($this->getContainer()->get('kernel')->getRootDir().'/..');

            $inputBlueprint = $input->getArgument('input');

            if(!$fs->exists($inputBlueprint)) {
                $inputBlueprint = $projectDir.'/'.$inputBlueprint;
                if(!$fs->exists($inputBlueprint)) {
                    throw new \InvalidArgumentException('Input file '.$inputBlueprint.' doesn\'t exists');
                }
            }

            $outputHtml = $projectDir.'/'.$input->getArgument('output');
            $outputHtmlDir = dirname($outputHtml);

            if (!$fs->exists($outputHtmlDir)) {
                $fs->mkdir($outputHtmlDir);
            }

            $template = $input->getOption('template');
            if (!in_array($template, $this->getAvailableTemplates())) {
                $template = 'default';
            }

            echo $this->executeAglioCommand('-t '.$template.' -i '.$inputBlueprint.' -o '.$outputHtml)->getOutput();
            $output->writeln('API Documentation generated to <info>'.$outputHtml.'<info>');
        } catch(\Exception $e) {
            $output->writeln('<error>API Documentation generation failed : </error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }

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