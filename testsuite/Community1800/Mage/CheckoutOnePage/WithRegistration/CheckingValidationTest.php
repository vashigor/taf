<?php

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
     * <p>Fixes bug with state when country is empty.</p>
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
     * <p>Fix bug with state when country is empty.</p>
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
