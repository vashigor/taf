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
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ProductAttribute_Helper extends Mage_Selenium_TestCase
{
    /**
     * Action_helper method for Create Attribute
     * Preconditions: 'Manage Attributes' page is opened.
     *
     * @param array $attrData Array which contains DataSet for filling of the current form
     */
    public function createAttribute($attrData)
    {
        $this->clickButton('add_new_attribute');
        $position = '';
        if (isset($attrData['position'])) {
            $position = $attrData['position'];
            unset($attrData['position']);
        }
        $this->fillForm($attrData, 'properties');
        if ($position) {
            $this->fillField('position', $position);
        }
        $this->fillForm($attrData, 'manage_labels_options');
        $this->storeViewTitles($attrData);
        $this->attributeOptions($attrData);
        $this->saveForm('save_attribute');
    }

    /**
     * Open Product Attribute.
     * Preconditions: 'Manage Attributes' page is opened.
     *
     * @param array $searchData
     */
    public function openAttribute($searchData)
    {
        $this->_prepareDataForSearch($searchData);
        $xpathTR = $this->search($searchData, 'attributes_grid');
        $this->assertNotNull($xpathTR, 'Attribute is not found');
        $this->addParameter('tableLineXpath', $xpathTR);
        $this->addParameter('cellIndex', $this->getColumnIdByName('Attribute Code'));
        $text = $this->getControlAttribute('pageelement', 'table_line_cell_index', 'text');
        $this->addParameter('elementTitle', $text);
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->clickControl('pageelement', 'table_line_cell_index');
    }

    /**
     * Verify all data in saved Attribute.
     * Preconditions: Attribute page is opened.
     *
     * @param array $attrData
     */
    public function verifyAttribute($attrData)
    {
        $this->assertTrue($this->verifyForm($attrData, 'properties'), $this->getParsedMessages());
        $this->openTab('manage_labels_options');
        $this->storeViewTitles($attrData, 'manage_titles', 'verify');
        $this->attributeOptions($attrData, 'verify');
    }

    /**
     * Create Attribute from product page.
     * Preconditions: Product page is opened.
     *
     * @param array $attrData
     */
    public function createAttributeOnGeneralTab($attrData)
    {
        // Defining and adding %fieldSetId% for Uimap pages.
        $id = explode('_', $this->getControlAttribute('fieldset', 'product_general', 'id'));
        foreach ($id as $value) {
            if (is_numeric($value)) {
                $fieldSetId = $value;
                $this->addParameter('tabId', $fieldSetId);
                break;
            }
        }
        //Steps. Click 'Create New Attribute' button, select opened window.
        $this->clickButton('create_new_attribute', false);
        $this->selectLastWindow();
        $this->validatePage('new_product_attribute_from_product_page');
        $this->fillForm($attrData, 'properties');
        $this->fillForm($attrData, 'manage_labels_options');
        $this->storeViewTitles($attrData);
        $this->attributeOptions($attrData);
        $this->addParameter('attributeId', 0);
        $this->saveForm('save_attribute', false);
    }

    /**
     * Fill or Verify Titles for different Store View
     *
     * @param array $attrData
     * @param string $fieldsetName
     * @param string $action
     */
    public function storeViewTitles($attrData, $fieldsetName = 'manage_titles', $action = 'fill')
    {
        $name = 'store_view_titles';
        if (isset($attrData['admin_title'])) {
            $attrData[$name]['Admin'] = $attrData['admin_title'];
        }
        if (array_key_exists($name, $attrData) && is_array($attrData[$name])) {
            $this->addParameter('tableHeadXpath', $this->_getControlXpath('fieldset', $fieldsetName));
            $qtyStore = $this->getControlCount('pageelement', 'table_column');
            foreach ($attrData[$name] as $storeViewName => $storeViewValue) {
                $number = -1;
                for ($i = 1; $i <= $qtyStore; $i++) {
                    $this->addParameter('index', $i);
                    if ($this->getControlAttribute('pageelement', 'table_column_index', 'text') == $storeViewName) {
                        $number = $i;
                        break;
                    }
                }
                if ($number != -1) {
                    $this->addParameter('storeViewNumber', $number);
                    $fieldName = preg_replace('/^manage_/', '', $fieldsetName) . '_by_store_name';
                    switch ($action) {
                        case 'fill':
                            $this->fillField($fieldName, $storeViewValue);
                            break;
                        case 'verify':
                            $actualText = $this->getControlAttribute('field', $fieldName, 'value');
                            $var = array_flip(get_html_translation_table());
                            $actualText = strtr($actualText, $var);
                            $this->assertEquals($storeViewValue, $actualText, 'Stored data not equals to specified');
                            break;
                    }
                } else {
                    $this->fail('Cannot find specified Store View with name \'' . $storeViewName . '\'');
                }
            }
        }
    }

    /**
     * Fill or Verify Options for Dropdown and Multiple Select Attributes
     *
     * @param array $attrData
     * @param string $action
     */
    public function attributeOptions($attrData, $action = 'fill')
    {
        $optionCount = $this->getControlCount('pageelement', 'manage_options_option');
        $number = 1;
        foreach ($attrData as $fKey => $dValue) {
            if (preg_match('/^option_/', $fKey) and is_array($attrData[$fKey])) {
                if ($this->controlIsPresent('fieldset', 'manage_options')) {
                    switch ($action) {
                        case 'fill':
                            $this->addParameter('fieldOptionNumber', $optionCount);
                            $this->clickButton('add_option', false);
                            $this->storeViewTitles($attrData[$fKey], 'manage_options');
                            $this->fillForm($attrData[$fKey], 'manage_labels_options');
                            $optionCount = $this->getControlCount('pageelement', 'manage_options_option');
                            break;
                        case 'verify':
                            if ($optionCount-- > 0) {
                                $this->addParameter('index', $number++);
                                $optionNumber = $this->getControlAttribute('pageelement', 'is_default_option_index',
                                    'selectedValue');
                                $this->addParameter('fieldOptionNumber', $optionNumber);
                                $this->assertTrue($this->verifyForm($attrData[$fKey], 'manage_labels_options'),
                                    $this->getParsedMessages());
                                $this->storeViewTitles($attrData[$fKey], 'manage_options', 'verify');
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * Define Attribute Id
     *
     * @param array $searchData
     *
     * @return int
     */
    public function defineAttributeId(array $searchData)
    {
        $this->navigate('manage_attributes');
        $attrXpath = $this->search($searchData, 'attributes_grid');
        $this->assertNotEquals(null, $attrXpath);

        return $this->defineIdFromTitle($attrXpath);
    }
}