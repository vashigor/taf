<?php
/**
 * MTAF Simplified
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to vash.igor(at)gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * The Original Work is provided under this License on an "AS IS" BASIS and WITHOUT WARRANTY,
 * either express or implied, including, without limitation, the warranties of non-infringement,
 * merchantability or fitness for a particular purpose.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Igor Tkachenko <vash.igor(at)gmail.com>
 * @copyright   Copyright (c) 2013 Igor Tkachenko (https://github.com/vashigor/taf)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Bundle Dynamic product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Product_Create_BundleTest extends Core_Mage_Product_Create_BundleTest
{

    /**
     * <p>Simplified list of empty fields.</p>
     *
     * @see Core_Mage_Product_Create_BundleTest::emptyRequiredFieldInBundleDataProvider()
     */
    public function emptyRequiredFieldInBundleDataProvider()
    {
        return array(
            array(array('general_name' => '%noValue%'), 'field'),
            array(array('prices_price' => '%noValue%'), 'field')
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     *
     * @see Core_Mage_Product_Create_BundleTest::invalidNumericFieldDataProvider()
     */
    public function invalidNumericFieldDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:'))
        );
    }
}