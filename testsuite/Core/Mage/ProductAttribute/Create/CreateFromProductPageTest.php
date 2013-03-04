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
 * Create new product attribute. Type: Date
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ProductAttribute_Create_CreateFromProductPageTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Manage Attributes.</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_products');
    }

    protected function tearDownAfterTest()
    {
        $this->closeLastWindow();
    }

    /**
     * <p>Checking of attributes creation functionality during product creation process</p>
     * <p>Steps:</p>
     * <p>1.Go to Catalog->Manage Products</p>
     * <p>2.Click on "Add Product" button</p>
     * <p>3.Specify settings for product creation</p>
     * <p>3.1.Select "Attribute Set"</p>
     * <p>3.2.Select "Product Type"</p>
     * <p>4.Click on "Continue" button</p>
     * <p>5.Click on "Create New Attribute" button in the top of "General" fieldset under "General" tab</p>
     * <p>6.Choose attribute type in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>7.Fill all required fields.</p>
     * <p>8.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute successfully created.
     * Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @param $attributeType
     *
     * @test
     * @dataProvider onProductPageWithRequiredFieldsOnlyDataProvider
     */
    public function onProductPageWithRequiredFieldsOnly($attributeType)
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $attrData = $this->loadDataSet('ProductAttribute', $attributeType, null);
        //Steps
        $this->clickButton('add_new_product');
        $this->productHelper()->fillProductSettings($productData);
        $this->productAttributeHelper()->createAttributeOnGeneralTab($attrData);
        //Verifying
        $this->selectWindow(null);
        $code = ($attributeType != 'product_attribute_fpt')
            ? $attrData['attribute_code']
            : $attrData['attribute_code'] . '_table';
        $this->addParameter('elementId', $code);
        $this->assertTrue($this->controlIsPresent('pageelement', 'element_by_id'));
    }

    public function onProductPageWithRequiredFieldsOnlyDataProvider()
    {
        return array(
            array('product_attribute_textfield'),
            array('product_attribute_textarea'),
            array('product_attribute_date'),
            array('product_attribute_yesno'),
            array('product_attribute_multiselect_with_options'),
            array('product_attribute_dropdown_with_options'),
            array('product_attribute_price'),
            array('product_attribute_fpt')
        );
    }
}
