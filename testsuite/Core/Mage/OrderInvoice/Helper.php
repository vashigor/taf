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
class Core_Mage_OrderInvoice_Helper extends Mage_Selenium_TestCase
{
    /**
     * Provides partial or full invoice
     *
     * @param string $captureType
     * @param array $invoiceData
     */
    public function createInvoiceAndVerifyProductQty($captureType = null, $invoiceData = array())
    {
        $verify = array();
        $this->clickButton('invoice');
        foreach ($invoiceData as $options) {
            if (is_array($options)) {
                $sku = (isset($options['invoice_product_sku'])) ? $options['invoice_product_sku'] : null;
                $productQty = (isset($options['qty_to_invoice'])) ? $options['qty_to_invoice'] : '%noValue%';
                if ($sku) {
                    $verify[$sku] = $productQty;
                    $this->addParameter('sku', $sku);
                    $this->fillField('qty_to_invoice', $productQty);
                }
            }
        }
        if ($captureType) {
            $this->fillDropdown('amount', $captureType);
        }
        if (!$verify) {
            $productCount = $this->getControlCount('fieldset', 'product_line_to_invoice');
            for ($i = 1; $i <= $productCount; $i++) {
                $this->addParameter('productNumber', $i);
                $qtyXpath = $this->_getControlXpath('field', 'product_qty');
                $prodSku = $this->getControlAttribute('field', 'product_sku', 'text');
                $prodSku = trim(preg_replace('/SKU:|\\n/', '', $prodSku));
                $this->addParameter('tableLineXpath', $qtyXpath);
                if ($this->controlIsPresent('pageelement', 'table_line_input')) {
                    $prodQty = $this->getControlAttribute('pageelement', 'table_line_input', 'selectedValue');
                } else {
                    $prodQty = $this->getControlAttribute('field', 'product_qty', 'text');
                }
                $verify[$prodSku] = $prodQty;
            }
        }
        $this->addParameter('elementXpath', $this->_getControlXpath('button', 'update_qty'));
        if ($this->controlIsPresent('pageelement', 'element_not_disabled')) {
            $this->clickButton('update_qty', false);
            $this->pleaseWait();
        }
        $this->clickButton('submit_invoice', false);
        $this->waitForNewPage();
        $this->validatePage();
        //@TODO
        //Remove workaround for getting fails, not skipping tests if payment methods are inaccessible
        $this->paypalHelper()->verifyMagentoPayPalErrors();
        $this->assertMessagePresent('success', 'success_creating_invoice');
        foreach ($verify as $productSku => $qty) {
            if ($qty == '%noValue%') {
                continue;
            }
            $this->addParameter('sku', $productSku);
            $this->addParameter('invoicedQty', $qty);
            $this->assertTrue($this->controlIsPresent('field', 'qty_invoiced'),
                'Qty of invoiced products is incorrect at the orders form');
        }
    }

    /**
     * Opens invoice
     *
     * @param array|string $searchData
     */
    public function openInvoice($searchData)
    {
        if (is_string($searchData)) {
            $elements = explode('/', $searchData);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $searchData = $this->loadDataSet($fileName, implode('/', $elements));
        }
        $xpathTR = $this->search($searchData, 'sales_invoice_grid');
        $this->assertNotEquals(null, $xpathTR, 'Invoice is not found');
        $cellId = $this->getColumnIdByName('Invoice #');
        $this->addParameter('tableLineXpath', $xpathTR);
        $this->addParameter('cellIndex', $cellId);
        $param = $this->getControlAttribute('pageelement', 'table_line_cell_index', 'text');
        $this->addParameter('elementTitle', '#' . $param);
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->clickControl('pageelement', 'table_line_cell_index');
    }
}