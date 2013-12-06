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
 * One page Checkout  - checking validation tests

 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Community1800_Mage_CheckoutOnePage_WithRegistration_CheckingValidationTest extends Core_Mage_CheckoutOnePage_WithRegistration_CheckingValidationTest
{

    /**
     * <p>Creating Simple product</p>
     * <p>This overriding is required, to restore the certain tests order.</p>
     *
     * @test
     * @return string
     * @see Core_Mage_CheckoutOnePage_WithRegistration_CheckingValidationTest::preconditionsForTests()
     */
    public function preconditionsForTests()
    {
        return parent::preconditionsForTests();
    }

    /**
     * <p>Empty required fields in billing address tab</p>
     * <p>This overriding fixes the bug with state that appears when country is empty.</p>
     *
     * @param string $field
     * @param string $message
     * @param string $simpleSku
     *
     * @test
     * @dataProvider emptyRequiredFieldsInBillingAddressDataProvider
     * @depends preconditionsForTests
     * @see Core_Mage_CheckoutOnePage_WithRegistration_CheckingValidationTest::emptyRequiredFieldsInBillingAddress()
     */
    public function emptyRequiredFieldsInBillingAddress($field, $message, $simpleSku)
    {
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', 'with_register_flatrate_checkmoney_different_address',
                                           array('general_name' => $simpleSku,
                                                $field          => ''));
        if ($field == 'billing_password') {
            $message .= "\n" . '"Confirm Password": Please make sure your passwords match.';
        }

        if ('billing_country' == $field)
        {
            unset($checkoutData['billing_address_data']['billing_state']);
        }

        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $message);
        //Steps
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
    }

    /**
     * <p>Empty required fields in shipping address tab</p>
     * <p>This overriding fixes the bug with state that appears when country is empty.</p>
     *
     * @param string $field
     * @param string $message
     * @param string $simpleSku
     *
     * @test
     * @dataProvider emptyRequiredFieldsInShippingAddressDataProvider
     * @depends preconditionsForTests
     * @see Core_Mage_CheckoutOnePage_WithRegistration_CheckingValidationTest::emptyRequiredFieldsInShippingAddress()
     */
    public function emptyRequiredFieldsInShippingAddress($field, $message, $simpleSku)
    {
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', 'with_register_flatrate_checkmoney_different_address',
                                           array('general_name' => $simpleSku,
                                                $field          => ''));

        if ('shipping_country' == $field)
        {
            unset($checkoutData['shipping_address_data']['shipping_state']);
        }

        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $message);
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
    }

}
