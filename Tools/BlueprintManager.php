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

class BlueprintManager
{
    /**
     * aglio bin path
     *
     * @var string
     */
    protected $aglioBin;

    /**
     * snowcrash bin path
     *
     * @var string
     */
    protected $snowcrashBin;

    /**
     * apiary2postman bin path
     *
     * @var string
     */
    protected $apiary2postmanBin;

    /**
     * @var array
     */
    protected $replacements;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @param $kernel
     * @param $aglioBin
     * @param $snowcrashBin
     * @param $apiary2postmanBin
     * @param string $relativeProjectDir
     */
    public function __construct($kernel, $aglioBin, $snowcrashBin, $apiary2postmanBin, $replacements = array(), $relativeProjectDir = '/..')
    {
        $this->aglioBin = $aglioBin;
        $this->snowcrashBin = $snowcrashBin;
        $this->apiary2postmanBin = $apiary2postmanBin;
        $this->kernel = $kernel;
        $this->projectDir = realpath($this->kernel->getRootDir().$relativeProjectDir);
        $this->replacements = $replacements;
    }

    /**
     * @param OutputInterface $output
     * @return array
     */
    public function getAvailableTemplates(OutputInterface $output = null)
    {
        return explode("\n", trim(str_ireplace('Templates:', '', $this->executeAglio('-l', $output)->getOutput())));
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
    public function generateDoc($mainBlueprint, $outputHtml, $concat = false, $resourceDir = 'doc/api', $template = null, OutputInterface $output = null)
    {
        $fs = new Filesystem();

        $inputBlueprint = $concat ? $this->concatFiles($mainBlueprint, $resourceDir, null, $output) : $mainBlueprint;

        $outputHtmlDir = dirname($outputHtml);
        if (!$fs->exists($outputHtmlDir)) {
            $fs->mkdir($outputHtmlDir);
        }

        $inputBlueprint = $this->replacePatterns($inputBlueprint);

        if (!in_array($template, $this->getAvailableTemplates($output))) {
            $template = 'default';
        }

        $inputBlueprint = $this->replacePatterns($inputBlueprint);

        $result = $this->executeAglio('-t '.$template.' -i '.$inputBlueprint.' -o '.$outputHtml, $output);
        if ($concat && $mainBlueprint != $inputBlueprint) {
            $fs->remove($inputBlueprint);
        }

        return $result->getOutput();
    }

    /**
     * @param $mainBlueprint
     * @param $outputHtml
     * @param string $format
     * @param bool $concat
     * @param string $resourceDir
     * @param OutputInterface $output
     * @return string
     */
    public function generateBlueprint($mainBlueprint, $outputHtml, $format = 'json', $concat = false, $resourceDir = 'doc/api', OutputInterface $output = null)
    {
        $fs = new Filesystem();

        $inputBlueprint = $concat ? $this->concatFiles($mainBlueprint, $resourceDir, null, $output) : $mainBlueprint;

        $outputHtmlDir = dirname($outputHtml);
        if (!$fs->exists($outputHtmlDir)) {
            $fs->mkdir($outputHtmlDir);
        }

        $inputBlueprint = $this->replacePatterns($inputBlueprint);

        $result = $this->executeSnowcrash(' -o '.$outputHtml.' --format '.$format.' '.$inputBlueprint, $output);
        if ($concat && $mainBlueprint != $inputBlueprint) {
            $fs->remove($inputBlueprint);
        }

        return $result->getOutput();
    }

    /**
     * @param $mainBlueprint
     * @param $target
     * @param string $resourceDir
     * @param OutputInterface $output
     * @return string
     */
    public function concatenateDoc($mainBlueprint, $target, $resourceDir = 'doc/api', OutputInterface $output = null)
    {
        $fs = new Filesystem();

        $targetDir = dirname($target);
        if (!$fs->exists($targetDir)) {
            $fs->mkdir($targetDir);
        }

        $target = $this->concatFiles($mainBlueprint, $resourceDir, $target, $output);

        return $this->replacePatterns($target, true);
    }

    /**
     * @param string $mainFile
     * @param string $resourceDir
     * @param null $target
     * @param OutputInterface $output
     * @return string
     */
    protected function concatFiles($mainFile, $resourceDir = 'doc/api', $target = null, OutputInterface $output = null)
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

        if (empty($target)) {
            $target = tempnam(sys_get_temp_dir(), 'api_');
        }

        $infos = pathinfo($target);
        if (empty($infos['extension']) || $infos['extension'] != 'md') {
            $target = $infos['dirname'].'/'.$infos['filename'].'.md';
        }

        $fs->touch($target);
        if($fs->exists($mainFile)) {
            file_put_contents($target, file_get_contents($mainFile));
        }

        if (!empty($dirToScan)) {
            $finder
                ->files()
                ->name('*.md')
                ->sortByName();

            $finder->in($dirToScan);

            foreach ($finder as $file) {
                if ($output && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $relativePath = $fs->makePathRelative($file->getRealpath(), $this->projectDir);
                    $output->writeln('concatenate <comment>'.$relativePath.'</comment>');
                }
                file_put_contents($target, "\n\n".$file->getContents(), FILE_APPEND);
            }
        }

        return $target;
    }

    public function executeAglio($command, OutputInterface $output = null)
    {
        return $this->execute($this->aglioBin, $command, $output);
    }

    public function executeSnowcrash($command, OutputInterface $output = null)
    {
        return $this->execute($this->snowcrashBin, $command, $output);
    }

    public function executeApiary2Postman($command, OutputInterface $output = null)
    {
        return $this->execute($this->apiary2postmanBin, $command, $output);
    }

    /**
     * @param string $command
     * @param OutputInterface $output
     * @return Process
     */
    protected function execute($bin, $command, OutputInterface $output = null)
    {
        if ($output && $output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $output->writeln('Executing <info>'.$bin.' '.$command.'</info>');
        }

        $process = new Process($bin.' '.$command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process;
    }

    /**
     * @param $file
     * @param bool $inFile
     * @return string filename
     */
    protected function replacePatterns($file, $inFile = false)
    {
        if (empty($this->replacements)) {
            return $file;
        }

        $fs = new Filesystem();

        $patterns = array();
        $replaces = array();
        foreach ($this->replacements as $pattern => $replace) {
            $patterns[] = '#'.$pattern.'#';
            $replaces[] = $replace;
        }

        $content = file_get_contents($file);
        $updatedContent = preg_replace($patterns, $replaces, $content);

        if ($inFile) {
            $newFile = $file;
        } else {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $newFile = tempnam(sys_get_temp_dir(), 'api_replaced_').($extension ? '.'.$extension : '');
        }

        $fs->dumpFile($newFile, $updatedContent);

        return $newFile;
    }
}