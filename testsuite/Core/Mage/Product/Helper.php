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
class Core_Mage_Product_Helper extends Mage_Selenium_TestCase
{
    public static $arrayToReturn = array();

    /**
     * Fill in Product Settings tab
     *
     * @param array $productData
     * @param string $productType Value - simple|virtual|bundle|configurable|downloadable|grouped
     */
    public function fillProductSettings($productData, $productType = 'simple')
    {
        $attributeSet = (isset($productData['product_attribute_set'])) ? $productData['product_attribute_set'] : null;

        if (!empty($attributeSet)) {
            $this->fillDropdown('product_attribute_set', $attributeSet);
        }
        $this->fillDropdown('product_type', $productType);

        $attributeSetID = $this->getControlAttribute('dropdown', 'product_attribute_set', 'selectedValue');
        $this->addParameter('setId', $attributeSetID);
        $this->addParameter('productType', $productType);

        $this->clickButton('continue');
    }

    /**
     * Select Dropdown Attribute(s) for configurable product creation
     *
     * @param array $productData
     */
    public function fillConfigurableSettings(array $productData)
    {
        $attributes = (isset($productData['configurable_attribute_title']))
            ? explode(',', $productData['configurable_attribute_title'])
            : null;

        if (!empty($attributes)) {
            $attributesId = array();
            $attributes = array_map('trim', $attributes);

            foreach ($attributes as $attributeTitle) {
                $this->addParameter('attributeTitle', $attributeTitle);
                if ($this->controlIsPresent('checkbox', 'configurable_attribute_title')) {
                    $attributesId[] =
                        $this->getControlAttribute('checkbox', 'configurable_attribute_title', 'selectedValue');
                    $this->fillCheckbox('configurable_attribute_title', 'Yes');
                } else {
                    $this->fail("Dropdown attribute with title '$attributeTitle' is not present on the page");
                }
            }

            $attributesUrl = urlencode(base64_encode(implode(',', $attributesId)));
            $this->addParameter('attributesUrl', $attributesUrl);

            $this->clickButton('continue');
        } else {
            $this->fail('Dropdown attribute for configurable product creation is not set');
        }
    }

