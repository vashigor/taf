<?php

/**
 * Tests for payment methods. Frontend - OnePageCheckout
 *
 * @method Simplified_Mage_Correction_Helper correctionHelper()
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_CheckoutOnePage_LoggedIn_PaymentMethodsTest extends Core_Mage_CheckoutOnePage_LoggedIn_PaymentMethodsTest
{

    /**
     * <p>We don't initialize PayPal and we shouldn't tear it down.</p>
     * @see Core_Mage_CheckoutOnePage_Existing_PaymentMethodsTest::tearDownAfterTestClass()
     */
    protected function tearDownAfterTestClass()
    {
        $this->loginAdminUser();
        $this->systemConfigurationHelper()->useHttps('frontend', 'no');
    }

    /**
     * <p>Creating Simple product</p>
     * <p>We've removed the paypal authentication and configuration for simplified tests.</p>
     *
     * @return string
     * @test
     * @see Core_Mage_CheckoutOnePage_LoggedIn_PaymentMethodsTest::preconditionsForTests()
     */
    public function preconditionsForTests()
    {
        //Data
        $simple = $this->loadDataSet('Product', 'simple_product_visible');
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');

        // Work with PayPal has been removed.
        $accounts = $this->correctionHelper()->getPaypalLikeVisaAccount();
        return array('sku'  => $simple['general_name'], 'api' => array(), 'visa'=> $accounts['visa']['credit_card'],
                     'user' => array('email' => $userData['email'], 'password' => $userData['password']));
    }

    /**
     * <p>Providing the simplified list ot payment methods.</p>
     * @see Core_Mage_CheckoutOnePage_Existing_PaymentMethodsTest::differentPaymentMethodsWithout3DDataProvider()
     */
    public function differentPaymentMethodsWithout3DDataProvider()
    {
        return $this->correctionHelper()->getPaymentMethodsForDataProvider();
    }

    /**
     * <p>We don't need tests with 3D secure in the Simplified mode.</p>
     * @see Core_Mage_CheckoutOnePage_Existing_PaymentMethodsTest::differentPaymentMethodsWith3D()
     */
    public function differentPaymentMethodsWith3D($payment, $testData)
    {

    }

}