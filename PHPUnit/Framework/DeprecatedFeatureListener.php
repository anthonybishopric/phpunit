<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Ralph Schindler <ralph.schindler@zend.com>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2010 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.6
 */

/**
 * A listener that is utilized to notify the developer that a deprecated feature was used in a test
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Ralph Schindler <ralph.schindler@zend.com>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2010 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Interface available since Release 3.5.6
 */
class PHPUnit_Framework_DeprecatedFeatureListener implements PHPUnit_Framework_TestListener
{

    /**
     * This will minimally be a PHPUnit_Framework_Test, but most likely a PHPUnit_Framework_TestCase
     * 
     * @var PHPUnit_Framework_TestCase
     */
    protected static $currentTest = null;

    /**
     * This is the publically accessible API for notifying the system that a deprecated feature has been used
     * 
     * If it is run via a TestRunner and the test extends PHPUnit_Framework_TestCase, then this will inject
     * the result into the test runner for display, if not, it will throw the notice to STDERR.
     * 
     * @param string $message
     * @param int|bool $backtraceDepth
     */
    public static function log($message, $backtraceDepth = 2)
    {
        
        if ($backtraceDepth !== false) {
            $trace = debug_backtrace(false);
            
            if (is_int($backtraceDepth)) {
                $traceItem = $trace[$backtraceDepth];
            }
            
            // fill in missing file (debug_backtrace does not fill in line file from call_user_func)
            if (!isset($traceItem['file'])) {
                $reflectionClass = new ReflectionClass($traceItem['class']);
                $traceItem['file'] = $reflectionClass->getFileName();
            }
            
            // fill in missing line (debug_backtrace does not fill in line file from call_user_func)
            if (!isset($traceItem['line']) && isset($traceItem['class']) && isset($traceItem['function'])) {
                $reflectionClass = (isset($reflectionClass)) ? $reflectionClass : new ReflectionClass($traceItem['class']);
                $methodReflection = $reflectionClass->getMethod($traceItem['function']);
                $traceItem['line'] = '(between ' . $methodReflection->getStartLine() . ' and ' . $methodReflection->getEndLine() . ')';
            }
        }
        
        $deprecatedFeature = new PHPUnit_Framework_DeprecatedFeature($message, $traceItem);
        
        if (self::$currentTest instanceof PHPUnit_Framework_TestCase) {
            /* @var $result PHPUnit_Framework_TestResult */
            $result = self::$currentTest->getResult();
            $result->addDeprecatedFeature($deprecatedFeature);
        } else {
            file_put_contents('php://stderr', $deprecatedFeature->__toString());
        }
    }
    
    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {}

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {}

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {}

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {}

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {}

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {}

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        self::$currentTest = $test;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        self::$currentTest = null;
    }
    
}
