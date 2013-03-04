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
 * Product Tax class Core_Mage_deletion tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tax_ProductTaxClass_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Save rule name for clean up</p>
     */
    protected $_ruleToBeDeleted = array();

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Sales-Tax-Product Tax Classes</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTest()
    {
        //Remove Tax rule after test
        if (!empty($this->_ruleToBeDeleted)) {
            $this->loginAdminUser();
            $this->navigate('manage_tax_rule');
            $this->taxHelper()->deleteTaxItem($this->_ruleToBeDeleted, 'rule');
            $this->_ruleToBeDeleted = array();
        }
    }

    /**
     * <p>Delete a Product Tax Class</p>
     * <p>Steps:</p>
     * <p>1. Create a new Product Tax Class</p>
     * <p>2. Open the Product Tax Class</p>
     * <p>3. Delete the Product Tax Class</p>
     * <p>Expected result:</p>
     * <p>Received the message that the Product Tax class Core_Mage_has been deleted.</p>
     *
     * @test
     */
    public function notUsedInRule()
    {
        //Data
        $productTaxClassData = $this->loadDataSet('Tax', 'new_product_tax_class');
        //Steps
        $this->navigate('manage_product_tax_class');
        $this->taxHelper()->createTaxItem($productTaxClassData, 'product_class');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_class');
        //Steps
        $this->taxHelper()->deleteTaxItem($productTaxClassData, 'product_class');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_tax_class');
    }

    /**
     * <p>Delete a Product Tax class Core_Mage_that used in Tax Rule</p>
     * <p>Steps:</p>
     * <p>1. Create a new Product Tax Class</p>
     * <p>2. Create a new Tax Rule that use Product Tax class Core_Mage_from previous step</p>
     * <p>2. Open the Product Tax Class</p>
     * <p>3. Delete the Product Tax Class</p>
     * <p>Expected result:</p>
     * <p>Received the message that the Product Tax class Core_Mage_could not be deleted.</p>
     *
     * @test
     */
    public function usedInRule()
    {
        //Data
        $taxRateData = $this->loadDataSet('Tax', 'tax_rate_create_test');
        $taxClass = $this->loadDataSet('Tax', 'new_product_tax_class');
        $taxRule = $this->loadDataSet('Tax', 'new_tax_rule_required',
            array('product_tax_class' => $taxClass['product_class_name'],
                  'tax_rate'          => $taxRateData['tax_identifier']));
        $searchTaxRuleData = $this->loadDataSet('Tax', 'search_tax_rule', array('filter_name' => $taxRule['name']));
        //Steps
        $this->navigate('manage_tax_zones_and_rates');
        $this->taxHelper()->createTaxItem($taxRateData, 'rate');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rate');
        $this->navigate('manage_product_tax_class');
        $this->taxHelper()->createTaxItem($taxClass, 'product_class');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_class');
        //Steps
        $this->navigate('manage_tax_rule');
        $this->taxHelper()->createTaxItem($taxRule, 'rule');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rule');
        $this->_ruleToBeDeleted = $searchTaxRuleData;
        //Steps
        $this->navigate('manage_product_tax_class');
        $this->taxHelper()->deleteTaxItem($taxClass, 'product_class');
        //Verifying
        $this->assertMessagePresent('error', 'error_delete_tax_class');
    }

    /**
     * <p>Delete a Product Tax class Core_Mage_that used in Product</p>
     * <p>Steps:</p>
     * <p>1. Create a new Product Tax Class</p>
     * <p>2. Create a new Product that use Product Tax class Core_Mage_from previous step</p>
     * <p>2. Open the Product Tax Class</p>
     * <p>3. Delete the Product Tax Class</p>
     * <p>Expected result:</p>
     * <p>Received the message that the Product Tax class Core_Mage_could not be deleted.</p>
     * <p>Error message: You cannot delete this tax class Core_Mage_as it is used for 1 products.</p>
     *
     * @test
     */
    public function usedInProduct()
    {
        //Data
        $taxClass = $this->loadDataSet('Tax', 'new_product_tax_class');
        $product = $this->loadDataSet('Product', 'simple_product_required',
            array('prices_tax_class' => $taxClass['product_class_name']));
        //Steps
        $this->navigate('manage_product_tax_class');
        $this->taxHelper()->createTaxItem($taxClass, 'product_class');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_class');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($product);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->navigate('manage_product_tax_class');
        $this->taxHelper()->deleteTaxItem($taxClass, 'product_class');
        //Verifying
        $this->assertMessagePresent('error', 'error_delete_tax_class_product');
    }
}