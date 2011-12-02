<?php
/**
 * Flitch
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@dasprids.de so I can send you a copy immediately.
 *
 * @category   Flitch
 * @package    Flitch_Rule
 * @subpackage File
 * @copyright  Copyright (c) 2011 Ben Scholzen <mail@dasprids.de>
 * @license    New BSD License
 */

namespace Flitch\Rule\File;

use Flitch\Rule\Rule,
    Flitch\File\File,
    Flitch\File\Error;

/**
 * Line endings rule.
 * 
 * @category   Flitch
 * @package    Flitch_Rule
 * @subpackage File
 * @copyright  Copyright (c) 2011 Ben Scholzen <mail@dasprids.de>
 * @license    New BSD License
 */
class LineEndings implements Rule
{
    /**
     * check(): defined by Rule interface.
     * 
     * @see    Rule::check()
     * @param  File  $file
     * @param  array $options
     * @return void
     */
    public function check(File $file, array $options = array())
    {
        $eolChar = "\n";
        $eolName = '\n';
        
        if (isset($options['eol-char'])) {
            switch ($options['eol-char']) {
                case '\r\n':
                    $eolChar = "\r\n";
                    $eolName = '\r\n';
                    break;
                
                case '\n':
                    $eolChar = "\n";
                    $eolName = '\n';
                    break;
                
                case '\r':
                    $eolChar = "\r";
                    $eolName = '\r';
                    break;
            }
        }

        foreach ($file->getLines() as $lineNo => $line) {
            if ($line['ending'] !== '' && $line['ending'] !== $eolChar) {
                $ending = str_replace(array("\r", "\n"), array('\r', '\n'), $line['ending']);

                $file->addError(new Error(
                    $lineNo, 0, Error::SEVERITY_ERROR,
                    sprintf('Line must end with "%s", found "%s"', $eolName, $ending),
                    $this
                ));
            }
        }
    }
}
