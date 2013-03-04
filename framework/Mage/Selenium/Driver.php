<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implementation of the Selenium RC client/server protocol.
 * Extension: logging of all client/server protocol transactions to the 'selenium-rc-DATE.log' file.
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Driver extends PHPUnit_Extensions_SeleniumTestCase_Driver
{
    /**
     * Handle to log file
     * @var null|resource
     */
    protected $_logHandle = null;

    public function stop()
    {
    }

    /**
     * Stop browser session
     */
    public function stopBrowserSession()
    {
        if (!isset($this->sessionId)) {
            return;
        }
        $this->doCommand('testComplete');
        $this->sessionId = NULL;
    }

    /**
     * Sends a command to the Selenium RC server.
     * Extension: transaction logging to opened file stream in view: TIME,REQUEST,RESPONSE or TIME,EXCEPTION
     *
     * @param string $command Command for send to Selenium RC server
     * @param array $arguments Array of arguments to command
     * @param array $namedArguments
     *
     * @throws Exception
     * @return string
     */
    protected function doCommand($command, array $arguments = array(), array $namedArguments = array())
    {
        try {
            $response = parent::doCommand($command, $arguments, $namedArguments);
            // Add command logging
            if (!empty($this->_logHandle)) {
                fputs($this->_logHandle, self::udate('H:i:s.u') . "\n");
                fputs($this->_logHandle, "\tRequest: " . $command . "\n");
                if ($command == 'captureEntirePageScreenshotToString' || $command == 'getHtmlSource') {
                    fputs($this->_logHandle, "\tResponse: OK\n\n");
                } else {
                    fputs($this->_logHandle, "\tResponse: " . $response . "\n\n");
                }
                fflush($this->_logHandle);
            }
            return $response;
        } catch (Exception $e) {
            if (!empty($this->_logHandle)) {
                fputs($this->_logHandle, self::udate('H:i:s.u') . "\n");
                fputs($this->_logHandle, "\tRequest: " . $command . "\n");
                fputs($this->_logHandle, "\tException: " . $e->getMessage() . "\n\n");
                fflush($this->_logHandle);
            }
            throw $e;
        }
    }

    /**
     * Set log file
     *
     * @param $file
     */
    public function setLogHandle($file)
    {
        $this->_logHandle = $file;
    }

    /**
     * Get browser settings
     * @return array
     */
    public function getBrowserSettings()
    {
        return array('timeout' => $this->seleniumTimeout, 'name' => $this->name, 'browser' => $this->browser,
                     'host'    => $this->host, 'port' => $this->port);
    }

    /**
     * Get current time for logging (e.g. 15:18:43.244768)
     *
     * @static
     *
     * @param string $format A composite format string
     * @param mixed $uTimeStamp Timestamp (by default = null)
     *
     * @return string A formatted date string.
     */
    public static function udate($format, $uTimeStamp = null)
    {
        if (is_null($uTimeStamp)) {
            $uTimeStamp = microtime(true);
        }

        $timestamp = floor($uTimeStamp);
        $milliseconds = round(($uTimeStamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}
