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
 * Create Widget Test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CmsWidgets_CreateTest extends Mage_Selenium_TestCase
{
    protected function assertPreconditions()
    {
        $this->loginAdminUser();
    }

    /**
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        $productData = $this->productHelper()->createConfigurableProduct(true);
        $categoryPath = $productData['category']['path'];
        $bundle = $this->loadDataSet('SalesOrder', 'fixed_bundle_for_order', array('categories' => $categoryPath),
            array('add_product_1' => $productData['simple']['product_sku'],
                  'add_product_2' => $productData['virtual']['product_sku']));
        $grouped = $this->loadDataSet('SalesOrder', 'grouped_product_for_order', array('categories' => $categoryPath),
            array('associated_1' => $productData['simple']['product_sku'],
                  'associated_2' => $productData['virtual']['product_sku'],
                  'associated_3' => $productData['downloadable']['product_sku']));
        $this->productHelper()->createProduct($bundle, 'bundle');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($grouped, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');

        return array('category' => array('category_path' => $productData['category']['path']),
                     'products' => array('product_1' => $productData['simple']['product_sku'],
                                         'product_2' => $grouped['general_sku'],
                                         'product_3' => $productData['configurable']['product_sku'],
                                         'product_4' => $productData['virtual']['product_sku'],
                                         'product_5' => $bundle['general_sku'],
                                         'product_6' => $productData['downloadable']['product_sku']));
    }

    /**
     * <p>Creates All Types of widgets</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with all fields filled</p>
     * <p>Expected result</p>
     * <p>Widgets are created successfully</p>
     *
     * @param string $dataWidgetType
     * @param array $testData
     *
     * @test
     * @dataProvider widgetTypesDataProvider
     * @depends preconditionsForTests
     */
    public function createAllTypesOfWidgetsAllFields($dataWidgetType, $testData)
    {
        //Data
        $widgetData =
            $this->loadDataSet('CmsWidget', $dataWidgetType . '_widget', $testData['category'], $testData['products']);
        //Steps
        $this->navigate('manage_cms_widgets');
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        //Verifying
        $this->assertMessagePresent('success', 'successfully_saved_widget');
    }

    public function widgetTypesDataProvider()
    {
        return array(
            array('cms_page_link'),
            array('cms_static_block'),
            array('catalog_category_link'),
            array('catalog_new_products_list'),
            array('catalog_product_link'),
            array('orders_and_returns'),
            array('recently_compared_products'),
            array('recently_viewed_products')
        );
    }

    /**
     * <p>Creates All Types of widgets with required fields only</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with required fields filled</p>
     * <p>Expected result</p>
     * <p>Widgets are created successfully</p>
     *
     * @param string $dataWidgetType
     * @param array $testData
     *
     * @test
     * @dataProvider widgetTypesDataProvider
     * @depends preconditionsForTests
     */
    public function createAllTypesOfWidgetsReqFields($dataWidgetType, $testData)
    {
        //Data
        $override = array();
        if ($dataWidgetType == 'catalog_product_link') {
            $override = array('filter_sku'    => $testData['products']['product_3'],
                              'category_path' => $testData['category']['category_path']);
        } elseif ($dataWidgetType == 'catalog_category_link') {
            $override = array('category_path' => $testData['category']['category_path']);
        }
        $widgetData = $this->loadDataSet('CmsWidget', $dataWidgetType . '_widget_req', $override);
        //Steps
        $this->navigate('manage_cms_widgets');
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        //Verifying
        $this->assertMessagePresent('success', 'successfully_saved_widget');
    }

    /**
     * <p>Creates All Types of widgets with required fields empty</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with required fields empty</p>
     * <p>Expected result</p>
     * <p>Widgets are not created. Message about required field empty appears.</p>
     *
     * @param string $dataWidgetType
     * @param string $emptyField
     * @param string $fieldType
     * @param array $testData
     *
     * @test
     * @dataProvider withEmptyFieldsDataProvider
     * @depends preconditionsForTests
     */
    public function withEmptyFields($dataWidgetType, $emptyField, $fieldType, $testData)
    {
        //Data
        $override = array();
        if ($dataWidgetType == 'catalog_product_link') {
            $override = array('filter_sku'    => $testData['products']['product_3'],
                              'category_path' => $testData['category']['category_path']);
        } elseif ($dataWidgetType == 'catalog_category_link') {
            $override = array('category_path' => $testData['category']['category_path']);
        }
        if ($fieldType == 'field') {
            $override[$emptyField] = ' ';
        } elseif ($fieldType == 'dropdown') {
            if ($emptyField == 'select_display_on') {
                if ($dataWidgetType == 'cms_page_link' || $dataWidgetType == 'catalog_category_link') {
                    $override['select_template'] = '%noValue%';
                }
                $override['select_block_reference'] = '%noValue%';
            }
            $override[$emptyField] = '-- Please Select --';
        } else {
            $override['widget_options'] = '%noValue%';
            $this->addParameter('elementName', 'Not Selected');
        }
        $widgetData = $this->loadDataSet('CmsWidget', $dataWidgetType . '_widget_req', $override);
        //Steps
        $this->navigate('manage_cms_widgets');
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        //Verifying
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withEmptyFieldsDataProvider()
    {
        return array(
            array('cms_page_link', 'widget_instance_title', 'field'),
            array('cms_page_link', 'page_id', 'pageelement'),
            array('cms_page_link', 'select_display_on', 'dropdown'),
            array('cms_page_link', 'select_block_reference', 'dropdown'),
            array('cms_static_block', 'widget_instance_title', 'field'),
            array('cms_static_block', 'block_id', 'pageelement'),
            array('cms_static_block', 'select_display_on', 'dropdown'),
            array('cms_static_block', 'select_block_reference', 'dropdown'),
            array('catalog_category_link', 'widget_instance_title', 'field'),
            array('catalog_category_link', 'category_id', 'pageelement'),
            array('catalog_category_link', 'select_display_on', 'dropdown'),
            array('catalog_category_link', 'select_block_reference', 'dropdown'),
            array('catalog_new_products_list', 'widget_instance_title', 'field'),
            array('catalog_new_products_list', 'number_of_products_to_display', 'field'),
            array('catalog_new_products_list', 'select_display_on', 'dropdown'),
            array('catalog_new_products_list', 'select_block_reference', 'dropdown'),
            array('catalog_product_link', 'widget_instance_title', 'field'),
            array('catalog_product_link', 'category_id', 'pageelement'),
            array('catalog_product_link', 'select_display_on', 'dropdown'),
            array('catalog_product_link', 'select_block_reference', 'dropdown'),
            array('orders_and_returns', 'widget_instance_title', 'field'),
            array('orders_and_returns', 'select_display_on', 'dropdown'),
            array('orders_and_returns', 'select_block_reference', 'dropdown'),
            array('recently_compared_products', 'widget_instance_title', 'field'),
            array('recently_compared_products', 'number_of_products_to_display_compared_and_viewed', 'field'),
            array('recently_compared_products', 'select_display_on', 'dropdown'),
            array('recently_compared_products', 'select_block_reference', 'dropdown'),
            array('recently_viewed_products', 'widget_instance_title', 'field'),
            array('recently_viewed_products', 'number_of_products_to_display_compared_and_viewed', 'field'),
            array('recently_viewed_products', 'select_display_on', 'dropdown'),
            array('recently_viewed_products', 'select_block_reference', 'dropdown')
        );
    }
}