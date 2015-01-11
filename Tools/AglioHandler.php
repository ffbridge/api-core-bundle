<?php

namespace Kilix\Bundle\ApiCoreBundle\Tools;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;

class AglioHandler 
{
    /**
     * aglio bin path
     *
     * @var string
     */
    protected $bin;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @param $bin
     * @param $kernel
     * @param string $relativeProjectDir
     */
    public function __construct($bin, $kernel, $relativeProjectDir = '/..')
    {
        $this->bin = $bin;
        $this->kernel = $kernel;
        $this->projectDir = realpath($this->kernel->getRootDir().$relativeProjectDir);
    }

    /**
     * @param OutputInterface $output
     * @return array
     */
    public function getAvailableTemplates(OutputInterface $output = null)
    {
        return explode("\n", trim(str_ireplace('Templates:', '', $this->execute('-l', $output)->getOutput())));
    }

    /**
     * @param $mainBlueprint
     * @param $outputHtml
     * @param bool $concat
     * @param string $resourceDir
     * @param null $template
     * @param OutputInterface $output
     * @return string
     */
    public function  generateDoc($mainBlueprint, $outputHtml, $concat = false, $resourceDir = 'doc/api', $template = null, OutputInterface $output = null)
    {
        $fs = new Filesystem();

        $inputBlueprint = $concat ? $this->concatDocFiles($mainBlueprint, $resourceDir, $output) : $mainBlueprint;

        $outputHtmlDir = dirname($outputHtml);
        if (!$fs->exists($outputHtmlDir)) {
            $fs->mkdir($outputHtmlDir);
        }

        if (!in_array($template, $this->getAvailableTemplates($output))) {
            $template = 'default';
        }

        $result = $this->execute('-t '.$template.' -i '.$inputBlueprint.' -o '.$outputHtml, $output);

        if ($concat && $mainBlueprint != $inputBlueprint) {
            $fs->remove($inputBlueprint);
        }

        return $result->getOutput();
    }

    /**
     * @param string $command
     * @param OutputInterface $output
     * @return Process
     */
    public function execute($command, OutputInterface $output = null)
    {
        if ($output && $output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $output->writeln('Executing <info>'.$this->bin.' '.$command.'</info>');
        }

        $process = new Process($this->bin.' '.$command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process;
    }

    /**
     * @param string $mainFile
     * @param string $resourceDir
     * @param OutputInterface $output
     * @return string
     */
    protected function concatDocFiles($mainFile, $resourceDir = 'doc/api', OutputInterface $output = null)
    {
        $fs = new Filesystem();
        $finder = new Finder();

        $bundles = $this->kernel->getBundles();
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
            if ($output && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $relativePath  = $fs->makePathRelative($file->getRealpath(), $this->projectDir);
                $output->writeln('concatenate <comment>' . $relativePath . '</comment>');
            }
            file_put_contents($tempFile, "\n\n".$file->getContents(), FILE_APPEND);
        }

        $fs->rename($tempFile, $tempFile.'.md');

        return $tempFile.'.md';
    }
}