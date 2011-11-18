<?php
/**
 * PHPCV
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@dasprids.de so I can send you a copy immediately.
 *
 * @category   PHPCV
 * @package    PHPCV_Cli
 * @copyright  Copyright (c) 2011 Ben Scholzen <mail@dasprids.de>
 * @license    New BSD License
 */

namespace PHPCV\Cli;

use PHPCV\Version,
    PHPCV\File\Tokenizer,
    PHPCV\Rule\Manager,
    RegexIterator,
    RecursiveDirectoryIterator,
    RecursiveIteratorIterator;

/**
 * CLI handler.
 * 
 * @category   PHPCV
 * @package    PHPCV_Cli
 * @copyright  Copyright (c) 2011 Ben Scholzen <mail@dasprids.de>
 * @license    New BSD License
 */
class Cli
{
    /**
     * Working directory.
     * 
     * @var string
     */
    protected $workingDirectory;
    
    /**
     * Standard to use.
     * 
     * @var string
     */
    protected $standard = 'ZF2';
    
    /**
     * Paths to scan.
     * 
     * @var array
     */
    protected $paths = array();
    
    /**
     * Create a new CLI object.
     * 
     * @param  string $workingDirectory
     * @return void
     */
    public function __construct($workingDirectory)
    {
        $this->workingDirectory = rtrim($workingDirectory, '/\\');
    }
    
    /**
     * Run PHPCV.
     * 
     * @param  array $arguments
     * @return void
     */
    public function run(array $arguments)
    {
        echo "PHPCV " . Version::getVersion() . " by Ben Scholzen.\n\n";
        
        $parser = new ArgumentParser($arguments, array(
            array(
                'code'    => 's',
                'name'    => 'standard',
                'has_arg' => true
            ),
            array(
                'code'    => 'h',
                'name'    => 'help',
                'has_arg' => false
            ),
            array(
                'code'    => 'v',
                'name'    => 'version',
                'has_arg' => false
            ),
        ));
        
        if ($parser->getError() !== null) {
            echo $parser->getError() . "\n";
            return;
        }
        
        $method  = 'analyzeFiles';
        
        foreach ($parser->getOptions() as $option) {
            switch ($option['code']) {
                case 's':
                    $this->standard = $option['argument'];
                    break;
                
                case 'h':
                    $method = 'printHelp';
                    break;
                
                case 'v':
                    return;
            }
        }
        
        foreach ($parser->getNonOptions() as $nonOption) {
            $this->paths[] = $nonOption;
        }
        
        $this->{$method}();
    }
    
    /**
     * Analyze files for coding standard violations.
     * 
     * @return void
     */
    protected function analyzeFiles()
    {
        if (!$this->paths) {
            $this->printHelp();
            return;
        }

        $paths = array();
        
        foreach ($this->paths as $path) {
            if (!file_exists($path) || !is_readable($path)) {
                echo "Cannot open " . $path . "\n";
            }
            
            if (is_dir($path)) {
                $paths[] = new RegexIterator(
                    new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($this->path)
                    ),
                    '(\.php$)i'
                );
            } else {
                $paths[] = $path;
            }
        }

        $manager   = new Manager(__DIR__ . '/../../../standards', '~/.phpcv/standards', $this->standard);
        $tokenizer = new Tokenizer();

        foreach ($paths as $path) {
            if (is_string($path)) {
                $file = $tokenizer->tokenize($path, file_get_contents($path));
                
                $manager->check($file);
            } else {
                foreach ($iterator as $fileInfo) {
                    $file = $tokenizer->tokenize($fileInfo->getPathname(), file_get_contents($fileInfo->getPathname()));

                    $manager->check($file);
                }
            }
        }
    }
    
    /**
     * Print help.
     * 
     * @return void
     */
    protected function printHelp()
    {
        echo "Usage: phpcv [switches] <directory>\n"
           . "       phpcv [switches] <file>\n\n"
           . "  -s, --standard=STANDARD Use specified coding standard\n"
           . "  -h, --help              Prints this usage information\n"
           . "  -v, --version           Print version information\n";
    }
}