    /**
     * Fill Product Tab
     *
     * @param array $productData
     * @param string $tabName Value - general|prices|meta_information|images|recurring_profile
     * |design|gift_options|inventory|websites|categories|related|up_sells
     * |cross_sells|custom_options|bundle_items|associated|downloadable_information
     *
     * @return bool
     */
    public function fillProductTab(array $productData, $tabName = 'general')
    {
        $tabData = array();
        $needFilling = false;

        foreach ($productData as $key => $value) {
            if (preg_match('/^' . $tabName . '/', $key)) {
                $tabData[$key] = $value;
            }
        }

        if ($tabData) {
            $needFilling = true;
        }

        if ($tabName == 'websites' && !$this->controlIsPresent('tab', $tabName)) {
            $needFilling = false;
        }

        if (!$needFilling) {
            return true;
        }

        $this->openTab($tabName);

        switch ($tabName) {
            case 'prices':
                $arrayKey = 'prices_tier_price_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $value) {
                        $this->addTierPrice($value);
                    }
                }
                $this->fillForm($tabData, 'prices');
                $this->fillUserAttributesOnTab($tabData, $tabName);
                break;
            case 'websites':
                $websites = explode(',', $tabData[$tabName]);
                $websites = array_map('trim', $websites);
                foreach ($websites as $value) {
                    $this->selectWebsite($value);
                }
                break;
            case 'categories':
                $categories = explode(',', $tabData[$tabName]);
                $categories = array_map('trim', $categories);
                foreach ($categories as $value) {
                    $this->categoryHelper()->selectCategory($value);
                }
                break;
            case 'related':
            case 'up_sells':
            case 'cross_sells':
                $arrayKey = $tabName . '_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $value) {
                        $this->assignProduct($value, $tabName);
                    }
                }
                break;
            case 'custom_options':
                $arrayKey = $tabName . '_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $value) {
                        $this->addCustomOption($value);
                    }
                }
                break;
            case 'bundle_items':
                $arrayKey = $tabName . '_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    if (array_key_exists('ship_bundle_items', $tabData[$arrayKey])) {
                        $array['ship_bundle_items'] = $tabData[$arrayKey]['ship_bundle_items'];
                        $this->fillForm($array, 'bundle_items');
                    }
                    foreach ($tabData[$arrayKey] as $value) {
                        if (is_array($value)) {
                            $this->addBundleOption($value);
                        }
                    }
                }
                break;
            case 'associated':
                $arrayKey = $tabName . '_grouped_data';
                $arrayKey1 = $tabName . '_configurable_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $value) {
                        $this->assignProduct($value, $tabName);
                    }
                } elseif (array_key_exists($arrayKey1, $tabData) && is_array($tabData[$arrayKey1])) {
                    $attributeTitle = (isset($productData['configurable_attribute_title']))
                        ? $productData['configurable_attribute_title']
                        : null;
                    if (!$attributeTitle) {
                        $this->fail('Attribute Title for configurable product is not set');
                    }
                    $this->addParameter('attributeTitle', $attributeTitle);
                    $this->fillForm($tabData[$arrayKey1], $tabName);
                    foreach ($tabData[$arrayKey1] as $value) {
                        if (is_array($value)) {
                            $this->assignProduct($value, $tabName, $attributeTitle);
                        }
                    }
                }
                break;
            case 'downloadable_information':
                $arrayKey = $tabName . '_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $key => $value) {
                        if (preg_match('/^downloadable_sample_/', $key) && is_array($value)) {
                            $this->addDownloadableOption($value, 'sample');
                        }
                        if (preg_match('/^downloadable_link_/', $key) && is_array($value)) {
                            $this->addDownloadableOption($value, 'link');
                        }
                    }
                }
                $this->fillForm($tabData[$arrayKey], $tabName);
                break;
            default:
                $this->fillForm($tabData, $tabName);
                $this->fillUserAttributesOnTab($tabData, $tabName);
                break;
        }
        return true;
    }

    /**
     * Add Tier Price
     *
     * @param array $tierPriceData
     */
    public function addTierPrice(array $tierPriceData)
    {
        $rowNumber = $this->getControlCount('fieldset', 'tier_price_row');
        $this->addParameter('tierPriceId', $rowNumber);
        $this->clickButton('add_tier_price', false);
        $this->fillForm($tierPriceData, 'prices');
    }

    /**
     * Add Custom Option
     *
     * @param array $customOptionData
     */
    public function addCustomOption(array $customOptionData)
    {
        $optionId = $this->getControlCount('fieldset', 'custom_option_set') + 1;
        $this->addParameter('optionId', $optionId);
        $this->clickButton('add_option', false);
        $this->fillForm($customOptionData, 'custom_options');
        foreach ($customOptionData as $rowKey => $rowValue) {
            if (preg_match('/^custom_option_row/', $rowKey) && is_array($rowValue)) {
                $rowId = $this->getControlCount('pageelement', 'custom_option_row');
                $this->addParameter('rowId', $rowId);
                $this->clickButton('add_row', false);
                $this->fillForm($rowValue, 'custom_options');
            }
        }
    }

    /**
     * Select Website by Website name
     *
     * @param $websiteName
     * @param $action
     */
    public function selectWebsite($websiteName, $action = 'select')
    {
        $this->addParameter('websiteName', $websiteName);
        $this->assertTrue($this->controlIsPresent('checkbox', 'websites'),
            'Website with name "' . $websiteName . '" does not exist');

        switch ($action) {
            case 'select':
                $this->fillCheckbox('websites', 'Yes');
                break;
            case 'verify':
                $currentValue = $this->getControlAttribute('checkbox', 'websites', 'value');
                if ($currentValue == 'off' || $currentValue == '0') {
                    $this->addVerificationMessage('Website with name "' . $websiteName . '" is not selected');
                }
                break;
        }
    }

    /**
     * Assign product. Use for fill in 'Related Products', 'Up-sells' or 'Cross-sells' tabs
     *
     * @param array $data
     * @param string $tabName
     * @param string $attributeTitle
     */
    public function assignProduct(array $data, $tabName, $attributeTitle = null)
    {
        $fillingData = array();

        foreach ($data as $key => $value) {
            if (!preg_match('/^' . $tabName . '_search_/', $key)) {
                $fillingData[$key] = $value;
                unset($data[$key]);
            }
        }

        if ($attributeTitle) {
            $this->addParameter('cellName', $attributeTitle);
            $attributeCode = $this->getControlAttribute('pageelement', 'table_header_cell_name', 'name');
            $this->addParameter('attributeCode', $attributeCode);
            $this->addParameter('attributeTitle', $attributeTitle);
        }
        $this->searchAndChoose($data, $tabName);
        //Fill in additional data
        if ($fillingData) {
            $xpathTR = $this->formSearchXpath($data);
            if ($attributeTitle) {
                $number = $this->getColumnIdByName($attributeTitle,
                    $this->_getControlXpath('pageelement', 'associated_table'));
                $this->addParameter('tableLineXpath', $this->_getControlXpath('fieldset', 'associated') . $xpathTR);
                $this->addParameter('cellIndex', $number);
                $this->addParameter('attributeValue',
                    $this->getControlAttribute('pageelement', 'table_line_cell_index', 'text'));
            } else {
                $this->addParameter('productXpath', $xpathTR);
            }
            $this->fillForm($fillingData, $tabName);
        }
    }

    /**
     * Add Bundle Option
     *
     * @param array $bundleOptionData
     */
    public function addBundleOption(array $bundleOptionData)
    {
        $optionsCount = $this->getControlCount('pageelement', 'bundle_item_row');
        $this->addParameter('optionId', $optionsCount);
        $this->clickButton('add_new_option', false);
        $this->fillForm($bundleOptionData, 'bundle_items');
        foreach ($bundleOptionData as $value) {
            $productSearch = array();
            $selectionSettings = array();
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if ($k == 'bundle_items_search_name' or $k == 'bundle_items_search_sku') {
                        $this->addParameter('productSku', $v);
                    }
                    if (preg_match('/^bundle_items_search_/', $k)) {
                        $productSearch[$k] = $v;
                    } elseif ($k == 'bundle_items_qty_to_add') {
                        $selectionSettings['selection_item_default_qty'] = $v;
                    } elseif (preg_match('/^selection_item_/', $k)) {
                        $selectionSettings[$k] = $v;
                    }
                }
                if ($productSearch) {
                    $this->clickButton('add_selection', false);
                    $this->pleaseWait();
                    $this->searchAndChoose($productSearch, 'select_product_to_bundle_option');
                    $this->clickButton('add_selected_products', false);
                    if ($selectionSettings) {
                        $this->fillForm($selectionSettings);
                    }
                }
            }
        }
    }

    /**
     * Add Sample for Downloadable product
     *
     * @param array $optionData
     * @param string $type
     */
    public function addDownloadableOption(array $optionData, $type)
    {
        if (!$this->controlIsPresent('pageelement', 'opened_downloadable_' . $type)) {
            $this->clickControl('link', 'downloadable_' . $type, false);
        }
        $rowNumber = $this->getControlCount('pageelement', 'added_downloadable_' . $type);
        $this->addParameter('rowId', $rowNumber);
        $this->clickButton('downloadable_' . $type . '_add_new_row', false);
        $this->fillForm($optionData, 'downloadable_information');
    }

    /**
     * Fill user product attribute
     *
     * @param array $productData
     * @param string $tabName
     */
    public function fillUserAttributesOnTab(array $productData, $tabName)
    {
        $userFieldData = $tabName . '_user_attr';
        if (array_key_exists($userFieldData, $productData) && is_array($productData[$userFieldData])) {
            foreach ($productData[$userFieldData] as $fieldType => $dataArray) {
                if (!is_array($dataArray)) {
                    continue;
                }
                foreach ($dataArray as $fieldKey => $fieldValue) {
                    $this->addParameter('attributeCode' . ucfirst(strtolower($fieldType)), $fieldKey);
                    $fillFunction = 'fill' . ucfirst(strtolower($fieldType));
                    $this->$fillFunction($tabName . '_user_attr_' . $fieldType, $fieldValue);
                }
            }
        }
    }

    /**
     * Create Product
     *
     * @param array $productData
     * @param string $productType
     */
    public function createProduct(array $productData, $productType = 'simple')
    {
        $this->clickButton('add_new_product');
        $this->fillProductSettings($productData, $productType);
        if ($productType == 'configurable') {
            $this->fillConfigurableSettings($productData);
        }
        $this->fillProductInfo($productData, $productType);
        $this->saveForm('save');
    }

    /**
     * Fill Product info
     *
     * @param array $productData
     * @param string $productType
     */
    public function fillProductInfo(array $productData, $productType = 'simple')
    {
        $this->fillProductTab($productData);
        $this->fillProductTab($productData, 'prices');
        $this->fillProductTab($productData, 'meta_information');
        //@TODO Fill in Images Tab
        if ($productType == 'simple' || $productType == 'virtual') {
            $this->fillProductTab($productData, 'recurring_profile');
        }
        $this->fillProductTab($productData, 'design');
        $this->fillProductTab($productData, 'gift_options');
        $this->fillProductTab($productData, 'inventory');
        $this->fillProductTab($productData, 'websites');
        $this->fillProductTab($productData, 'categories');
        $this->fillProductTab($productData, 'related');
        $this->fillProductTab($productData, 'up_sells');
        $this->fillProductTab($productData, 'cross_sells');
        $this->fillProductTab($productData, 'custom_options');
        if ($productType == 'grouped' || $productType == 'configurable') {
            $this->fillProductTab($productData, 'associated');
        }
        if ($productType == 'bundle') {
            $this->fillProductTab($productData, 'bundle_items');
        }
        if ($productType == 'downloadable') {
            $this->fillProductTab($productData, 'downloadable_information');
        }
    }

    /**
     * Define attribute set ID that used in product
     *
     * @param array $productSearchData
     *
     * @return string
     */
    public function defineAttributeSetUsedInProduct(array $productSearchData)
    {
        $productXpath = $this->search($productSearchData, 'product_grid');
        $this->assertNotEquals(null, $productXpath);
        $columnId = $this->getColumnIdByName('Attrib. Set Name');
        $this->addParameter('cellIndex', $columnId);
        $this->addParameter('tableLineXpath', $productXpath);
        $value = $this->getControlAttribute('pageelement', 'table_line_cell_index', 'text');
        $this->addParameter('optionText', $value);

        return $this->getControlAttribute('pageelement', 'table_head_cell_index_option_text', 'value');
    }

    /**
     * Open product.
     *
     * @param array $productSearch
     */
    public function openProduct(array $productSearch)
    {
        $this->_prepareDataForSearch($productSearch);
        $xpathTR = $this->search($productSearch, 'product_grid');
        $this->assertNotNull($xpathTR, 'Product is not found');
        $cellId = $this->getColumnIdByName('Name');
        $this->addParameter('tableLineXpath', $xpathTR);
        $this->addParameter('cellIndex', $cellId);
        $param = $this->getControlAttribute('pageelement', 'table_line_cell_index', 'text');
        $this->addParameter('elementTitle', $param);
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->clickControl('pageelement', 'table_line_cell_index');
    }

    /**
     * Verify product info
     *
     * @param array $productData
     * @param array $skipElements
     */
    public function verifyProductInfo(array $productData, $skipElements = array())
    {
        $nestedArrays = array();
        foreach ($productData as $key => $value) {
            if (is_array($value)) {
                $nestedArrays[$key] = $value;
                unset($productData[$key]);
            }
            if ($key == 'websites' or $key == 'categories') {
                $nestedArrays[$key] = $value;
                unset($productData[$key]);
            }
        }
        $this->verifyForm($productData, null, $skipElements);
        // Verify tier prices
        if (array_key_exists('prices_tier_price_data', $nestedArrays)) {
            $this->verifyTierPrices($nestedArrays['prices_tier_price_data']);
        }
        //Verify selected websites
        if (array_key_exists('websites', $nestedArrays)) {
            if ($this->controlIsPresent('tab', 'websites')) {
                $this->openTab('websites');
                $websites = explode(',', $nestedArrays['websites']);
                $websites = array_map('trim', $websites);
                foreach ($websites as $value) {
                    $this->selectWebsite($value, 'verify');
                }
            }
        }
        //Verify selected categories
        if (array_key_exists('categories', $nestedArrays)) {
            $categories = explode(',', $nestedArrays['categories']);
            $categories = array_map('trim', $categories);
            $this->openTab('categories');
            foreach ($categories as $value) {
                $this->isSelectedCategory($value);
            }
        }
        //Verify assigned products for 'Related Products', 'Up-sells', 'Cross-sells' tabs
        if (array_key_exists('related_data', $nestedArrays)) {
            $this->openTab('related');
            foreach ($nestedArrays['related_data'] as $value) {
                $this->isAssignedProduct($value, 'related');
            }
        }
        if (array_key_exists('up_sells_data', $nestedArrays)) {
            $this->openTab('up_sells');
            foreach ($nestedArrays['up_sells_data'] as $value) {
                $this->isAssignedProduct($value, 'up_sells');
            }
        }
        if (array_key_exists('cross_sells_data', $nestedArrays)) {
            $this->openTab('cross_sells');
            foreach ($nestedArrays['cross_sells_data'] as $value) {
                $this->isAssignedProduct($value, 'cross_sells');
            }
        }
        // Verify Associated Products tab
        if (array_key_exists('associated_grouped_data', $nestedArrays)) {
            $this->openTab('associated');
            foreach ($nestedArrays['associated_grouped_data'] as $value) {
                $this->isAssignedProduct($value, 'associated');
            }
        }
        if (array_key_exists('associated_configurable_data', $nestedArrays)) {
            $this->openTab('associated');
            $attributeTitle = (isset($productData['configurable_attribute_title']))
                ? $productData['configurable_attribute_title']
                : null;
            if (!$attributeTitle) {
                $this->fail('Attribute Title for configurable product is not set');
            }
            $this->addParameter('attributeTitle', $attributeTitle);
            $this->verifyForm($nestedArrays['associated_configurable_data'], 'associated');
            foreach ($nestedArrays['associated_configurable_data'] as $value) {
                if (is_array($value)) {
                    $this->isAssignedProduct($value, 'associated', $attributeTitle);
                }
            }
        }
        if (array_key_exists('custom_options_data', $nestedArrays)) {
            $this->verifyCustomOption($nestedArrays['custom_options_data']);
        }
        if (array_key_exists('bundle_items_data', $nestedArrays)) {
            $this->verifyBundleOptions($nestedArrays['bundle_items_data']);
        }
        if (array_key_exists('downloadable_information_data', $nestedArrays)) {
            $samples = array();
            $links = array();
            foreach ($nestedArrays['downloadable_information_data'] as $key => $value) {
                if (preg_match('/^downloadable_sample_/', $key) && is_array($value)) {
                    $samples[$key] = $value;
                }
                if (preg_match('/^downloadable_link_/', $key) && is_array($value)) {
                    $links[$key] = $value;
                }
            }
            if ($samples) {
                $this->verifyDownloadableOptions($samples, 'sample');
            }
            if ($links) {
                $this->verifyDownloadableOptions($links, 'link');
            }
            $this->verifyForm($nestedArrays['downloadable_information_data'], 'downloadable_information');
        }
        // Error Output
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Verify Tier Prices
     *
     * @param array $tierPriceData
     *
     * @return boolean
     */
    public function verifyTierPrices(array $tierPriceData)
    {
        $rowQty = $this->getControlCount('fieldset', 'tier_price_row');
        $needCount = count($tierPriceData);
        if ($needCount != $rowQty) {
            $this->addVerificationMessage(
                'Product must be contains ' . $needCount . 'Tier Price(s), but contains ' . $rowQty);
            return false;
        }
        $i = 0;
        foreach ($tierPriceData as $value) {
            $this->addParameter('tierPriceId', $i);
            $this->verifyForm($value, 'prices');
            $i++;
        }
        return true;
    }

    /**
     * Verify that category is selected
     *
     * @param string $categoryPath
     */
    public function isSelectedCategory($categoryPath)
    {
        $nodes = explode('/', $categoryPath);
        $rootCat = array_shift($nodes);

        $correctRoot = $this->categoryHelper()->defineCorrectCategory($rootCat);

        foreach ($nodes as $value) {
            $correctSubCat = array();

            for ($i = 0; $i < count($correctRoot); $i++) {
                $correctSubCat = array_merge($correctSubCat,
                    $this->categoryHelper()->defineCorrectCategory($value, $correctRoot[$i]));
            }
            $correctRoot = $correctSubCat;
        }

        if ($correctRoot) {
            $this->addParameter('categoryId', array_shift($correctRoot));
            if ($this->getControlAttribute('checkbox', 'category_by_id', 'value') == 'off') {
                $this->addVerificationMessage('Category with path: "' . $categoryPath . '" is not selected');
            }
        } else {
            $this->addVerificationMessage("Category with path='$categoryPath' not found");
        }
    }

    /**
     * Verify that product is assigned
     *
     * @param array $data
     * @param string $fieldSetName
     * @param string $attributeTitle
     */
    public function isAssignedProduct(array $data, $fieldSetName, $attributeTitle = null)
    {
        $fillingData = array();

        foreach ($data as $key => $value) {
            if (!preg_match('/^' . $fieldSetName . '_search_/', $key)) {
                $fillingData[$key] = $value;
                unset($data[$key]);
            }
        }

        if ($attributeTitle) {
            $this->addParameter('cellName', $attributeTitle);
            $attributeCode = $this->getControlAttribute('pageelement', 'table_header_cell_name', 'name');
            $this->addParameter('attributeCode', $attributeCode);
            $this->addParameter('attributeTitle', $attributeTitle);
        }

        $xpathTR = $this->search($data, $fieldSetName);
        if (is_null($xpathTR)) {
            $this->addVerificationMessage(
                $fieldSetName . " tab: Product is not assigned with data: \n" . print_r($data, true));
        } else {
            if ($fillingData) {
                if ($attributeTitle) {
                    $this->addParameter('fieldsetXpath', $this->_getControlXpath('fieldset', 'associated'));
                    $xpath = $this->_getControlXpath('pageelement', 'table_in_fieldset');
                    $number = $this->getColumnIdByName($attributeTitle, $xpath);
                    $this->addParameter('tableLineXpath', $xpathTR);
                    $this->addParameter('cellIndex', $number);
                    $attributeValue = $this->getControlAttribute('pageelement', 'table_line_cell_index', 'text');
                    $this->addParameter('attributeValue', $attributeValue);
                } else {
                    $fieldsetXpath = $this->_getControlXpath('fieldset', $fieldSetName);
                    $this->addParameter('productXpath', str_replace($fieldsetXpath, '', $xpathTR));
                }
                $this->verifyForm($fillingData, $fieldSetName);
            }
        }
    }

    /**
     * Verify Custom Options
     *
     * @param array $customOptionData
     *
     * @return boolean
     */
    public function verifyCustomOption(array $customOptionData)
    {
        $this->openTab('custom_options');
        $optionsQty = $this->getControlCount('fieldset', 'custom_option_set');
        $needCount = count($customOptionData);
        if ($needCount != $optionsQty) {
            $this->addVerificationMessage(
                'Product must be contains ' . $needCount . ' Custom Option(s), but contains ' . $optionsQty);
            return false;
        }
        $optionId = '';
        $this->addParameter('elementXpath', $this->_getControlXpath('fieldset', 'custom_option_set'));
        $this->addParameter('index', 1);
        $id = $this->getControlAttribute('pageelement', 'element_index', 'id');
        $id = explode('_', $id);
        foreach ($id as $value) {
            if (is_numeric($value)) {
                $optionId = $value;
            }
        }
        // @TODO Need implement full verification for custom options with type = select (not tested rows)
        foreach ($customOptionData as $value) {
            if (is_array($value)) {
                $this->addParameter('optionId', $optionId);
                $this->verifyForm($value, 'custom_options');
                $optionId--;
            }
        }
        return true;
    }

    /**
     * verify Bundle Options
     *
     * @param array $bundleData
     *
     * @return boolean
     */
    public function verifyBundleOptions(array $bundleData)
    {
        $this->openTab('bundle_items');
        $optionsCount = $this->getControlCount('pageelement', 'bundle_item_grid');
        $needCount = count($bundleData);
        if (array_key_exists('ship_bundle_items', $bundleData)) {
            $needCount = $needCount - 1;
        }
        if ($needCount != $optionsCount) {
            $this->addVerificationMessage(
                'Product must be contains ' . $needCount . 'Bundle Item(s), but contains ' . $optionsCount);
            return false;
        }

        $i = 0;
        foreach ($bundleData as $option => $values) {
            if (is_string($values)) {
                $this->verifyForm(array($option => $values), 'bundle_items');
            }
            if (is_array($values)) {
                $this->addParameter('optionId', $i);
                $this->verifyForm($values, 'bundle_items');
                foreach ($values as $k => $v) {
                    if (preg_match('/^add_product_/', $k) && is_array($v)) {
                        $selectionSettings = array();
                        $productSku = '';
                        foreach ($v as $field => $data) {
                            if ($field == 'bundle_items_search_name' or $field == 'bundle_items_search_sku') {
                                $productSku = $data;
                            }
                            if (!preg_match('/^bundle_items_search/', $field)) {
                                if ($field == 'bundle_items_qty_to_add') {
                                    $selectionSettings['selection_item_default_qty'] = $data;
                                } else {
                                    $selectionSettings[$field] = $data;
                                }
                            }
                        }
                        $k = $i + 1;
                        $this->addParameter('productSku', $productSku);
                        $this->addParameter('index', $k);
                        if (!$this->controlIsPresent('pageelement', 'bundle_item_grid_index_product')) {
                            $this->addVerificationMessage(
                                "Product with sku(name)'" . $productSku . "' is not assigned to bundle item $i");
                        } else {
                            if ($selectionSettings) {
                                $this->addParameter('productSku', $productSku);
                                $this->verifyForm($selectionSettings, 'bundle_items');
                            }
                        }
                    }
                }
                $i++;
            }
        }
        return true;
    }

    /**
     * Verify Downloadable Options
     *
     * @param array $optionsData
     * @param string $type
     *
     * @return bool
     */
    public function verifyDownloadableOptions(array $optionsData, $type)
    {
        $this->openTab('downloadable_information');
        $rowQty = $this->getControlCount('pageelement', 'downloadable_' . $type . '_row');
        $needCount = count($optionsData);
        if ($needCount != $rowQty) {
            $this->addVerificationMessage(
                'Product must be contains ' . $needCount . ' Downloadable ' . $type . '(s), but contains ' . $rowQty);
            return false;
        }
        $i = 0;
        foreach ($optionsData as $value) {
            $this->addParameter('rowId', $i);
            $this->verifyForm($value, 'downloadable_information');
            $i++;
        }
        return true;
    }

    /**
     * Unselect any associated product(as up_sells, cross_sells, related) to opened product
     *
     * @param string $type
     * @param bool $saveChanges
     */
    public function unselectAssociatedProduct($type, $saveChanges = false)
    {
        $this->openTab($type);
        $this->addParameter('tableXpath', $this->_getControlXpath('fieldset', $type));
        if (!$this->controlIsPresent('message', 'specific_table_no_records_found')) {
            $this->fillCheckbox($type . '_select_all', 'No');
            if ($saveChanges) {
                $this->saveAndContinueEdit('button', 'save_and_continue_edit');
                $this->assertTrue($this->controlIsPresent('message', 'specific_table_no_records_found'),
                    'There are products assigned to "' . $type . '" tab');
            }
        }
    }

    #*******************************************
    #*         Frontend Helper Methods         *
    #*******************************************

    /**
     * Open product on FrontEnd
     *
     * @param string $productName
     */
    public function frontOpenProduct($productName)
    {
        if (!is_string($productName)) {
            $this->fail('Wrong data to open a product');
        }
        $productUrl = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '-', $productName)), '-');
        $this->addParameter('productUrl', $productUrl);
        $this->addParameter('elementTitle', $productName);
        $this->frontend('product_page', false);
        $this->setCurrentPage($this->getCurrentLocationUimapPage()->getPageId());
        $this->addParameter('productName', $productName);
        $openedProductName = $this->getControlAttribute('pageelement', 'product_name', 'text');
        $this->assertEquals($productName, $openedProductName,
            "Product with name '$openedProductName' is opened, but should be '$productName'");
    }

    /**
     * Add product to shopping cart
     *
     * @param array|null $dataForBuy
     */
    public function frontAddProductToCart($dataForBuy = null)
    {
        if ($dataForBuy) {
            $this->frontFillBuyInfo($dataForBuy);
        }
        $openedProductName = $this->getControlAttribute('pageelement', 'product_name', 'text');
        $this->addParameter('productName', $openedProductName);
        $this->saveForm('add_to_cart');
        $this->assertMessageNotPresent('validation');
    }

    /**
     * Choose custom options and additional products
     *
     * @param array $dataForBuy
     */
    public function frontFillBuyInfo($dataForBuy)
    {
        foreach ($dataForBuy as $value) {
            $fill = (isset($value['options_to_choose'])) ? $value['options_to_choose'] : array();
            $params = (isset($value['parameters'])) ? $value['parameters'] : array();
            foreach ($params as $k => $v) {
                $this->addParameter($k, $v);
            }
            $this->fillForm($fill);
        }
    }

    /**
     * Verify product info on frontend
     *
     * @param array $productData
     */
    public function frontVerifyProductInfo(array $productData)
    {
        $this->frontOpenProduct($productData['general_name']);
        $xpathArray = $this->getCustomOptionsXpathes($productData);
        foreach ($xpathArray as $fieldName => $data) {
            if (is_string($data)) {
                if (!$this->isElementPresent($data)) {
                    $this->addVerificationMessage('Could not find element ' . $fieldName);
                }
            } else {
                foreach ($data as $optionData) {
                    foreach ($optionData as $x => $y) {
                        if (!preg_match('/xpath/', $x)) {
                            continue;
                        }
                        if (!$this->isElementPresent($y)) {
                            $this->addVerificationMessage(
                                'Could not find element type "' . $optionData['type'] . '" and title "'
                                . $optionData['title'] . '"');
                        }
                    }
                }
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Gets the xpathes for validation on frontend
     *
     * @param array $productData
     *
     * @return array
     */
    public function getCustomOptionsXpathes(array $productData)
    {
        $xpathArray = array();
        $date = strtotime(date("m/d/Y"));
        $startDate = isset($productData['prices_special_price_from'])
            ? strtotime($productData['prices_special_price_from'])
            : 1;
        $expirationDate = isset($productData['prices_special_price_to'])
            ? strtotime($productData['prices_special_price_to'])
            : 1;
        if ($startDate <= $date && $expirationDate >= $date) {
            $priceToCalc = $productData['prices_special_price'];
        } else {
            $priceToCalc = $productData['prices_price'];
        }
        $avail = (isset($productData['inventory_stock_availability']))
            ? $productData['inventory_stock_availability']
            : null;
        $allowedQty = (isset($productData['inventory_min_allowed_qty']))
            ? $productData['inventory_min_allowed_qty']
            : null;
        $shortDescription = (isset($productData['general_short_description']))
            ? $productData['general_short_description']
            : null;
        $longDescription = (isset($productData['general_description'])) ? $productData['general_description'] : null;
        if ($shortDescription) {
            $this->addParameter('shortDescription', $shortDescription);
            $xpathArray['Short Description'] = $this->_getControlXpath('pageelement', 'short_description');
        }
        if ($longDescription) {
            $this->addParameter('longDescription', $longDescription);
            $xpathArray['Description'] = $this->_getControlXpath('pageelement', 'description');
        }
        $avail = ($avail == 'In Stock') ? 'In stock' : 'Out of stock';
        if ($avail == 'Out of stock') {
            $this->addParameter('avail', $avail);
            $xpathArray['Availability'] = $this->_getControlXpath('pageelement', 'availability_param');
            return $xpathArray;
        }
        $allowedQty = ($allowedQty == null) ? '1' : $allowedQty;
        $this->addParameter('price', $allowedQty);
        $xpathArray['Quantity'] = $this->_getControlXpath('pageelement', 'qty');
        $i = 0;
        foreach ($productData['custom_options_data'] as $value) {
            $title = $value['custom_options_general_title'];
            $optionType = $value['custom_options_general_input_type'];
            $xpathArray['custom_options']['option_' . $i]['title'] = $title;
            $xpathArray['custom_options']['option_' . $i]['type'] = $optionType;
            $this->addParameter('title', $title);
            if ($value['custom_options_general_input_type'] == 'Drop-down'
                || $value['custom_options_general_input_type'] == 'Multiple Select'
            ) {
                $someArr = $this->_formXpathForCustomOptionsRows($value, $priceToCalc, $i, 'custom_option_select');
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            } elseif ($value['custom_options_general_input_type'] == 'Radio Buttons'
                      || $value['custom_options_general_input_type'] == 'Checkbox'
            ) {
                $someArr = $this->_formXpathForCustomOptionsRows($value, $priceToCalc, $i, 'custom_option_check');
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            } else {
                $someArr = $this->_formXpathesForFieldsArray($value, $i, $priceToCalc);
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            }
            $i++;
        }
        return $xpathArray;
    }

    /**
     * @param array $value
     * @param int $i
     * @param string $priceToCalc
     *
     * @return array
     */
    private function _formXpathesForFieldsArray(array $value, $i, $priceToCalc)
    {
        $xpathArray = array();
        if (array_key_exists('custom_options_price_type', $value)) {
            if ($value['custom_options_price_type'] == 'Fixed' && isset($value['custom_options_price'])) {
                $price = '$' . number_format((float)$value['custom_options_price'], 2);
                $this->addParameter('price', $price);
                $xpath = $this->_getControlXpath('pageelement', 'custom_option_non_select');
                $someArr = $this->_defineXpathForAdditionalOptions($value, $i, $xpath);
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            } elseif ($value['custom_options_price_type'] == 'Percent' && isset($value['custom_options_price'])) {
                $price = '$' . number_format(round($priceToCalc / 100 * $value['custom_options_price'], 2), 2);
                $this->addParameter('price', $price);
                $xpath = $this->_getControlXpath('pageelement', 'custom_option_non_select');
                $someArr = $this->_defineXpathForAdditionalOptions($value, $i, $xpath);
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            } else {
                $xpath = $this->_getControlXpath('pageelement', 'custom_option_non_select_wo_price');
                $someArr = $this->_defineXpathForAdditionalOptions($value, $i, $xpath);
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            }
        }
        return $xpathArray;
    }

    /**
     * @param array $value
     * @param int $i
     * @param string $xpath
     *
     * @return array
     */
    private function _defineXpathForAdditionalOptions(array $value, $i, $xpath)
    {
        $xpathArray = array();
        $count = 0;
        if (array_key_exists('custom_options_max_characters', $value)
            || array_key_exists('custom_options_allowed_file_extension', $value)
            || array_key_exists('custom_options_image_size_x', $value)
            || array_key_exists('custom_options_image_size_y', $value)
        ) {
            if (array_key_exists('custom_options_max_characters', $value)) {
                $this->addParameter('maxChars', $value['custom_options_max_characters']);
                $xpathMax = $this->_getControlXpath('pageelement', 'custom_option_max_chars');
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] = $xpathMax;
            }
            if (array_key_exists('custom_options_allowed_file_extension', $value)) {
                $this->addParameter('fileExt', $value['custom_options_allowed_file_extension']);
                $xpathExt = $this->_getControlXpath('pageelement', 'custom_option_file_ext');
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] = $xpathExt;
            }
            if (array_key_exists('custom_options_image_size_x', $value)) {
                $this->addParameter('fileWidth', $value['custom_options_image_size_x']);
                $xpathExt = $this->_getControlXpath('pageelement', 'custom_option_file_max_width');
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] = $xpathExt;
            }
            if (array_key_exists('custom_options_image_size_y', $value)) {
                $this->addParameter('fileHeight', $value['custom_options_image_size_y']);
                $xpathExt = $this->_getControlXpath('pageelement', 'custom_option_file_max_height');
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count] = $xpathExt;
            }
        } else {
            $xpathArray['custom_options']['option_' . $i]['xpath_' . $count] = $xpath;
        }
        return $xpathArray;
    }

    /**
     * @param array $options
     * @param string $priceToCalc
     * @param int $i
     * @param string $pageelement
     *
     * @return array
     */
    private function _formXpathForCustomOptionsRows(array $options, $priceToCalc, $i, $pageelement)
    {
        $xpathArray = array();
        $count = 0;
        foreach ($options as $k => $v) {
            if (!preg_match('/^custom_option_row_/', $k)) {
                continue;
            }
            $optionTitle = $v['custom_options_title'];
            $this->addParameter('optionTitle', $optionTitle);
            if (array_key_exists('custom_options_price_type', $v)) {
                if ($v['custom_options_price_type'] == 'Fixed' && isset($v['custom_options_price'])) {
                    $optionPrice = '$' . number_format((float)$v['custom_options_price'], 2);
                    $this->addParameter('optionPrice', $optionPrice);
                    $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] =
                        $this->_getControlXpath('pageelement', $pageelement);
                } elseif ($v['custom_options_price_type'] == 'Percent' && isset($v['custom_options_price'])) {
                    $optionPrice = '$' . number_format(round($priceToCalc / 100 * $v['custom_options_price'], 2), 2);
                    $this->addParameter('optionPrice', $optionPrice);
                    $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] =
                        $this->_getControlXpath('pageelement', $pageelement);
                } else {
                    $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] =
                        $this->_getControlXpath('pageelement', $pageelement . '_wo_price');
                }
            } else {
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] =
                    $this->_getControlXpath('pageelement', $pageelement . '_wo_price');
            }
        }
        return $xpathArray;
    }

    /**
     * Create Configurable product
     *
     * @param bool $inSubCategory
     *
     * @return array
     */
    public function createConfigurableProduct($inSubCategory = false)
    {
        //Create category
        if ($inSubCategory) {
            $category = $this->loadDataSet('Category', 'sub_category_required');
            $catPath = $category['parent_category'] . '/' . $category['name'];
            $this->navigate('manage_categories', false);
            $this->categoryHelper()->checkCategoriesPage();
            $this->categoryHelper()->createCategory($category);
            $this->assertMessagePresent('success', 'success_saved_category');
            $returnCategory = array('name' => $category['name'], 'path' => $catPath);
        } else {
            $returnCategory = array('name' => 'Default Category', 'path' => 'Default Category');
        }
        //Create product
        $productCat = array('categories' => $returnCategory['path']);
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_dropdown_with_options');
        $configurableOptions = array($attrData['option_1']['store_view_titles']['Default Store View'],
                                     $attrData['option_2']['store_view_titles']['Default Store View'],
                                     $attrData['option_3']['store_view_titles']['Default Store View']);
        $attrCode = $attrData['attribute_code'];
        $associatedAttributes =
            $this->loadDataSet('AttributeSet', 'associated_attributes', array('General' => $attrCode));
        $simple = $this->loadDataSet('Product', 'simple_product_visible', $productCat);
        $simple['general_user_attr']['dropdown'][$attrCode] = $attrData['option_1']['admin_option_name'];
        $virtual = $this->loadDataSet('Product', 'virtual_product_visible', $productCat);
        $virtual['general_user_attr']['dropdown'][$attrCode] = $attrData['option_2']['admin_option_name'];
        $download = $this->loadDataSet('SalesOrder', 'downloadable_product_for_order',
            array('downloadable_links_purchased_separately' => 'No',
                  'categories'                              => $returnCategory['path']));
        $download['general_user_attr']['dropdown'][$attrCode] = $attrData['option_3']['admin_option_name'];
        $configurable = $this->loadDataSet('SalesOrder', 'configurable_product_for_order',
            array('configurable_attribute_title' => $attrData['admin_title'],
                  'categories'                   => $returnCategory['path']),
            array('associated_1' => $simple['general_sku'], 'associated_2' => $virtual['general_sku'],
                  'associated_3' => $download['general_sku']));
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        $this->assertMessagePresent('success', 'success_attribute_set_saved');
        $this->navigate('manage_products');
        $this->createProduct($simple);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->createProduct($virtual, 'virtual');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->createProduct($download, 'downloadable');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->createProduct($configurable, 'configurable');
        $this->assertMessagePresent('success', 'success_saved_product');

        return array('simple'             => array('product_name' => $simple['general_name'],
                                                   'product_sku'  => $simple['general_sku']),
                     'downloadable'       => array('product_name' => $download['general_name'],
                                                   'product_sku'  => $download['general_sku']),
                     'virtual'            => array('product_name' => $virtual['general_name'],
                                                   'product_sku'  => $virtual['general_sku']),
                     'configurable'       => array('product_name' => $configurable['general_name'],
                                                   'product_sku'  => $configurable['general_sku']),
                     'simpleOption'       => array('option'       => $attrData['option_1']['admin_option_name'],
                                                   'option_front' => $configurableOptions[0]),
                     'virtualOption'      => array('option'       => $attrData['option_2']['admin_option_name'],
                                                   'option_front' => $configurableOptions[1]),
                     'downloadableOption' => array('option'       => $attrData['option_3']['admin_option_name'],
                                                   'option_front' => $configurableOptions[2]),
                     'configurableOption' => array('title'                 => $attrData['admin_title'],
                                                   'custom_option_dropdown'=> $configurableOptions[0]),
                     'attribute'          => array('title'       => $attrData['admin_title'],
                                                   'title_front' => $attrData['store_view_titles']['Default Store View'],
                                                   'code'        => $attrCode),
                     'category'           => $returnCategory);
    }

    /**
     * Create Grouped product
     *
     * @param bool $inSubCategory
     *
     * @return array
     */
    public function createGroupedProduct($inSubCategory = false)
    {
        //Create category
        if ($inSubCategory) {
            $category = $this->loadDataSet('Category', 'sub_category_required');
            $catPath = $category['parent_category'] . '/' . $category['name'];
            $this->navigate('manage_categories', false);
            $this->categoryHelper()->checkCategoriesPage();
            $this->categoryHelper()->createCategory($category);
            $this->assertMessagePresent('success', 'success_saved_category');
            $returnCategory = array('name' => $category['name'], 'path' => $catPath);
        } else {
            $returnCategory = array('name' => 'Default Category', 'path' => 'Default Category');
        }
        //Create product
        $productCat = array('categories' => $returnCategory['path']);
        $simple = $this->loadDataSet('Product', 'simple_product_visible', $productCat);
        $virtual = $this->loadDataSet('Product', 'virtual_product_visible', $productCat);
        $download = $this->loadDataSet('SalesOrder', 'downloadable_product_for_order',
            array('downloadable_links_purchased_separately' => 'No',
                  'categories'                              => $returnCategory['path']));
        $grouped = $this->loadDataSet('SalesOrder', 'grouped_product_for_order', $productCat,
            array('associated_1' => $simple['general_sku'], 'associated_2' => $virtual['general_sku'],
                  'associated_3' => $download['general_sku']));
        $this->navigate('manage_products');
        $this->createProduct($simple);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->createProduct($virtual, 'virtual');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->createProduct($download, 'downloadable');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->createProduct($grouped, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');

        return array('simple'        => array('product_name' => $simple['general_name'],
                                              'product_sku'  => $simple['general_sku']),
                     'downloadable'  => array('product_name' => $download['general_name'],
                                              'product_sku'  => $download['general_sku']),
                     'virtual'       => array('product_name' => $virtual['general_name'],
                                              'product_sku'  => $virtual['general_sku']),
                     'grouped'       => array('product_name' => $grouped['general_name'],
                                              'product_sku'  => $grouped['general_sku']),
                     'category'      => $returnCategory,
                     'groupedOption' => array('subProduct_1' => $simple['general_name'],
                                              'subProduct_2' => $virtual['general_name'],
                                              'subProduct_3' => $download['general_name']));
    }

    /**
     * Create Bundle product
     *
     * @param bool $inSubCategory
     *
     * @return array
     */
    public function createBundleProduct($inSubCategory = false)
    {
        //Create category
        if ($inSubCategory) {
            $category = $this->loadDataSet('Category', 'sub_category_required');
            $catPath = $category['parent_category'] . '/' . $category['name'];
            $this->navigate('manage_categories', false);
            $this->categoryHelper()->checkCategoriesPage();
            $this->categoryHelper()->createCategory($category);
            $this->assertMessagePresent('success', 'success_saved_category');
            $returnCategory = array('name' => $category['name'], 'path' => $catPath);
        } else {
            $returnCategory = array('name' => 'Default Category', 'path' => 'Default Category');
        }
        //Create product
        $productCat = array('categories' => $returnCategory['path']);
        $simple = $this->loadDataSet('Product', 'simple_product_visible', $productCat);
        $virtual = $this->loadDataSet('Product', 'virtual_product_visible', $productCat);
        $bundle = $this->loadDataSet('SalesOrder', 'fixed_bundle_for_order', $productCat,
            array('add_product_1' => $simple['general_sku'], 'price_product_1' => 0.99, 'price_product_2' => 1.24,
                  'add_product_2' => $virtual['general_sku']));
        $this->navigate('manage_products');
        $this->createProduct($simple);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->createProduct($virtual, 'virtual');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->createProduct($bundle, 'bundle');
        $this->assertMessagePresent('success', 'success_saved_product');

        return array('simple'      => array('product_name' => $simple['general_name'],
                                            'product_sku'  => $simple['general_sku']),
                     'virtual'     => array('product_name' => $virtual['general_name'],
                                            'product_sku'  => $virtual['general_sku']),
                     'bundle'      => array('product_name' => $bundle['general_name'],
                                            'product_sku'  => $bundle['general_sku']),
                     'category'    => $returnCategory,
                     'bundleOption'=> array('subProduct_1' => $simple['general_name'],
                                            'subProduct_2' => $virtual['general_name'],
                                            'subProduct_3' => $simple['general_name'],
                                            'subProduct_4' => $virtual['general_name']));
    }

    /**
     * Create Downloadable product
     *
     * @param bool $inSubCategory
     *
     * @return array
     */
    public function createDownloadableProduct($inSubCategory = false)
    {
        //Create category
        if ($inSubCategory) {
            $category = $this->loadDataSet('Category', 'sub_category_required');
            $catPath = $category['parent_category'] . '/' . $category['name'];
            $this->navigate('manage_categories', false);
            $this->categoryHelper()->checkCategoriesPage();
            $this->categoryHelper()->createCategory($category);
            $this->assertMessagePresent('success', 'success_saved_category');
            $returnCategory = array('name' => $category['name'], 'path' => $catPath);
        } else {
            $returnCategory = array('name' => 'Default Category', 'path' => 'Default Category');
        }
        //Create product
        $assignCategory = array('categories' => $returnCategory['path']);
        $downloadable = $this->loadDataSet('Product', 'downloadable_product_visible', $assignCategory);
        $link = $downloadable['downloadable_information_data']['downloadable_link_1']['downloadable_link_row_title'];
        $linksTitle = $downloadable['downloadable_information_data']['downloadable_links_title'];
        $this->navigate('manage_products');
        $this->createProduct($downloadable, 'downloadable');
        $this->assertMessagePresent('success', 'success_saved_product');
        return array('downloadable'       => array('product_name' => $downloadable['general_name'],
                                                   'product_sku'  => $downloadable['general_sku']),
                     'downloadableOption' => array('title' => $linksTitle, 'optionTitle' => $link),
                     'category'           => $returnCategory);
    }

    /**
     * Create Simple product
     *
     * @param bool $inSubCategory
     *
     * @return array
     */
    public function createSimpleProduct($inSubCategory = false)
    {
        //Create category
        if ($inSubCategory) {
            $category = $this->loadDataSet('Category', 'sub_category_required');
            $catPath = $category['parent_category'] . '/' . $category['name'];
            $this->navigate('manage_categories', false);
            $this->categoryHelper()->checkCategoriesPage();
            $this->categoryHelper()->createCategory($category);
            $this->assertMessagePresent('success', 'success_saved_category');
            $returnCategory = array('name' => $category['name'], 'path' => $catPath);
        } else {
            $returnCategory = array('name' => 'Default Category', 'path' => 'Default Category');
        }
        //Create product
        $assignCategory = array('categories' => $returnCategory['path']);
        $simple = $this->loadDataSet('Product', 'simple_product_visible', $assignCategory);
        $this->navigate('manage_products');
        $this->createProduct($simple);
        $this->assertMessagePresent('success', 'success_saved_product');
        return array('simple'  => array('product_name' => $simple['general_name'],
                                        'product_sku'  => $simple['general_sku']), 'category'=> $returnCategory);
    }

    /**
     * Create Virtual product
     *
     * @param bool $inSubCategory
     *
     * @return array
     */
    public function createVirtualProduct($inSubCategory = false)
    {
        //Create category
        if ($inSubCategory) {
            $category = $this->loadDataSet('Category', 'sub_category_required');
            $catPath = $category['parent_category'] . '/' . $category['name'];
            $this->navigate('manage_categories', false);
            $this->categoryHelper()->checkCategoriesPage();
            $this->categoryHelper()->createCategory($category);
            $this->assertMessagePresent('success', 'success_saved_category');
            $returnCategory = array('name' => $category['name'], 'path' => $catPath);
        } else {
            $returnCategory = array('name' => 'Default Category', 'path' => 'Default Category');
        }
        //Create product
        $assignCategory = array('categories' => $returnCategory['path']);
        $virtual = $this->loadDataSet('Product', 'virtual_product_visible', $assignCategory);
        $this->navigate('manage_products');
        $this->createProduct($virtual, 'virtual');
        $this->assertMessagePresent('success', 'success_saved_product');
        return array('virtual'  => array('product_name' => $virtual['general_name'],
                                         'product_sku'  => $virtual['general_sku']), 'category' => $returnCategory);
    }
}