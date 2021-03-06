<?php

namespace Kilix\Bundle\ApiCoreBundle\Tools;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;

class BlueprintManager
{
    /**
     * Possible Blueprint to postman converters.
     *
     * @var array
     */
    protected static $postmanConverters = array(
        'blueman',
        'apiary2postman',
    );

    /**
     * aglio bin path.
     *
     * @var string
     */
    protected $aglioBin;

    /**
     * blueprint parser bin path.
     *
     * @var string
     */
    protected $blueprintParserBin;

    /**
     * apiary2postman bin path.
     *
     * @var string
     */
    protected $apiary2postmanBin;

    /**
     * blueman Bin bin path.
     *
     * @var string
     */
    protected $bluemanBin;

    /**
     * Postman Converter to use by default.
     *
     * @var string
     */
    protected $defaultPostmanConverter;

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
     * @param $blueprintParserBin
     * @param $apiary2postmanBin
     * @param $bluemanBin
     * @param string $defaultPostmanConverter
     * @param array  $replacements
     * @param string $relativeProjectDir
     */
    public function __construct($kernel, $aglioBin, $blueprintParserBin, $apiary2postmanBin, $bluemanBin, $defaultPostmanConverter = 'blueman', $replacements = array(), $relativeProjectDir = '/..')
    {
        $this->aglioBin = $aglioBin;
        $this->blueprintParserBin = $blueprintParserBin;
        $this->apiary2postmanBin = $apiary2postmanBin;
        $this->bluemanBin = $bluemanBin;
        $this->defaultPostmanConverter = in_array($defaultPostmanConverter, static::$postmanConverters) ? $defaultPostmanConverter : 'blueman';
        $this->kernel = $kernel;
        $this->projectDir = realpath($this->kernel->getRootDir().$relativeProjectDir);
        $this->replacements = $replacements;
    }

    /**
     * @param OutputInterface $output
     *
     * @return array
     */
    public function getAvailableTemplates()
    {
        return array(
            'olio',
            'default',
            'default-collapsed',
            'flatly',
            'flatly-collapsed',
            'slate',
            'slate-collapsed',
            'cyborg',
            'cyborg-collapsed',
        );
    }

    /**
     * @param $mainBlueprint
     * @param $outputHtml
     * @param bool            $concat
     * @param string          $resourceDir
     * @param null            $template
     * @param OutputInterface $output
     *
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

        if (!in_array($template, $this->getAvailableTemplates())) {
            $template = 'default';
        }

        $inputBlueprint = $this->replacePatterns($inputBlueprint);

        $result = $this->executeAglio('--theme-template '.$template.' -i '.$inputBlueprint.' -o '.$outputHtml, $output);
        if ($concat && $mainBlueprint != $inputBlueprint) {
            $fs->remove($inputBlueprint);
        }

        return $result->getOutput();
    }

    public function generatePostman($mainBlueprint, $outputPostman, $pretty = true, $concat = false, $replace = true, $converterToUse = 'blueman', $resourceDir = 'doc/api', OutputInterface $output = null)
    {
        $converterToUse = in_array($converterToUse, static::$postmanConverters) ? $converterToUse : $this->defaultPostmanConverter;

        $blueprintJson = $target = tempnam(sys_get_temp_dir(), 'api_blueprint_').'.json';
        $this->generateBlueprint($mainBlueprint, $blueprintJson, 'json', $concat, $replace, $resourceDir, $output);

        $fs = new Filesystem();

        $outputDir = dirname($outputPostman);
        if (!$fs->exists($outputDir)) {
            $fs->mkdir($outputDir);
        }

        if ($converterToUse == 'apiary2postman') {
            $result = $this->executeApiary2Postman(
                ($pretty ? '--pretty ' : '').'--only-collection --output '.$outputPostman.' json '.$blueprintJson,
                $output
            );
        } else {
            $dirname = dirname($blueprintJson);
            $filename = basename($blueprintJson);

            $result = $this->executeBlueman(
                'convert --output='.$outputPostman.' --path='.$dirname.' '.$filename,
                $output
            );
        }
        $fs->remove($blueprintJson);

        return $result->getOutput();
    }

    /**
     * @param $mainBlueprint
     * @param $outputHtml
     * @param string          $format
     * @param bool            $concat
     * @param bool            $replace
     * @param string          $resourceDir
     * @param OutputInterface $output
     *
     * @return string
     */
    public function generateBlueprint($mainBlueprint, $outputHtml, $format = 'json', $concat = false, $replace = true, $resourceDir = 'doc/api', OutputInterface $output = null)
    {
        $fs = new Filesystem();

        $inputBlueprint = $concat ? $this->concatFiles($mainBlueprint, $resourceDir, null, $output) : $mainBlueprint;

        $outputHtmlDir = dirname($outputHtml);
        if (!$fs->exists($outputHtmlDir)) {
            $fs->mkdir($outputHtmlDir);
        }

        if ($replace) {
            $inputBlueprint = $this->replacePatterns($inputBlueprint);
        }

        $result = $this->executeBlueprintParser(' -o '.$outputHtml.' --format '.$format.' '.$inputBlueprint, $output);
        if ($concat && $mainBlueprint != $inputBlueprint) {
            $fs->remove($inputBlueprint);
        }

        return $result->getOutput();
    }

    /**
     * @param $mainBlueprint
     * @param $target
     * @param string          $resourceDir
     * @param bool            $replace
     * @param OutputInterface $output
     *
     * @return string
     */
    public function concatenateDoc($mainBlueprint, $target, $resourceDir = 'doc/api', $replace = true, OutputInterface $output = null)
    {
        $fs = new Filesystem();

        $targetDir = dirname($target);
        if (!$fs->exists($targetDir)) {
            $fs->mkdir($targetDir);
        }

        $target = $this->concatFiles($mainBlueprint, $resourceDir, $target, $output);

        if ($replace) {
            $target = $this->replacePatterns($target, true);
        }

        return $target;
    }

    /**
     * @param string          $mainFile
     * @param string          $resourceDir
     * @param null            $target
     * @param OutputInterface $output
     *
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
        if ($fs->exists($mainFile)) {
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

    public function executeBlueprintParser($command, OutputInterface $output = null)
    {
        return $this->execute($this->blueprintParserBin, $command, $output);
    }

    public function executeApiary2Postman($command, OutputInterface $output = null)
    {
        return $this->execute($this->apiary2postmanBin, $command, $output);
    }

    public function executeBlueman($command, OutputInterface $output = null)
    {
        return $this->execute($this->bluemanBin, $command, $output);
    }

    /**
     * @param string          $command
     * @param OutputInterface $output
     *
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
     *
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
