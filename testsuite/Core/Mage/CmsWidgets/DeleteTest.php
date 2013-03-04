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
 * Delete Widget Test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CmsWidgets_DeleteTest extends Mage_Selenium_TestCase
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
        return $this->productHelper()->createSimpleProduct(true);
    }

    /**
     * <p>Creates All Types of widgets with required fields only and delete them</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with required fields filled</p>
     * <p>3. Open newly created widget</p>
     * <p>4. Delete opened widget</p>
     * <p>Expected result</p>
     * <p>Widgets are created and deleted successfully</p>
     *
     * @param array $dataWidgetType
     * @param array $testData
     *
     * @test
     * @dataProvider widgetTypesReqDataProvider
     * @depends preconditionsForTests
     */
    public function deleteAllTypesOfWidgets($dataWidgetType, $testData)
    {
        //Data
        $override = array();
        if ($dataWidgetType == 'catalog_product_link') {
            $override = array('filter_sku'    => $testData['simple']['product_sku'],
                              'category_path' => $testData['category']['path']);
        } elseif ($dataWidgetType == 'catalog_category_link') {
            $override = array('category_path' => $testData['category']['path']);
        }
        $widgetData = $this->loadDataSet('CmsWidget', $dataWidgetType . '_widget_req', $override);
        $widgetToDelete = array('filter_type'  => $widgetData['settings']['type'],
                                'filter_title' => $widgetData['frontend_properties']['widget_instance_title']);
        //Steps
        $this->navigate('manage_cms_widgets');
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        $this->assertMessagePresent('success', 'successfully_saved_widget');
        $this->cmsWidgetsHelper()->deleteWidget($widgetToDelete);
        $this->assertMessagePresent('success', 'successfully_deleted_widget');
    }

    public function widgetTypesReqDataProvider()
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
}