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
 * Duplicate product tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_DuplicateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Catalog -> Manage Products</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_products');
    }

    /**
     * Test Realizing precondition for creating configurable product.
     *
     * @test
     * @return array $attrData
     * @group preConditions
     */
    public function createConfigurableAttribute()
    {
        //Data
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_dropdown_with_options');
        $associatedAttributes = $this->loadDataSet('AttributeSet', 'associated_attributes',
            array('General' => $attrData['attribute_code']));
        //Steps
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        //Verifying
        $this->assertMessagePresent('success', 'success_attribute_set_saved');

        return $attrData;
    }

    /**
     * Test Realizing precondition for duplicating products.
     *
     * @param array $attrData
     *
     * @return array $productData
     * @test
     * @depends createConfigurableAttribute
     * @group preConditions
     */
    public function createProducts($attrData)
    {
        //Data
        $simple = $this->loadDataSet('Product', 'simple_product_visible');
        $simple['general_user_attr']['dropdown'][$attrData['attribute_code']] =
            $attrData['option_1']['admin_option_name'];
        $virtual = $this->loadDataSet('Product', 'virtual_product_visible');
        $virtual['general_user_attr']['dropdown'][$attrData['attribute_code']] =
            $attrData['option_2']['admin_option_name'];
        $downloadable = $this->loadDataSet('SalesOrder', 'downloadable_product_for_order',
            array('downloadable_links_purchased_separately' => 'No'));
        $downloadable['general_user_attr']['dropdown'][$attrData['attribute_code']] =
            $attrData['option_3']['admin_option_name'];

        $productData = array('simple'       => $simple,
                             'downloadable' => $downloadable,
                             'virtual'      => $virtual);
        //Steps
        foreach ($productData as $key => $value) {
            $this->productHelper()->createProduct($value, $key);
            //Verifying
            $this->assertMessagePresent('success', 'success_saved_product');
        }

        return array('related_search_sku'     => $simple['general_sku'],
                     'up_sells_search_sku'    => $downloadable['general_sku'],
                     'cross_sells_search_sku' => $virtual['general_sku']);
    }

    /**
     * <p>Creating duplicated simple product</p>
     * <p>Steps:</p>
     * <p>1. Open created product;</p>
     * <p>2. Click "Duplicate" button;</p>
     * <p>3. Verify that all fields has the same data except SKU and Status(fields empty)</p>
     * <p>Expected result:</p>
     * <p>Product is duplicated, confirmation message appears;</p>
     *
     * @param array $attrData
     * @param array $assignData
     *
     * @test
     * @depends createConfigurableAttribute
     * @depends createProducts
     */
    public function duplicateSimple($attrData, $assignData)
    {
        //Data
        $simple = $this->loadDataSet('Product', 'duplicate_simple', $assignData);
        $simple['general_user_attr']['dropdown'][$attrData['attribute_code']] =
            $attrData['option_1']['admin_option_name'];
        $search = $this->loadDataSet('Product', 'product_search', array('product_sku' => $simple['general_sku']));
        //Steps
        $this->productHelper()->createProduct($simple);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($search);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertMessagePresent('success', 'success_duplicated_product');
        $this->productHelper()->verifyProductInfo($simple, array('general_sku', 'general_status'));
    }

    /**
     * <p>Creating duplicated virtual product</p>
     * <p>Steps:</p>
     * <p>1. Open created product;</p>
     * <p>2. Click "Duplicate" button;</p>
     * <p>3. Verify that all fields has the same data except SKU and Status(fields empty)</p>
     * <p>Expected result:</p>
     * <p>Product is duplicated, confirmation message appears;</p>
     *
     * @param array $attrData
     * @param array $assignData
     *
     * @test
     * @depends createConfigurableAttribute
     * @depends createProducts
     */
    public function duplicateVirtual($attrData, $assignData)
    {
        //Data
        $virtual = $this->loadDataSet('Product', 'duplicate_virtual', $assignData);
        $virtual['general_user_attr']['dropdown'][$attrData['attribute_code']] =
            $attrData['option_2']['admin_option_name'];
        $search = $this->loadDataSet('Product', 'product_search', array('product_sku' => $virtual['general_sku']));
        //Steps
        $this->productHelper()->createProduct($virtual, 'virtual');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($search);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertMessagePresent('success', 'success_duplicated_product');
        $this->productHelper()->verifyProductInfo($virtual, array('general_sku', 'general_status'));
    }

    /**
     * <p>Creating grouped product with associated products</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add product' button;</p>
     * <p>2. Fill in 'Attribute Set' and 'Product Type' fields;</p>
     * <p>3. Click 'Continue' button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click 'Save' button;</p>
     * <p>6. Open created product;</p>
     * <p>7. Click "Duplicate" button;</p>
     * <p>8. Verify required fields has the same data except SKU (field empty)</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @param array $assignData
     *
     * @test
     * @depends createProducts
     */
    public function duplicateGrouped($assignData)
    {
        //Data
        $grouped = $this->loadDataSet('Product', 'duplicate_grouped', $assignData,
            array('product_1' => $assignData['related_search_sku'],
                  'product_2' => $assignData['up_sells_search_sku'],
                  'product_3' => $assignData['cross_sells_search_sku']));
        $search = $this->loadDataSet('Product', 'product_search', array('product_sku' => $grouped['general_sku']));
        //Steps
        $this->productHelper()->createProduct($grouped, 'grouped');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($search);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertMessagePresent('success', 'success_duplicated_product');
        $this->productHelper()->verifyProductInfo($grouped, array('general_sku', 'general_status'));
    }

    /**
     * <p>Creating duplicated Bundle Product</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add product' button;</p>
     * <p>2. Fill in 'Attribute Set' and 'Product Type' fields;</p>
     * <p>3. Click 'Continue' button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click 'Save' button;</p>
     * <p>6. Open created product;</p>
     * <p>7. Click "Duplicate" button;</p>
     * <p>8. Verify required fields has the same data except SKU (field empty)</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @param $data
     * @param array $assignData
     *
     * @test
     * @dataProvider duplicateBundleDataProvider
     * @depends createProducts
     */
    public function duplicateBundle($data, $assignData)
    {
        //Data
        $bundle = $this->loadDataSet('Product', $data, $assignData,
            array('product_1' => $assignData['related_search_sku'],
                  'product_2' => $assignData['cross_sells_search_sku']));
        $search = $this->loadDataSet('Product', 'product_search', array('product_sku' => $bundle['general_sku']));
        //Steps
        $this->productHelper()->createProduct($bundle, 'bundle');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($search);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertMessagePresent('success', 'success_duplicated_product');
        $this->productHelper()->verifyProductInfo($bundle, array('general_sku', 'general_status'));
    }

    public function duplicateBundleDataProvider()
    {
        return array(
            array('duplicate_fixed_bundle'),
            array('duplicate_dynamic_bundle')
        );
    }

    /**
     * <p>Duplicate Configurable product with associated products</p>
     * <p>Preconditions</p>
     * <p>Attribute Set created</p>
     * <p>Virtual product created</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add product' button;</p>
     * <p>2. Fill in 'Attribute Set' and 'Product Type' fields;</p>
     * <p>3. Click 'Continue' button;</p>
     * <p>4. Fill in all required fields;</p>
     * <p>5. Goto "Associated products" tab;</p>
     * <p>6. Select created Virtual product;</p>
     * <p>5. Click 'Save' button;</p>
     * <p>6. Open created product;</p>
     * <p>7. Click "Duplicate" button;</p>
     * <p>8. Verify required fields has the same data except SKU (field empty)</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @param array $attrData
     * @param array $assignData
     *
     * @test
     * @depends createConfigurableAttribute
     * @depends createProducts
     */
    public function duplicateConfigurable($attrData, $assignData)
    {
        //Data
        $assign = array_merge($assignData, array('configurable_attribute_title' => $attrData['admin_title']));
        $configurable = $this->loadDataSet('Product', 'duplicate_configurable', $assign);
        $search = $this->loadDataSet('Product', 'product_search', array('product_sku' => $configurable['general_sku']));
        //Steps
        $this->productHelper()->createProduct($configurable, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($search);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertMessagePresent('success', 'success_duplicated_product');
        //Steps
        $this->productHelper()->fillConfigurableSettings($configurable);
        //Verifying
        $this->productHelper()
            ->verifyProductInfo($configurable, array('general_sku', 'general_status', 'configurable_attribute_title'));
    }
}