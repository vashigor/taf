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
 * Product creation with custom options tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Product_Create_CustomOptionsTest extends Core_Mage_Product_Create_CustomOptionsTest
{

    /**
     * <p>Simplified list of empty options.</p>
     *
     * @see Core_Mage_Product_Create_CustomOptionsTest::emptyOptionRowTitleInCustomOptionDataProvider()
     */
    public function emptyOptionRowTitleInCustomOptionDataProvider()
    {
        return array(
            array('custom_options_multipleselect')
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     *
     * @see Core_Mage_Product_Create_CustomOptionsTest::invalidNumericValueDataProvider()
     */
    public function invalidNumericValueDataProvider()
    {
        return array(
            array('g3648GJTest')
        );
    }

    /**
     * <p>Simplified list of options with negative price.</p>
     *
     * @see Core_Mage_Product_Create_CustomOptionsTest::negativeNumberInCustomOptionsPricePosDataProvider()
     */
    public function negativeNumberInCustomOptionsPricePosDataProvider()
    {
        return array(
            array('custom_options_area')
        );
    }
}