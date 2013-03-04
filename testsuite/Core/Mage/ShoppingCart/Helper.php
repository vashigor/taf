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
class Core_Mage_ShoppingCart_Helper extends Mage_Selenium_TestCase
{
    const QTY = 'Qty';
    const EXCLTAX = '(Excl. Tax)';
    const INCLTAX = '(Incl. Tax)';

    /**
     * Get table column names and column numbers
     *
     * @param string $tableHeadName
     * @param bool $transformKeys
     *
     * @return array
     */
    public function getColumnNamesAndNumbers($tableHeadName = 'product_table_head', $transformKeys = true)
    {
        $isExlAndInclInHead = false;
        $this->addParameter('tableHeadXpath', $this->_getControlXpath('pageelement', $tableHeadName));
        $lineQty = $this->getControlCount('pageelement', 'table_line');
        if ($lineQty == 2) {
            $isExlAndInclInHead = true;
            $this->addParameter('tableHeadXpath', $this->_getControlXpath('pageelement', 'table_head_first'));
        }
        $columnQty = $this->getControlCount('pageelement', 'table_column');
        $returnData = array();
        $y = 1;
        for ($i = 1; $i <= $columnQty; $i++) {
            $this->addParameter('index', $i);
            if ($this->controlIsPresent('pageelement', 'table_column_index_colspan')) {
                $text = $this->getControlAttribute('pageelement', 'table_column_index', 'text');
                $qtyColspan = $this->getControlAttribute('pageelement', 'table_column_index', 'colspan');
                if ($isExlAndInclInHead && $qtyColspan == 2) {
                    $returnData[$y] = $text . self::EXCLTAX;
                    $returnData[$y + 1] = $text . self::INCLTAX;
                } else {
                    $returnData[$y] = $text;
                }
                $y = $y + $qtyColspan;
            } else {
                $returnData[$y++] = $this->getControlAttribute('pageelement', 'table_column_index', 'text');
            }
        }
        $returnData = array_diff($returnData, array(''));
        if ($transformKeys) {
            foreach ($returnData as $key => &$value) {
                $value = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '_', $value)), '_');
                if ($value == 'action') {
                    unset($returnData[$key]);
                }
            }
        }

        return array_flip($returnData);
    }

    /**
     * Get all Products info in Shopping Cart
     *
     * @param array $skipFields list of fields to skip from scraping (default value is set for EE)
     *
     * @return array
     */
    public function getProductInfoInTable($skipFields = array('move_to_wishlist', 'remove'))
    {
        $productValues = array();

        $tableRowNames = $this->getColumnNamesAndNumbers();
        $this->addParameter('tableLineXpath', $this->_getControlXpath('pageelement', 'product_line'));
        $productCount = $this->getControlCount('pageelement', 'product_line');
        for ($i = 1; $i <= $productCount; $i++) {
            foreach ($tableRowNames as $key => $value) {
                if (in_array($key, $skipFields)) {
                    continue;
                }
                $this->addParameter('lineIndex', $i);
                $this->addParameter('cellIndex', $value);
                if ($key == 'qty'
                    && $this->controlIsPresent('pageelement', 'table_line_index_cell_index_with_input_value')
                ) {
                    $value = $this->getControlAttribute('pageelement', 'table_line_index_cell_index_with_input_value',
                        'selectedValue');
                    $productValues['product_' . $i][$key] = $value;
                } elseif ($key == 'product_name'
                          && $this->controlIsPresent('pageelement', 'table_line_index_cell_index_options')
                ) {
                    $name =
                        $this->getControlAttribute('pageelement', 'table_line_index_cell_index_product_name', 'text');
                    $productValues['product_' . $i][$key] = trim($name);
                    //@TODO get product parameters
                    /*$optionsXpath = $this->_getControlXpath('pageelement', 'table_line_index_cell_index_options');
                    $countOptions = $this->getXpathCount($optionsXpath . '//dt');
                    $options = array();
                    for ($i = 0; $i < $countOptions; $i++) {
                        $nameXpath = $optionsXpath . '//dt[' . $i . ']';
                        $valueXpath = $nameXpath . "/following-sibling::dd[1]";
                        $name = trim($this->getText($nameXpath));
                        $price = trim($this->getText($valueXpath . '/span'));
                        $value = str_replace($price, '', trim($this->getText($valueXpath)));
                        $options[$i]['option_name'] = $name;
                        $options[$i]['option_price'] = $price;
                        $options[$i]['option_parameter'] = $value;
                    }*/
                } else {
                    $text = $this->getControlAttribute('pageelement', 'table_line_index_cell_index', 'text');
                    if (preg_match('/Excl. Tax/', $text)) {
                        $text = preg_replace("/ \\n/", ':', $text);
                        $values = explode(':', $text);
                        $values = array_map('trim', $values);
                        foreach ($values as $k => $v) {
                            if ($v == 'Excl. Tax' && isset($values[$k + 1])) {
                                $productValues['product_' . $i][$key . '_excl_tax'] = $values[$k + 1];
                            }
                            if ($v == 'Incl. Tax' && isset($values[$k + 1])) {
                                $productValues['product_' . $i][$key . '_incl_tax'] = $values[$k + 1];
                            }
                        }
                    } elseif (preg_match('/Ordered/', $text)) {
                        $values = explode(' ', $text);
                        $values = array_map('trim', $values);
                        foreach ($values as $k => $v) {
                            if ($k % 2 != 0 && isset($values[$k - 1])) {
                                $newKey = $key . '_' . strtolower(preg_replace('#[^0-9a-z]+#i', '', $values[$k - 1]));
                                $productValues['product_' . $i][$newKey] = $v;
                            }
                        }
                    } else {
                        $productValues['product_' . $i][$key] = trim($text);
                    }
                }
            }
        }

        foreach ($productValues as &$productData) {
            $productData = array_diff($productData, array(''));
            foreach ($productData as &$fieldValue) {
                if (preg_match('/([\d]+\.[\d]+)|([\d]+)/', $fieldValue)) {
                    preg_match_all('/^([\D]+)?(([\d]+\.[\d]+)|([\d]+))(\%)?/', $fieldValue, $price);
                    $fieldValue = $price[0][0];
                }
                if (preg_match('/SKU:/', $fieldValue)) {
                    $fieldValue = substr($fieldValue, 0, strpos($fieldValue, ':') - 3);
                }
            }
        }

        return $productValues;
    }

    /**
     * Get all order prices info in Shopping Cart
     *
     * @return array
     */
    public function getOrderPriceData()
    {
        $count = $this->getControlCount('pageelement', 'price_totals_line');
        $returnData = array();
        for ($i = $count; $i >= 1; $i--) {
            $this->addParameter('index', $i);
            if ($this->controlIsPresent('pageelement', 'price_totals_line_index_value')) {
                $fieldName = $this->getControlAttribute('pageelement', 'price_totals_line_index_name', 'text');
                if (!preg_match('/\$\(([\d]+\.[\d]+)|([\d]+)\%\)/', $fieldName)) {
                    $fieldName = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '_', $fieldName)), '_');
                }
                $fieldValue = $this->getControlAttribute('pageelement', 'price_totals_line_index_value', 'text');
                $returnData[$fieldName] = trim($fieldValue, "\x00..\x1F");
            }
        }

        return array_diff($returnData, array(''));
    }

    /**
     * Verify prices data on page
     *
     * @param string|array $productData
     * @param string|array $orderPriceData
     */
    public function verifyPricesDataOnPage($productData, $orderPriceData)
    {
        if (is_string($productData)) {
            $elements = explode('/', $productData);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $productData = $this->loadDataSet($fileName, implode('/', $elements));
        }
        if (is_string($orderPriceData)) {
            $elements = explode('/', $orderPriceData);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $orderPriceData = $this->loadDataSet($fileName, implode('/', $elements));
        }
        //Get Products data and order prices data
        $actualProductData = $this->getProductInfoInTable();
        $actualOrderPriceData = $this->getOrderPriceData();
        //Verify Products data
        $actualQty = count($actualProductData);
        $expectedQty = count($productData);
        if ($actualQty != $expectedQty) {
            $this->addVerificationMessage(
                "'" . $actualQty . "' product(s) added to Shopping cart but must be '" . $expectedQty . "'");
        } else {
            for ($i = 1; $i <= $actualQty; $i++) {
                $productName = '';
                foreach ($actualProductData['product_' . $i] as $key => $value) {
                    if (preg_match('/^product/', $key)) {
                        $productName = $value;
                        break;
                    }
                }
                $this->compareArrays($actualProductData['product_' . $i], $productData['product_' . $i], $productName);
            }
        }
        //Verify order prices data
        $this->compareArrays($actualOrderPriceData, $orderPriceData);
        $this->assertEmptyVerificationErrors();
    }

    /**
     *
     * @param array $actualArray
     * @param array $expectedArray
     * @param string $productName
     */
    public function compareArrays($actualArray, $expectedArray, $productName = '')
    {
        foreach ($actualArray as $key => $value) {
            if (array_key_exists($key, $expectedArray) && (strcmp($expectedArray[$key], trim($value)) == 0)) {
                unset($expectedArray[$key]);
                unset($actualArray[$key]);
            }
        }

        if ($productName) {
            $productName = $productName . ': ';
        }

        if ($actualArray) {
            $actualErrors = $productName . "Data is displayed on the page: \n";
            foreach ($actualArray as $key => $value) {
                $actualErrors .= "Field '$key': value '$value'\n";
            }
        }
        if ($expectedArray) {
            $expectedErrors = $productName . "Data should appear on the page: \n";
            foreach ($expectedArray as $key => $value) {
                $expectedErrors .= "Field '$key': value '$value'\n";
            }
        }
        if (isset($actualErrors)) {
            $this->addVerificationMessage(trim($actualErrors, "\x00..\x1F"));
        }
        if (isset($expectedErrors)) {
            $this->addVerificationMessage(trim($expectedErrors, "\x00..\x1F"));
        }
    }

    /**
     *
     * @param string|array $shippingAddress
     * @param string|array $shippingMethod
     * @param boolean $validate
     */
    public function frontEstimateShipping($shippingAddress, $shippingMethod, $validate = true)
    {
        if (is_string($shippingAddress)) {
            $elements = explode('/', $shippingAddress);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $shippingAddress = $this->loadDataSet($fileName, implode('/', $elements));
        }
        $this->fillForm($shippingAddress);
        $this->clickButton('get_quote');
        $this->chooseShipping($shippingMethod, $validate);
        $this->clickButton('update_total');
    }

    /**
     *
     * @param array $shippingMethod
     */
    public function chooseShipping($shippingMethod)
    {
        if (is_string($shippingMethod)) {
            $elements = explode('/', $shippingMethod);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $shippingMethod = $this->loadDataSet($fileName, implode('/', $elements));
        }
        $shipService = (isset($shippingMethod['shipping_service'])) ? $shippingMethod['shipping_service'] : null;
        $shipMethod = (isset($shippingMethod['shipping_method'])) ? $shippingMethod['shipping_method'] : null;
        if (!$shipService or !$shipMethod) {
            $this->addVerificationMessage('Shipping Service(or Shipping Method) is not set');
        } else {
            $this->addParameter('shipService', $shipService);
            $this->addParameter('shipMethod', $shipMethod);
            if ($this->controlIsPresent('field', 'ship_service_name')) {
                if ($this->controlIsPresent('radiobutton', 'ship_method')) {
                    $this->fillRadiobutton('ship_method', 'Yes');
                } else {
                    $this->addVerificationMessage(
                        'Shipping Method "' . $shipMethod . '" for "' . $shipService . '" is currently unavailable.');
                }
            } else {
                $this->addVerificationMessage('Shipping Service "' . $shipService . '" is currently unavailable.');
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Open and clear Shopping Cart
     */
    public function frontClearShoppingCart()
    {
        if ($this->getArea() == 'frontend' && !$this->controlIsPresent('link', 'empty_my_cart')) {
            $this->frontend('shopping_cart');
            $productCount = $this->getControlCount('pageelement', 'product_line');
            for ($i = 1; $i <= $productCount; $i++) {
                $this->addParameter('productNumber', $i);
                $this->fillField('product_qty', 0);
            }
            $this->clickButton('update_shopping_cart');
            $this->assertMessagePresent('success', 'shopping_cart_is_empty');
        }
    }

    /**
     * Moves products to the wishlist from Shopping Cart
     *
     * @param string|array $productNameSet Name or array of product names to move
     */
    public function frontMoveToWishlist($productNameSet)
    {
        if (is_string($productNameSet)) {
            $productNameSet = array($productNameSet);
        }
        foreach ($productNameSet as $productName) {
            $this->addParameter('productName', $productName);
            if ($this->controlIsPresent('checkbox', 'move_to_wishlist')) {
                $this->fillCheckbox('move_to_wishlist', 'Yes');
            } else {
                $this->fail('Product ' . $productName . ' is not in the shopping cart.');
            }
        }
        $this->clickButton('update_shopping_cart');
    }

    /**
     * Verifies if the product(s) are in the Shopping Cart
     *
     * @param string|array $productNameSet Product name (string) or array of product names to check
     *
     * @return bool|array True if the products are all present.
     *                    Otherwise returns an array of product names that are absent.
     */
    public function frontShoppingCartHasProducts($productNameSet)
    {
        if (is_string($productNameSet)) {
            $productNameSet = array($productNameSet);
        }
        $absentProducts = array();
        foreach ($productNameSet as $productName) {
            $this->addParameter('productName', $productName);
            if (!$this->controlIsPresent('link', 'product_name')) {
                $absentProducts[] = $productName;
            }
        }
        return (empty($absentProducts)) ? true : $absentProducts;
    }
}