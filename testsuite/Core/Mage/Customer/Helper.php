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
 * Add address tests.
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Customer_Helper extends Mage_Selenium_TestCase
{
    /**
     * Verify that address is present.
     * PreConditions: Customer is opened on 'Addresses' tab.
     *
     * @param array $addressData
     *
     * @return int|mixed|string
     */
    public function isAddressPresent(array $addressData)
    {
        $addressCount = $this->getControlCount('pageelement', 'list_customer_address');
        for ($i = $addressCount; $i > 0; $i--) {
            $this->addParameter('index', $i);
            $this->clickControl('pageelement', 'list_customer_address_index', false);
            $id = $this->getControlAttribute('pageelement', 'list_customer_address_index', 'id');
            $arrayId = explode('_', $id);
            $id = end($arrayId);
            $this->addParameter('address_number', $id);
            if ($this->verifyForm($addressData, 'addresses')) {
                return $id;
            }
        }
        return 0;
    }

    /**
     * Defining and adding %address_number% for customer Uimap.
     * PreConditions: Customer is opened on 'Addresses' tab. New address form for filling is added
     */
    public function addAddressNumber()
    {
        $addressCount = $this->getControlCount('pageelement', 'list_customer_address');
        $this->addParameter('index', $addressCount);
        $param = $this->getControlAttribute('pageelement', 'list_customer_address_index', 'id');
        $this->addParameter('address_number', preg_replace('/(\D)+/', '', $param));
    }

    public function deleteAllAddresses($searchData)
    {
        $this->openCustomer($searchData);
        $this->openTab('addresses');
        $addressCount = $this->getControlCount('pageelement', 'list_customer_address');
        if ($addressCount > 0) {
            $this->addParameter('index', $addressCount);
            $param = $this->getControlAttribute('pageelement', 'list_customer_address_index', 'id');
            $this->addParameter('address_number', preg_replace('/[a-zA-z]+_/', '', $param));
            $this->fillRadiobutton('default_billing_address', 'Yes');
            $this->fillRadiobutton('default_shipping_address', 'Yes');
            for ($i = 1; $i <= $addressCount; $i++) {
                $this->addParameter('index', $i);
                $param = $this->getControlAttribute('pageelement', 'list_customer_address_index', 'id');
                $this->addParameter('address_number', preg_replace('/[a-zA-z]+_/', '', $param));
                $this->clickControlAndConfirm('button', 'delete_address', 'confirmation_for_delete_address', false);
            }
            $this->saveForm('save_customer');
            $this->assertMessagePresent('success');
        }
    }

    /**
     * Add address for customer.
     * PreConditions: Customer is opened.
     *
     * @param array $addressData
     */
    public function addAddress(array $addressData)
    {
        //Open 'Addresses' tab
        $this->openTab('addresses');
        $this->clickButton('add_new_address', false);
        $this->addAddressNumber();
        $this->waitForElement($this->_getControlXpath('fieldset', 'edit_address'));
        //Fill in 'Customer's Address' tab
        $this->fillTab($addressData, 'addresses');
    }

    /**
     * Create customer.
     * PreConditions: 'Manage Customers' page is opened.
     *
     * @param array $userData
     * @param array $addressData
     */
    public function createCustomer(array $userData, array $addressData = null)
    {
        //Click 'Add New Customer' button.
        $this->clickButton('add_new_customer');
        // Verify that 'send_from' field is present
        if (array_key_exists('send_from', $userData) && !$this->controlIsPresent('dropdown', 'send_from')) {
            unset($userData['send_from']);
        }
        //Fill in 'Account Information' tab
        $this->fillForm($userData, 'account_information');
        //Add address
        if (isset($addressData)) {
            $this->addAddress($addressData);
        }
        $this->saveForm('save_customer');
    }

    /**
     * Open customer.
     * PreConditions: 'Manage Customers' page is opened.
     *
     * @param array $searchData
     */
    public function openCustomer(array $searchData)
    {
        $this->searchAndOpen($searchData, true, 'customers_grid');
    }

    /**
     * Register Customer on Frontend.
     * PreConditions: 'Login or Create an Account' page is opened.
     *
     * @param array $registerData
     * @param bool $disableCaptcha
     *
     * @return void
     */
    public function registerCustomer(array $registerData, $disableCaptcha = true)
    {
        $currentPage = $this->getCurrentPage();
        $this->clickButton('create_account');
        // Disable CAPTCHA if present
        if ($disableCaptcha && $this->controlIsPresent('pageelement', 'captcha')) {
            $this->loginAdminUser();
            $this->navigate('system_configuration');
            $this->systemConfigurationHelper()->configure('disable_customer_captcha');
            $this->frontend($currentPage);
            $this->clickButton('create_account');
        }
        $this->fillForm($registerData);
        $waitConditions = array($this->_getMessageXpath('general_error'), $this->_getMessageXpath('general_validation'),
                                $this->_getControlXpath('link', 'log_out'));
        $this->clickButton('submit', false);
        $this->waitForElement($waitConditions);
        $this->validatePage();
    }

    /**
     * Log in customer at frontend.
     *
     * @param array $loginData
     */
    public function frontLoginCustomer(array $loginData)
    {
        $this->frontend();
        $this->logoutCustomer();
        $this->clickControl('link', 'log_in');
        $this->fillFieldset($loginData, 'log_in_customer');
        $waitConditions = array($this->_getMessageXpath('general_error'), $this->_getMessageXpath('general_validation'),
                                $this->_getControlXpath('link', 'log_out'));
        $this->clickButton('login', false);
        $this->waitForElement($waitConditions);
        $this->addParameter('id', $this->defineIdFromUrl());
        $this->assertTrue($this->controlIsPresent('link', 'log_out'), 'Customer is not logged in.');
        $this->setCurrentPage($this->_findCurrentPageFromUrl());
    }
}