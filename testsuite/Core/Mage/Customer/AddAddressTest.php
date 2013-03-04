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
 * <p>Add address tests.</p>
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Customer_AddAddressTest extends Mage_Selenium_TestCase
{
    protected static $_customerTitleParameter = '';

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Manage Customers</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_customers');
        $this->addParameter('elementTitle', self::$_customerTitleParameter);
    }

    /**
     * <p>Create customer for add customer address tests</p>
     * @group preConditions
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        $searchData = $this->loadDataSet('Customers', 'search_customer', array('email' => $userData['email']));
        self::$_customerTitleParameter = $userData['first_name'] . ' ' . $userData['last_name'];
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');

        return $searchData;
    }

    /**
     * <p>Add address for customer. Fill in only required field.</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in required fields.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function withRequiredFieldsOnly(array $searchData)
    {
        //Data

        $addressData = $this->loadDataSet('Customers', 'generic_address');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
    }

    /**
     * Add Address for customer with one empty required field.
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in fields except one required.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address isn't added. Customer info is not saved.</p>
     * <p>Error Message is displayed</p>
     *
     * @param string $emptyField
     * @param array $searchData
     *
     * @test
     * @depends preconditionsForTests
     * @dataProvider withRequiredFieldsEmptyDataProvider
     */
    public function withRequiredFieldsEmpty($emptyField, $searchData)
    {
        //Data
        if ($emptyField != 'country') {
            $addressData = $this->loadDataSet('Customers', 'generic_address', array($emptyField => ''));
        } else {
            $addressData = $this->loadDataSet('Customers', 'generic_address', array($emptyField => '',
                                                                                    'state'     => '%noValue%'));
        }
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying
        // Defining and adding %fieldXpath% for customer Uimap
        $fieldSet = $this->getUimapPage('admin', 'edit_customer')->findFieldset('edit_address');
        if ($emptyField != 'country' and $emptyField != 'state') {
            $fieldXpath = $fieldSet->findField($emptyField);
        } else {
            $fieldXpath = $fieldSet->findDropdown($emptyField);
        }
        if ($emptyField == 'street_address_line_1') {
            $fieldXpath .= "/ancestor::div[@class='multi-input']";
        }
        $this->addParameter('fieldXpath', $fieldXpath);

        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('first_name'),
            array('last_name'),
            array('street_address_line_1'),
            array('city'),
            array('country'),
            //array('state'), //Fails because of MAGE-1424 // Should be required only if country='United States'
            array('zip_code'),
            array('telephone')
        );
    }

    /**
     * <p>Add address for customer. Fill in only required field. Use this address as Default Billing.</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in required fields.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function withDefaultBillingAddress(array $searchData)
    {
        //Data
        $addressData = $this->loadDataSet('Customers', 'all_fields_address', array('default_shipping_address' => 'No'));
        //Steps
        // 1.Open customer
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('addresses');
        //Verifying #–2 - Check saved values
        $addressNumber = $this->customerHelper()->isAddressPresent($addressData);
        $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
    }

    /**
     * <p>Add address for customer. Fill in only required field. Use this address as Default Shipping.</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in required fields.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function withDefaultShippingAddress(array $searchData)
    {
        $addressData = $this->loadDataSet('Customers', 'all_fields_address', array('default_billing_address' => 'No'));
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('addresses');
        //Verifying #–2 - Check saved values
        $addressNumber = $this->customerHelper()->isAddressPresent($addressData);
        $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
    }

    /**
     * <p>Add address for customer. Fill in all fields by using special characters(except the field "country").</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in fields by long value alpha-numeric data except 'country' field.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed.</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function withSpecialCharactersExceptCountry(array $searchData)
    {
        //Data
        $addressData = $this->loadDataSet('Customers', 'special_char_address');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying #–1
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('addresses');
        //Verifying #–2 - Check saved values
        $addressNumber = $this->customerHelper()->isAddressPresent($addressData);
        $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
    }

    /**
     * <p>Add address for customer. Fill in only required field. Use max long values for fields.</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in fields by long value alpha-numeric data except 'country' field.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed. Length of fields are 255 characters.</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function withLongValuesExceptCountry(array $searchData)
    {
        //Data
        $addressData = $this->loadDataSet('Customers', 'long_values_address');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying #–1
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('addresses');
        //Verifying #–2 - Check saved values
        $addressNumber = $this->customerHelper()->isAddressPresent($addressData);
        $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
    }
}