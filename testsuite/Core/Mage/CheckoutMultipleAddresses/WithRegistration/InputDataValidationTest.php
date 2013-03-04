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
 * Tests for Checkout with Multiple Addresses. Frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutMultipleAddresses_WithRegistration_InputDataValidationTest extends Mage_Selenium_TestCase
{
    protected function tearDownAfterTest()
    {
        $this->logoutCustomer();
        $this->shoppingCartHelper()->frontClearShoppingCart();
    }

    /**
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        $this->loginAdminUser();
        $simple1 = $this->productHelper()->createSimpleProduct();
        $simple2 = $this->productHelper()->createSimpleProduct();
        return array('product_1' => $simple1['simple']['product_name'],
                     'product_2' => $simple2['simple']['product_name']);
    }

    /**
     * <p>Customer registration.  Use email that already exist.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in 'Email' field by using code that already exist.</p>
     * <p>7. Fill other required fields by regular data.</p>
     * <p>8. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function withEmailThatAlreadyExists(array $testData)
    {
        $message = 'There is already an account with this email address. '
            . 'If you are sure that it is your email address, click here to get your password and access your account.';
        //Data
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_register',
                                           array('email'=> $userData['email']), $testData);
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $message);
        $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
    }

    /**
     * <p>Customer registration. Fill in only required fields. Use max long values for fields.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in required fields by long value alpha-numeric data.</p>
     * <p>7. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is registered. Success Message is displayed.</p>
     * <p>Length of fields are 255 characters.</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function withLongValues($testData)
    {
        //Data
        $address = $this->loadDataSet('MultipleAddressesCheckout', 'register_data_long');
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_register',
                                           array('general_customer_data' => $address),
                                           $testData);
        //Steps
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
        $this->assertMessagePresent('success', 'success_checkout');
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    /**
     * <p>Customer registration with empty required field.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in fields except one required.</p>
     * <p>7. Click 'Submit' button</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $field
     * @param string $fieldName
     * @param array $testData
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends preconditionsForTests
     */
    public function withRequiredFieldsEmpty($field, $fieldName, $testData)
    {
        //Data
        $address = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_register/general_customer_data',
                                      array($field => ''));
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_register',
                                           array('general_customer_data' => $address),
                                           $testData);
        //Steps
        if ($field == 'state' || $field == 'country') {
            $message = '"' . $fieldName . '": Please select an option.';
        } else {
            $message = '"' . $fieldName . '": This is a required field.';
        }
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $message);
        $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('first_name', 'First Name'),
            array('last_name', 'Last Name'),
            array('email', 'Email Address'),
            array('telephone', 'Telephone'),
            array('street_address_1', 'Street Address'),
            array('city', 'City'),
            array('state', 'State/Province'),
            array('zip_code', 'Zip/Postal Code'),
            array('country', 'Country'),
            array('password', 'Password'),
            array('password_confirmation', 'Confirm Password')
        );
    }

    /**
     * <p>Customer registration. Fill in all required fields by using special characters(except the field "email").</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in all required fields by using special characters(except the field "email").</p>
     * <p>7. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is registered.</p>
     * <p>Success Message is displayed</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function withSpecialCharacters($testData)
    {
        //Data
        $address = $this->loadDataSet('MultipleAddressesCheckout', 'register_data_special');
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_register',
                                           array('general_customer_data' => $address),
                                           $testData);
        //Steps
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
        $this->assertMessagePresent('success', 'success_checkout');
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    /**
     * <p>Customer registration with invalid value for 'Email' field</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in 'Email' field by wrong value.</p>
     * <p>7. Fill other required fields by regular data.</p>
     * <p>8. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $invalidEmail
     * @param array $testData
     *
     * @test
     * @dataProvider withInvalidEmailDataProvider
     * @depends preconditionsForTests
     */
    public function withInvalidEmail($invalidEmail, $testData)
    {
        //Data
        $address = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_register/general_customer_data',
                                      array('email' => $invalidEmail));
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_register',
                                           array('general_customer_data' => $address),
                                           $testData);
        $message = '"Email Address": Please enter a valid email address. For example johndoe@domain.com.';
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $message);
        //Steps
        $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
    }

    public function withInvalidEmailDataProvider()
    {
        return array(
            array('invalid'),
            array('test@invalidDomain'),
            array('te@st@unknown-domain.com'),
            array('.test@unknown-domain.com'),
        );
    }

    /**
     * <p>Customer registration with invalid value for 'Password' fields</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in 'password' fields by wrong value.</p>
     * <p>7. Fill other required fields by regular data.</p>
     * <p>8. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $invalidPassword
     * @param string $errorMessage
     * @param array $testData
     *
     * @test
     * @dataProvider withInvalidPasswordDataProvider
     * @depends preconditionsForTests
     */
    public function withInvalidPassword($invalidPassword, $errorMessage, $testData)
    {
        //Data
        $address = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_register/general_customer_data',
                                      $invalidPassword);
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_register',
                                           array('general_customer_data' => $address),
                                           $testData);
        //Steps
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $errorMessage);
        $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
    }

    public function withInvalidPasswordDataProvider()
    {
        return array(
            array(array('password'              => 12345,
                        'password_confirmation' => 12345),
                  '"Password": Please enter 6 or more characters. Leading or trailing spaces will be ignored.'),
            array(array('password'              => 1234567,
                        'password_confirmation' => 12345678),
                  '"Confirm Password": Please make sure your passwords match.'),
        );
    }
}
