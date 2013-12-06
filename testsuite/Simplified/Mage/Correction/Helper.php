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
 * Helper class
 * This class provide the lists of tests' parameters for Simplified mode.
 *
 * @package     simplified
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Correction_Helper extends Mage_Selenium_TestCase
{

    /**
     * <p>Emulating credit card we get from PayPal Sandbox.</p>
     *
     * @return array
     */
    public function getPaypalLikeVisaAccount()
    {
        $cardData = $this->loadDataSet('Payment', 'saved_visa');
        $account = array(
                'visa' => array(
                        'credit_card' => $cardData
                )
        );
        return $account;
    }

    /**
     * <p>Providing simplified data set for payment method tests.</p>
     *
     * @return array
     */
    public function getPaymentMethodsForDataProvider()
    {
        return array(
                array('savedcc'),
                array('checkmoney')
        );
    }

    /**
     * <p>Providing simplified data set for shipping method tests.</p>
     *
     * @return array
     */
    public function getShippingMethodsForDataProvider()
    {
        return array(
            array('flatrate', null, 'usa'),
            array('free', null, 'usa')
        );
    }

}