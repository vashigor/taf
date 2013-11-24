<?php

/**
 * One page Checkout tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Community1800_Mage_CheckoutOnePage_LoggedIn_CheckingValidationTest extends Core_Mage_CheckoutOnePage_LoggedIn_CheckingValidationTest
{
    
    /**
     * <p>Creating Simple product and customer</p>
     *
     * @return array
     * @test
     * @see Core_Mage_CheckoutOnePage_LoggedIn_CheckingValidationTest::preconditionsForTests()
     */
    public function preconditionsForTests()
    {
        return parent::preconditionsForTests();
    }

    /**
     * <p>Empty required fields in billing address tab...</p>
     * <p>Fix bug with state when country is empty.</p>
     *
     * @param string $field
     * @param string $message
     * @param array $data
     *
     * @test
     * @dataProvider addressEmptyFieldsDataProvider
     * @depends preconditionsForTests
     * @see Core_Mage_CheckoutOnePage_LoggedIn_CheckingValidationTest::emptyRequiredFieldsInBillingAddress
     */
    public function emptyRequiredFieldsInBillingAddress($field, $message, $data)
    {
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', 'signedin_flatrate_checkmoney_different_address',
                                           array('general_name'      => $data['sku'],
                                                'billing_' . $field  => ''));
        
        if ('country' == $field)
        {
            unset($checkoutData['billing_address_data']['billing_state']);
        }
        
        //Steps
        $this->customerHelper()->frontLoginCustomer($data['customer']);
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $message);
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
    }

    /**
     * <p>Empty required fields in shipping address tab</p>
     * <p>Fix bug with state when country is empty.</p>
     *
     * @param string $field
     * @param string $message
     * @param array $data
     *
     * @test
     * @dataProvider addressEmptyFieldsDataProvider
     * @depends preconditionsForTests
     * @see Core_Mage_CheckoutOnePage_LoggedIn_CheckingValidationTest::emptyRequiredFieldsInShippingAddress
     */
    public function emptyRequiredFieldsInShippingAddress($field, $message, $data)
    {
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', 'signedin_flatrate_checkmoney_different_address',
                                           array('general_name'       => $data['sku'],
                                                'shipping_' . $field  => ''));
        
        if ('country' == $field)
        {
            unset($checkoutData['shipping_address_data']['shipping_state']);
        }
        
        //Steps
        $this->customerHelper()->frontLoginCustomer($data['customer']);
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $message);
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
    }
    
}