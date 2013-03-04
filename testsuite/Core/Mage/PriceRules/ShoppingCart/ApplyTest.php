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
 * Applying Shopping Cart Price Rules tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_PriceRules_ShoppingCart_ApplyTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('Tax/default_tax_config');
        $this->systemConfigurationHelper()->configure('ShippingSettings/shipping_settings_default');
        $this->systemConfigurationHelper()->configure('Currency/enable_usd');
        $this->systemConfigurationHelper()->configure('ShippingMethod/flatrate_enable');
    }

    protected function assertPreConditions()
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
     * @return array
     * @test
     * @skipTearDown
     */
    public function preconditionsForTests()
    {
        $user = $this->loadDataSet('PriceReview', 'customer_account_for_prices_validation');
        $address = $this->loadDataSet('PriceReview', 'customer_account_address_for_prices_validation');
        $category = $this->loadDataSet('Category', 'sub_category_required');
        $categoryPath = $category['parent_category'] . '/' . $category['name'];
        $products = array();
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($user, $address);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->createCategory($category);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();
        //Steps
        $this->navigate('manage_products');
        for ($i = 1; $i <= 3; $i++) {
            $simple = $this->loadDataSet('PriceReview', 'simple_product_for_prices_validation_front_' . $i,
                array('categories' => $categoryPath));
            $this->productHelper()->createProduct($simple);
            $this->assertMessagePresent('success', 'success_saved_product');
            $products['sku'][$i] = $simple['general_sku'];
            $products['name'][$i] = $simple['general_name'];
        }
        return array(array('email'    => $user['email'],
                           'password' => $user['password']), $products, $categoryPath);
    }

    /**
     * <p>Create Shopping cart price rule</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Shopping Cart Price Rules;</p>
     * <p>2. Fill form for Shopping Cart Price Rules (Type of discount is provided via data provider);
     * Select specific category in conditions; Add coupon that should be applied;</p>
     * <p>3. Save newly created Shopping Cart Price Rules;</p>
     * <p>4. Navigate to frontend;</p>
     * <p>5. Add product(s) for which rule should be applied to shopping cart;</p>
     * <p>6. Apply coupon for the shopping cart;</p>
     * <p>6. Verify prices for the product(s) in the totals of shopping cart;</p>
     * <p>Expected results:</p>
     * <p>Rule is created; Totals changed after applying coupon; Rule is discounting percent of each product;</p>
     *
     * @param string $ruleType
     * @param array $testData
     *
     * @test
     * @dataProvider createSCPRDataProvider
     * @depends preconditionsForTests
     */
    public function createSCPR($ruleType, $testData)
    {
        //Data
        list($customer, $products, $category) = $testData;
        $cartProductsData = $this->loadDataSet('ShoppingCartPriceRule', 'prices_for_' . $ruleType);
        $checkoutData = $this->loadDataSet('ShoppingCartPriceRule', 'totals_for_' . $ruleType);
        $ruleData = $this->loadDataSet('ShoppingCartPriceRule', 'scpr_' . $ruleType, array('category' => $category));
        //Steps
        $this->navigate('manage_shopping_cart_price_rules');
        $this->priceRulesHelper()->createRule($ruleData);
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->flushCache();
        $this->reindexAllData();
        $this->customerHelper()->frontLoginCustomer($customer);
        foreach ($products['name'] as $key => $productName) {
            $cartProductsData['product_' . $key]['product_name'] = $productName;
            $this->productHelper()->frontOpenProduct($productName);
            $this->productHelper()->frontAddProductToCart();
        }
        $this->shoppingCartHelper()
            ->frontEstimateShipping('PriceReview/estimate_shipping', 'Shipping/shipping_flatrate');
        $this->addParameter('couponCode', $ruleData['info']['coupon_code']);
        $this->fillFieldset(array('coupon_code' => $ruleData['info']['coupon_code']), 'discount_codes');
        $this->clickButton('apply_coupon');
        $this->assertMessagePresent('success', 'success_applied_coupon');
        $this->shoppingCartHelper()->verifyPricesDataOnPage($cartProductsData, $checkoutData);
    }

    public function createSCPRDataProvider()
    {
        return array(
            array('percent_of_product_price_discount'),
            array('fixed_amount_discount'),
            array('fixed_amount_discount_for_whole_cart')
        );
    }
}
