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
 * Tests for shipping methods. Frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutMultipleAddresses_Existing_ShippingMethodsTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        //Data
        $config = $this->loadDataSet('ShippingSettings', 'store_information');
        //Steps
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
    }

    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTestClass()
    {
        //Data
        $config = $this->loadDataSet('ShippingMethod', 'shipping_disable');
        $settings = $this->loadDataSet('ShippingSettings', 'shipping_settings_default');
        //Steps
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
        $this->systemConfigurationHelper()->configure($settings);
    }

    protected function tearDownAfterTest()
    {
        $this->frontend();
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->logoutCustomer();
    }

    /**
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        //Steps and Verification
        $simple1 = $this->productHelper()->createSimpleProduct();
        $simple2 = $this->productHelper()->createSimpleProduct();
        $virtual = $this->productHelper()->createVirtualProduct();
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');
        return array('products1' => array('product_1' => $simple1['simple']['product_name'],
                                          'product_2' => $simple2['simple']['product_name']),
                     'products2' => array('product_1' => $simple1['simple']['product_name'],
                                          'product_2' => $virtual['virtual']['product_name']),
                     'email'     => $userData['email']);
    }

    /**
     * <p>Steps:</p>
     * <p>1. Configure settings in System->Configuration</p>
     * <p>2. Login as a customer. Clear shopping cart</p>
     * <p>3. Logout as the customer</p>
     * <p>4. Add 2 simple products to the shopping cart</p>
     * <p>5. Checkout with multiple addresses</p>
     * <p>6. Add default shipping address when needed. Add new shipping address</p>
     * <p>7. Set each product to be delivered to a separate address</p>
     * <p>8. Continue with default billing address,
     *       Check/Money payment method and appropriate shipping method</p>
     * <p>9. Place the order</p>
     * <p>Expected result:</p>
     * <p>Two new orders are successfully created.</p>
     *
     * @param string $shipment
     * @param array $testData
     *
     * @test
     * @dataProvider shipmentDataProvider
     * @depends preconditionsForTests
     */
    public function withSimpleProducts($shipment, $testData)
    {
        //Data
        $shippingMethod = $this->loadDataSet('Shipping', 'shipping_' . $shipment);
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_login',
            array('shipping' => $shippingMethod,
                  'email'    => $testData['email']), $testData['products1']);
        $shippingSettings = $this->loadDataSet('ShippingMethod', $shipment . '_enable');
        //Setup
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($shippingSettings);
        //Steps and Verify
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    /**
     * <p>Steps:</p>
     * <p>1. Configure settings in System->Configuration</p>
     * <p>2. Login as a customer. Clear shopping cart</p>
     * <p>3. Logout as the customer</p>
     * <p>4. Add 1 simple product and 1 virtual to the shopping cart</p>
     * <p>5. Checkout with multiple addresses</p>
     * <p>6. Add default shipping address when needed. Add new shipping address</p>
     * <p>7. Set each product to be delivered to a separate address</p>
     * <p>8. Continue with default billing address, Check/Money payment method and appropriate shipping method</p>
     * <p>9. Place the order</p>
     * <p>Expected result:</p>
     *
     * @param string $shipment
     * @param array $testData
     *
     * @test
     * @dataProvider shipmentDataProvider
     * @depends preconditionsForTests
     */
    public function withSimpleAndVirtualProducts($shipment, $testData)
    {
        //Data
        $shippingMethod = $this->loadDataSet('Shipping', 'shipping_' . $shipment);
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_login_virtual',
            array('shipping' => $shippingMethod,
                  'email'    => $testData['email']), $testData['products2']);
        $shippingSettings = $this->loadDataSet('ShippingMethod', $shipment . '_enable');
        //Setup
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($shippingSettings);
        //Steps and Verify
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    public function shipmentDataProvider()
    {
        return array(
            array('flatrate'),
            array('free'),
            array('ups'),
            array('upsxml'),
            array('usps'),
            array('fedex')
        );
    }

    /**
     * @param array $testData
     * @param string $dataSet
     * @param string $productTypes
     *
     * @test
     * @dataProvider productTypesProvider
     * @depends preconditionsForTests
     */
    public function withDhlMethod($productTypes, $dataSet, $testData)
    {
        //Data
        $shippingMethod = $this->loadDataSet('Shipping', 'shipping_dhl');
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', $dataSet,
            array('shipping'  => $shippingMethod,
                  'email'     => $testData['email']), $testData[$productTypes]);
        $shippingSettings = $this->loadDataSet('ShippingMethod', 'dhl_enable');
        $shippingOrigin = $this->loadDataSet('ShippingSettings', 'shipping_settings_usa');
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($shippingSettings);
        $this->systemConfigurationHelper()->configure($shippingOrigin);
        //Steps and Verify
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    public function productTypesProvider()
    {
        return array(
            array('products1', 'multiple_with_login_france'),
            array('products2', 'multiple_with_login_france_virtual')
        );
    }
}