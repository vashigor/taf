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
 * Catalog Price Rules applying in frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_PriceRules_Catalog_ApplyTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('Tax/default_tax_config');
        $this->systemConfigurationHelper()->configure('ShippingSettings/shipping_settings_default');
        $this->systemConfigurationHelper()->configure('Currency/enable_usd');
        $this->navigate('manage_catalog_price_rules');
        $this->priceRulesHelper()->deleteAllRules();
        $this->clickButton('apply_rules', false);
        $this->waitForNewPage();
        $this->assertMessagePresent('success', 'success_applied_rule');
    }

    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTest()
    {
        $this->logoutCustomer();
        $this->loginAdminUser();
        $this->navigate('manage_catalog_price_rules');
        $this->priceRulesHelper()->deleteAllRules();
        $this->clickButton('apply_rules', false);
        $this->waitForNewPage();
        $this->assertMessagePresent('success', 'success_applied_rule');
    }

    /**
     * <p>Preconditions</p>
     * <p>Create Customer for tests</p>
     * <p>Creates Category to use during tests</p>
     * @return array
     * @test
     * @skipTearDown
     */
    public function preconditionsForTests()
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $data = $this->productHelper()->createSimpleProduct(true);
        return array('customer'     => array('email' => $userData['email'], 'password' => $userData['password']),
                     'categoryPath' => $data['category']['path'], 'categoryName' => $data['category']['name'],
                     'simpleName'   => $data['simple']['product_name']);
    }

    /**
     * <p>Create catalog price rule - To Fixed Amount</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Rule"</p>
     * <p>2. Fill in required fields</p>
     * <p>3. Select in "General Information" -> "Customer Groups" = "NOT LOGGED IN"</p>
     * <p>3. Select in "Apply" field option - "To Fixed Amount"</p>
     * <p>4. Specify "Discount Amount" = 10%</p>
     * <p>5. Click "Save and Apply" button</p>
     * <p>Expected result: New rule created, success message appears</p>
     * <p>Verification</p>
     * <p>6. Open product in Frontend as a GUEST</p>
     * <p>7. Verify product special price = $10.00</p>
     * <p>8. Login to Frontend</p>
     * <p>9. Verify product REGULAR PRICE = $120.00</p>
     *
     * @param string $ruleType
     * @param array $testData
     *
     * @test
     * @dataProvider applyRuleToSimpleFrontDataProvider
     * @depends preconditionsForTests
     */
    public function applyRuleToSimpleFront($ruleType, $testData)
    {
        //Data
        $action = $this->loadDataSet('CatalogPriceRule', $ruleType);
        $condition =
            $this->loadDataSet('CatalogPriceRule', 'condition', array('category' => $testData['categoryPath']));
        $priceRule = $this->loadDataSet('CatalogPriceRule', 'test_catalog_rule',
            array('conditions' => $condition, 'status' => 'Active', 'actions' => $action));
        $override = array('product_name' => $testData['simpleName'], 'category'     => $testData['categoryName']);
        $productPriceLogged = $this->loadDataSet('PriceReview', $ruleType . '_simple_product_logged');
        $productPriceNotLogged = $this->loadDataSet('PriceReview', $ruleType . '_simple_product_not_logged');
        $inCategoryLogged = $this->loadDataSet('PriceReview', $ruleType . '_simple_logged_category', $override);
        $inCategoryNotLogged = $this->loadDataSet('PriceReview', $ruleType . '_simple_not_logged_category', $override);
        //Steps
        $this->navigate('manage_catalog_price_rules');
        $this->priceRulesHelper()->createRule($priceRule);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rule');
        //Steps
        $this->clickButton('apply_rules', false);
        $this->waitForNewPage();
        $this->assertMessagePresent('success', 'success_applied_rule');
        $this->flushCache();
        $this->reindexAllData();
        //Verification on frontend
        $this->frontend();
        $this->categoryHelper()->frontOpenCategoryAndValidateProduct($inCategoryNotLogged);
        $this->productHelper()->frontOpenProduct($testData['simpleName']);
        $this->categoryHelper()->frontVerifyProductPrices($productPriceNotLogged, $testData['simpleName']);
        $this->customerHelper()->frontLoginCustomer($testData['customer']);
        $this->categoryHelper()->frontOpenCategoryAndValidateProduct($inCategoryLogged);
        $this->productHelper()->frontOpenProduct($testData['simpleName']);
        $this->categoryHelper()->frontVerifyProductPrices($productPriceLogged);
    }

    public function applyRuleToSimpleFrontDataProvider()
    {
        return array(
            array('by_percentage_of_the_original_price'),
            array('by_fixed_amount'),
            array('to_percentage_of_the_original_price'),
            array('to_fixed_amount')
        );
    }
}