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
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer registration tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Installation extends Mage_Selenium_TestCase
{
    /**
     * it's need to add some data before running test('database_name', 'user_name', 'user_password', 'base_url')
     * to 'install_magento' dataset
     */
    protected function assertPreConditions()
    {
        $data = $this->loadDataSet('Installation', 'install_magento/configuration');
        $host = $data['host'];
        $user = $data['user_name'];
        $password = $data['user_password'];
        $baseName = $data['database_name'];
        mysql_connect($host, $user, $password) or die("Couldn't connect to MySQL server!");
        mysql_query("DROP DATABASE IF EXISTS `$baseName`");
        mysql_query("CREATE DATABASE `$baseName`") or die("Couldn't create DATABASE!");
        //for local build
        //$this->installationHelper()->removeInstallData();
    }

    /**
     * @test
     */
    public function installTest()
    {
        $data = $this->loadDataSet('Installation', 'install_magento');
        $this->installationHelper()->installMagento($data);
    }
}