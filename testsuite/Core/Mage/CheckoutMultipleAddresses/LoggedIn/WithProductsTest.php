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
 * Checkout Multiple Addresses tests with different product types
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutMultipleAddresses_LoggedIn_WithProductsTest extends Mage_Selenium_TestCase
{
    private static $productTypes = array('simple', 'virtual', 'downloadable',
                                         'bundle', 'configurable', 'grouped');

    protected function assertPreconditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTest()
    {
        $this->frontend();
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->logoutCustomer();
    }

    /**
     * <p>Create all types of products</p>
     *
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        $products = array();
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');
        foreach (self::$productTypes as $type) {
            $method = 'create' . ucfirst($type) . 'Product';
            $products[$type] = $this->productHelper()->$method();
        }
        return array($products,
                     'user' => array('email'    => $userData['email'],
                                     'password' => $userData['password']),);
    }

    /**
     * <p>Checkout with multiple addresses simple and virtual/downloadable products</p>
     * <p>Preconditions:</p>
     * <p>1.Products are created.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with log in</p>
     * <p>5. Fill in Select Addresses page.</p>
     * <p>6. Click "Continue to Shipping Information" button;</p>
     * <p>7. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>8. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>9. Verify information into "Order Review" tab;</p>
     * <p>10. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productType
     * @param array $testData
     *
     * @test
     * @dataProvider virtualProductsDataProvider
     * @depends preconditionsForTests
     */
    public function withVirtualTypeOfProducts($productType, $testData)
    {
        //Data
        list($products) = $testData;
        $simple = $products['simple']['simple']['product_name'];
        $virtual = $products['configurable'][$productType]['product_name'];
        $checkout = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_signed_in_virtual', null,
            array('product_1' => $simple,
                  'product_2' => $virtual));
        //Steps and Verify
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkout);
        $this->assertMessagePresent('success', 'success_checkout');
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    public function virtualProductsDataProvider()
    {
        return array(
            array('downloadable'),
            array('virtual')
        );
    }

    /**
     * <p>Checkout with multiple addresses grouped products</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with log in</p>
     * <p>5. Fill in Select Addresses page.</p>
     * <p>6. Click "Continue to Shipping Information" button;</p>
     * <p>7. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>8. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>9. Verify information into "Order Review" tab;</p>
     * <p>10. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productType
     * @param string $dataSet
     * @param array $testData
     *
     * @test
     * @dataProvider productsDataProvider
     * @depends preconditionsForTests
     */
    public function withGroupedProduct($productType, $dataSet, $testData)
    {
        //Data
        list($products) = $testData;
        $simple = $products['simple']['simple']['product_name'];
        $grouped = $products['grouped']['grouped']['product_name'];
        $optionParams = $products['grouped'][$productType]['product_name'];
        $productOptions = $this->loadDataSet('Product', 'grouped_options_to_add_to_shopping_cart', null,
            array('subProduct_1' => $optionParams));
        $checkout = $this->loadDataSet('MultipleAddressesCheckout', $dataSet, null,
            array('product_1'        => $simple,
                  'product_2'        => $grouped,
                  'option_product_2' => $productOptions));
        $checkout['shipping_data'] = $this->loadDataSet('MultipleAddressesCheckout', $dataSet . '/shipping_data', null,
            array('product_1'  => $simple,
                  'product_2'  => $optionParams));
        //Steps and Verify
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkout);
        $this->assertMessagePresent('success', 'success_checkout');
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    /**
     * <p>Checkout with multiple addresses bundle products</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with log in</p>
     * <p>5. Fill in Select Addresses page.</p>
     * <p>6. Click "Continue to Shipping Information" button;</p>
     * <p>7. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>8. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>9. Verify information into "Order Review" tab;</p>
     * <p>10. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productType
     * @param string $dateSet
     * @param array $testData
     *
     * @test
     * @dataProvider withBundleProductDataProvider
     * @depends preconditionsForTests
     */
    public function withBundleProduct($productType, $dateSet, $testData)
    {
        //Data
        list($products) = $testData;
        $simple = $products['simple']['simple']['product_name'];
        $bundle = $products['bundle']['bundle']['product_name'];
        $optionParams = $products['bundle']['bundleOption'];
        foreach ($optionParams as $key => $value) {
            $optionParams[$key] = $products['bundle'][$productType]['product_name'];
        }
        $productOptions = $this->loadDataSet('Product', 'bundle_options_to_add_to_shopping_cart', null, $optionParams);
        $checkout = $this->loadDataSet('MultipleAddressesCheckout', $dateSet, null,
            array('product_1'        => $simple,
                  'product_2'        => $bundle,
                  'option_product_2' => $productOptions));
        //Steps and Verify
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkout);
        $this->assertMessagePresent('success', 'success_checkout');
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    /**
     * <p>Checkout with multiple addresses configurable product with associated products</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with log in</p>
     * <p>5. Fill in Select Addresses page.</p>
     * <p>6. Click "Continue to Shipping Information" button;</p>
     * <p>7. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>8. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>9. Verify information into "Order Review" tab;</p>
     * <p>10. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productType
     * @param string $dateSet
     * @param array $testData
     *
     * @test
     * @dataProvider productsDataProvider
     * @depends preconditionsForTests
     */
    public function withConfigurable($productType, $dateSet, $testData)
    {
        //Data
        list($products) = $testData;
        $simple = $products['simple']['simple']['product_name'];
        $configurable = $products['configurable']['configurable']['product_name'];
        $optionParams = $products['configurable']['configurableOption'];
        $optionParams['custom_option_dropdown'] = $products['configurable'][$productType . 'Option']['option_front'];
        $productOptions = $this->loadDataSet('Product', 'configurable_options_to_add_to_shopping_cart', $optionParams);
        $checkout = $this->loadDataSet('MultipleAddressesCheckout', $dateSet, null,
            array('product_1'        => $simple,
                  'product_2'        => $configurable,
                  'option_product_2' => $productOptions));
        //Steps and Verify
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkout);
        $this->assertMessagePresent('success', 'success_checkout');
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    /**
     * <p>Checkout with multiple addresses Downloadable product with associated links</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with log in</p>
     * <p>5. Fill in Select Addresses page.</p>
     * <p>6. Click "Continue to Shipping Information" button;</p>
     * <p>7. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>8. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>9. Verify information into "Order Review" tab;</p>
     * <p>10. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function withDownloadable($testData)
    {
        //Data
        list($products) = $testData;
        $simple = $products['simple']['simple']['product_name'];
        $downloadable = $products['downloadable']['downloadable']['product_name'];
        $optionParams = $products['downloadable']['downloadableOption'];
        $productOptions = $this->loadDataSet('Product', 'downloadable_options_to_add_to_shopping_cart', $optionParams);
        $checkout = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_signed_in_virtual', null,
            array('product_1'        => $simple,
                  'product_2'        => $downloadable,
                  'option_product_2' => $productOptions));
        //Steps and Verify
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkout);
        $this->assertMessagePresent('success', 'success_checkout');
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    public function productsDataProvider()
    {
        return array(
            array('simple', 'multiple_with_signed_in'),
            array('virtual', 'multiple_with_signed_in_virtual'),
            array('downloadable', 'multiple_with_signed_in_virtual')
        );
    }

    public function withBundleProductDataProvider()
    {
        return array(
            array('simple', 'multiple_with_signed_in'),
            //array('virtual', 'multiple_with_signed_in_virtual')
        );
    }

    /**
     * <p>Checkout with multiple addresses products with custom options</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with log in</p>
     * <p>5. Fill in Select Addresses page.</p>
     * <p>6. Click "Continue to Shipping Information" button;</p>
     * <p>7. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>8. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>9. Verify information into "Order Review" tab;</p>
     * <p>10. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productType
     * @param string $dataSet
     * @param array $testData
     *
     * @test
     * @dataProvider withCustomOptionsDataProvider
     * @depends preconditionsForTests
     */
    public function withCustomOptions($productType, $dataSet, $testData)
    {
        //Data
        list($products) = $testData;
        $simple = $products['simple']['simple']['product_name'];
        $productData = $products[$productType];
        if ($productType == 'simple') {
            $productData = $products['bundle'];
        }
        $secondProduct = $productData[$productType]['product_name'];
        $optionParams =
            (isset($productData[$productType . 'Option'])) ? $productData[$productType . 'Option'] : array();
        $productOptions = array();
        if (!empty($optionParams)) {
            $name = '_options_to_add_to_shopping_cart';
            if ($productType == 'configurable' || $productType == 'downloadable') {
                $productOptions = $this->loadDataSet('Product', $productType . $name, $optionParams);
            } else {
                $productOptions = $this->loadDataSet('Product', $productType . $name, null, $optionParams);
            }
        }
        $customOptions = $this->loadDataSet('Product', 'custom_options_to_add_to_shopping_cart');
        $productOptions = array_merge($productOptions, $customOptions);
        $checkout = $this->loadDataSet('MultipleAddressesCheckout', $dataSet, null,
            array('product_1'        => $simple,
                  'product_2'        => $secondProduct,
                  'option_product_2' => $productOptions));
        $search = $this->loadDataSet('Product', 'product_search', array('product_name'=> $secondProduct));
        $customOptionsData['custom_options_data'] = $this->loadDataSet('Product', 'custom_options_data');
        //Steps and Verify
        $this->navigate('manage_products');
        $this->productHelper()->openProduct($search);
        $this->productHelper()->fillProductTab($customOptionsData, 'custom_options');
        $this->saveForm('save');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkout);
        $this->assertMessagePresent('success', 'success_checkout');
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    public function withCustomOptionsDataProvider()
    {
        return array(
            array('virtual', 'multiple_with_signed_in_virtual'),
            array('downloadable', 'multiple_with_signed_in_virtual'),
            array('bundle', 'multiple_with_signed_in'),
            array('configurable', 'multiple_with_signed_in'),
            array('simple', 'multiple_with_signed_in')
        );
    }
}
