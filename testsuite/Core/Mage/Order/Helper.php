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
class Core_Mage_Order_Helper extends Mage_Selenium_TestCase
{
    /**
     * Generates array of strings for filling customer's billing/shipping form
     *
     * @param string $charsType :alnum:, :alpha:, :digit:, :lower:, :upper:, :punct:
     * @param string $addressType Gets two values: 'billing' and 'shipping'.
     *                         Default is 'billing'
     * @param int $symNum min = 5, default value = 32
     * @param bool $required
     *
     * @throws Exception
     *
     * @return array
     *
     * @uses DataGenerator::generate()
     * @see DataGenerator::generate()
     */
    public function customerAddressGenerator($charsType, $addressType = 'billing', $symNum = 32, $required = false)
    {
        $type = array(':alnum:', ':alpha:', ':digit:', ':lower:', ':upper:', ':punct:');
        if (!in_array($charsType, $type)
            || ($addressType != 'billing' && $addressType != 'shipping')
            || $symNum < 5
            || !is_int($symNum)
        ) {
            throw new Exception('Incorrect parameters');
        }
        $return = array();
        $page = $this->getUimapPage('admin', 'create_order_for_existing_customer');
        $fieldset = $page->findFieldset('order_' . $addressType . '_address');
        $fields = $fieldset->getAllFields();
        $requiredFields = $fieldset->getAllRequired();
        $req = array();
        foreach ($requiredFields as $value) {
            $req[] = $value;
        }
        if ($required) {
            foreach ($fields as $fieldsKey => $xpath) {
                if (in_array($fieldsKey, $req)) {
                    $return[$fieldsKey] = $this->generate('string', $symNum, $charsType);
                } else {
                    $return[$fieldsKey] = '%noValue%';
                }
            }
        } else {
            foreach ($fields as $fieldsKey => $xpath) {
                $return[$fieldsKey] = $this->generate('string', $symNum, $charsType);
            }
        }
        $return[$addressType . '_country'] = 'Ukraine';
        $return['address_choice'] = 'new';
        return $return;
    }

    /**
     * Creates order
     *
     * @param array|string $orderData Array or string with name of dataset to load
     * @param bool $validate (If $validate == false - errors will be skipped while filling order data)
     *
     * @return int
     */
    public function createOrder($orderData, $validate = true)
    {
        $this->doAdminCheckoutSteps($orderData, $validate);
        return $this->submitOrder();
    }

    /**
     * @param $orderData
     * @param bool $validate
     */
    public function doAdminCheckoutSteps($orderData, $validate = true)
    {
        $storeView = (isset($orderData['store_view'])) ? $orderData['store_view'] : null;
        $customer = (isset($orderData['customer_data'])) ? $orderData['customer_data'] : null;
        $account = (isset($orderData['account_data'])) ? $orderData['account_data'] : array();
        $products = (isset($orderData['products_to_add'])) ? $orderData['products_to_add'] : array();
        $coupons = (isset($orderData['coupons'])) ? $orderData['coupons'] : array();
        $billingAddress = (isset($orderData['billing_addr_data'])) ? $orderData['billing_addr_data'] : array();
        $shippingAddress = (isset($orderData['shipping_addr_data'])) ? $orderData['shipping_addr_data'] : array();
        $paymentMethod = (isset($orderData['payment_data'])) ? $orderData['payment_data'] : null;
        $shippingMethod = (isset($orderData['shipping_data'])) ? $orderData['shipping_data'] : null;
        $giftMessages = (isset($orderData['gift_messages'])) ? $orderData['gift_messages'] : array();
        $verProduct = (isset($orderData['prod_verification'])) ? $orderData['prod_verification'] : null;
        $verPrTotal = (isset($orderData['prod_total_verification'])) ? $orderData['prod_total_verification'] : array();
        $verTotal = (isset($orderData['total_verification'])) ? $orderData['total_verification'] : null;

        $this->navigateToCreateOrderPage($customer, $storeView);
        $this->fillForm($account);
        foreach ($products as $value) {
            $this->addProductToOrder($value);
        }
        if ($coupons) {
            $this->applyCoupon($coupons, $validate);
        }
        if ($billingAddress) {
            $billingChoice = $billingAddress['address_choice'];
            $this->fillOrderAddress($billingAddress, $billingChoice, 'billing');
        }
        if ($shippingAddress) {
            $shippingChoice = $shippingAddress['address_choice'];
            $this->fillOrderAddress($shippingAddress, $shippingChoice, 'shipping');
        }
        if ($shippingMethod) {
            $this->clickControl('link', 'get_shipping_methods_and_rates', false);
            $this->pleaseWait();
            $this->selectShippingMethod($shippingMethod, $validate);
        }
        if ($paymentMethod) {
            $this->selectPaymentMethod($paymentMethod, $validate);
        }
        $this->addGiftMessage($giftMessages);
        if ($verProduct && $verTotal) {
            $this->shoppingCartHelper()->verifyPricesDataOnPage($verProduct, $verTotal);
        }
        if ($verPrTotal) {
            $this->verifyProductsTotal($verPrTotal);
        }
    }

    /**
     * Submit Order
     * @return int
     */
    public function submitOrder()
    {
        $this->saveForm('submit_order', false);
        //@TODO
        //Remove workaround for getting fails, not skipping tests if payment methods are inaccessible
        $this->paypalHelper()->verifyMagentoPayPalErrors();
        $id = $this->defineOrderId();
        $this->validatePage();
        return $id;
    }

    /**
     * Fills customer's addresses at the order page.
     *
     * @param string $addressType 'new', 'exist', 'sameAsBilling'
     * @param string $addressChoice 'billing' or 'shipping'
     * @param array $addressData
     */
    public function fillOrderAddress($addressData, $addressChoice = 'new', $addressType = 'billing')
    {
        if (is_string($addressData)) {
            $elements = explode('/', $addressData);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $addressData = $this->loadDataSet($fileName, implode('/', $elements));
        }

        if ($addressChoice == 'sameAsBilling') {
            $this->fillCheckbox('shipping_same_as_billing_address', 'Yes');
        }
        if ($addressChoice == 'new') {
            $this->addParameter('dropdownXpath', $this->_getControlXpath('dropdown', $addressType . '_address_choice'));
            if ($this->controlIsPresent('pageelement', 'dropdown_option_selected')) {
                $this->fillDropdown($addressType . '_address_choice', 'Add New Address');
                if ($addressType == 'shipping') {
                    $this->pleaseWait();
                }
            }
            if ($addressType == 'shipping') {
                $this->fillCheckbox('shipping_same_as_billing_address', 'No');
            }
            $this->fillForm($addressData);
        }
        if ($addressChoice == 'exist') {
            if ($addressType == 'shipping') {
                $this->fillCheckbox('shipping_same_as_billing_address', 'No');
            }
            $addressLine = $this->defineAddressToChoose($addressData, $addressType);
            $this->fillDropdown($addressType . '_address_choice', $addressLine);
        }
    }

    /**
     * Returns address that was found and can be selected from existing customer addresses.
     *
     * @param array $addressData
     * @param string $addressType
     *
     * @return bool|string The most suitable address found by using keywords
     */
    public function defineAddressToChoose(array $addressData, $addressType = 'billing')
    {
        $inString = array();
        if ($addressType) {
            $addressType .= '_';
        }

        $needKeys = array('first_name', 'last_name', 'street_address_1', 'street_address_2', 'city', 'zip_code',
                          'country', 'state', 'region');
        foreach ($needKeys as $value) {
            if (array_key_exists($addressType . $value, $addressData)) {
                $inString[$addressType . $value] = $addressData[$addressType . $value];
            }
        }

        if (!$inString) {
            $this->fail('Data to select the address wrong');
        }

        $this->addParameter('dropdownXpath', $this->_getControlXpath('dropdown', $addressType . 'address_choice'));
        $addressCount = $this->getControlCount('pageelement', 'dropdown_option');

        for ($i = 1; $i <= $addressCount; $i++) {
            $res = 0;
            $this->addParameter('index', $i);
            $addressValue = $this->getControlAttribute('pageelement', 'dropdown_option_index', 'text');
            foreach ($inString as $v) {
                if ($v == '') {
                    $res++;
                } elseif (strpos($addressValue, (string)$v) !== false) {
                    $res++;
                }
            }
            if ($res == count($inString)) {
                $res = $addressValue;
                break;
            }
        }

        if (isset ($res) && is_string($res)) {
            return $res;
        }
        return null;
    }

    /**
     * Defines order id
     *
     * @return bool|integer
     */
    public function defineOrderId()
    {
        if ($this->controlIsPresent('message', 'order_id')) {
            $text = $this->getControlAttribute('message', 'order_id', 'text');
            $orderId = trim(substr($text, strpos($text, "#") + 1, -(strpos(strrev($text), "|") + 1)));
            $this->addParameter('elementTitle', '#' . $orderId);
            return $orderId;
        }
        return 0;
    }

    /**
     * Orders product during forming order
     *
     * @param array $productData Product in array to add to order. Function should be called for each product to add
     */
    public function addProductToOrder(array $productData)
    {
        $configure = array();
        $additionalData = array();
        $productSku = '';
        foreach ($productData as $key => $value) {
            if (!preg_match('/^filter_/', $key)) {
                $additionalData[$key] = $value;
                unset($productData[$key]);
            }
            if ($key == 'qty_to_add') {
                $additionalData['product_qty'] = $value;
                unset($productData[$key]);
            }
            if ($key == 'filter_sku' || $key == 'filter_name') {
                $productSku = $value;
            }
            if ($key == 'configurable_options') {
                $configure = $value;
            }
        }

        if ($productData) {
            $this->clickButton('add_products', false);
            $xpathProduct = $this->search($productData, 'select_products_to_add');
            $this->assertNotEquals(null, $xpathProduct, 'Product is not found');
            $this->addParameter('productXpath', $xpathProduct);
            $this->addParameter('tableLineXpath', $xpathProduct);
            $configurable = false;
            if (!$this->controlIsPresent('link', 'disabled_configure')) {
                $configurable = true;
            }
            $this->fillCheckbox('table_line_checkbox', 'Yes');
            if ($configurable && $configure) {
                $this->pleaseWait();
                $before = $this->getMessagesOnPage();
                $this->configureProduct($configure);
                $this->clickButton('composite_configure_ok', false);
                $after = $this->getMessagesOnPage();
                $result = array();
                foreach ($after as $key => $value) {
                    if ($key == 'success') {
                        continue;
                    }
                    if (is_array($value) && (array_key_exists($key, $before) && is_array($before[$key]))) {
                        $result = array_merge($result, array_diff($value, $before[$key]));
                    }
                }
                if ($result) {
                    $this->fail("Error(s) when configure product '$productSku':\n" . implode("\n", $result));
                }
            }
            $this->clickButton('add_selected_products_to_order', false);
            $this->pleaseWait();
            if ($additionalData) {
                $this->reconfigureProduct($productSku, $additionalData);
            }
        }
    }

    /**
     * Configuring product when placing to order.
     *
     * @param array $configureData Product in array to add to order. Function should be called for each product to add
     */
    public function configureProduct(array $configureData)
    {
        $setElements = $this->_findUimapElement('fieldset', 'product_composite_configure_form')->getFieldsetElements();
        foreach ($configureData as $optionData) {
            if (!is_array($optionData)) {
                continue;
            }
            $optionTitle = (isset($optionData['title'])) ? $optionData['title'] : null;
            $this->addParameter('optionTitle', $optionTitle);
            foreach ($optionData as $optionFieldData) {
                if (!is_array($optionFieldData)) {
                    continue;
                }
                $fieldType = (isset($optionFieldData['fieldType'])) ? $optionFieldData['fieldType'] : '';
                $parameter = (isset($optionFieldData['fieldParameter'])) ? $optionFieldData['fieldParameter'] : null;
                $fieldValue = (isset($optionFieldData['fieldsValue'])) ? $optionFieldData['fieldsValue'] : '';
                $this->addParameter('optionParameter', $parameter);
                $elements = (array_key_exists($fieldType, $setElements)) ? $setElements[$fieldType] : array();
                foreach ($elements as $elementName => $elementXpath) {
                    if ($this->controlIsPresent($fieldType, $elementName)) {
                        $fillMethod = 'fill' . ucfirst(strtolower($fieldType));
                        $this->$fillMethod($elementName, $fieldValue);
                        break;
                    }
                }
            }
        }
    }

    /**
     * The way customer will pay for the order
     *
     * @param array|string $paymentMethod
     * @param bool $validate
     */
    public function selectPaymentMethod($paymentMethod, $validate = true)
    {
        if (is_string($paymentMethod)) {
            $elements = explode('/', $paymentMethod);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $paymentMethod = $this->loadDataSet($fileName, implode('/', $elements));
        }
        $payment = (isset($paymentMethod['payment_method'])) ? $paymentMethod['payment_method'] : null;
        $card = (isset($paymentMethod['payment_info'])) ? $paymentMethod['payment_info'] : null;

        if ($payment) {
            $this->addParameter('paymentTitle', $payment);
            if (!$this->controlIsPresent('radiobutton', 'check_payment_method')) {
                if ($validate) {
                    $this->fail('Payment Method "' . $payment . '" is currently unavailable.');
                }
            } else {
                $this->fillRadiobutton('check_payment_method', 'Yes');
                $this->pleaseWait();
                if ($card) {
                    $paymentId = $this->getControlAttribute('radiobutton', 'check_payment_method', 'selectedValue');
                    $this->addParameter('paymentId', $paymentId);
                    $this->fillForm($card);
                    $this->validate3dSecure();
                }
            }
        }
    }

    /**
     * Validates 3D secure frame
     *
     * @param string $password
     */
    public function validate3dSecure($password = '1234')
    {
        if ($this->controlIsPresent('button', 'start_reset_validation')) {
            $this->clickButton('start_reset_validation', false);
            $this->pleaseWait();
            $this->assertTrue($this->checkoutOnePageHelper()->verifyNotPresetAlert(), $this->getParsedMessages());
            $this->addParameter('elementXpath', $this->_getControlXpath('fieldset', '3d_secure_card_validation'));
            if ($this->controlIsPresent('pageelement', 'element_not_disabled_style')) {
                $waitCondition = array($this->_getControlXpath('button', '3d_continue'),
                                       $this->_getControlXpath('pageelement', 'incorrect_password'),
                                       $this->_getControlXpath('pageelement', 'verification_successful'));
                if (!$this->controlIsVisible('pageelement', '3d_secure_iframe')) {
                    //Skipping test, but not failing
                    $this->skipTestWithScreenshot('3D Secure frame is not loaded(maybe wrong card)');
                    //$this->fail('3D Secure frame is not loaded(maybe wrong card)');
                }
                $this->selectFrame($this->_getControlXpath('pageelement', '3d_secure_iframe'));
                $this->waitForElement($this->_getControlXpath('button', '3d_submit'), 10);
                $this->fillField('3d_password', $password);
                $this->clickButton('3d_submit', false);
                $this->waitForElement($waitCondition);
                if ($this->controlIsPresent('button', '3d_continue')) {
                    $this->clickButton('3d_continue', false);
                    $this->waitForElement($this->_getControlXpath('pageelement', 'verification_successful'));
                }
                $this->selectFrame('relative=top');
            }
        }
    }

    /**
     * The way to ship the order
     *
     * @param array|string $shippingMethod
     * @param bool $validate
     */
    public function selectShippingMethod($shippingMethod, $validate = true)
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
            if ($this->controlIsPresent('message', 'ship_method_unavailable')
                || $this->controlIsPresent('message', 'no_shipping')
            ) {
                if ($validate) {
                    //@TODO
                    //Remove workaround for getting fails, not skipping tests if shipping methods are not available
                    $this->skipTestWithScreenshot('Shipping Service "' . $shipService . '" is currently unavailable.');
                    //$this->addVerificationMessage('Shipping Service "' . $shipService . '" is currently unavailable.');
                }
            } elseif ($this->controlIsPresent('field', 'ship_service_name')) {
                if ($this->controlIsPresent('radiobutton', 'ship_method')) {
                    $this->fillRadiobutton('ship_method', 'Yes');
                    $this->pleaseWait();
                } elseif ($validate) {
                    $this->addVerificationMessage(
                        'Shipping Method "' . $shipMethod . '" for "' . $shipService . '" is currently unavailable.');
                }
            } elseif ($validate) {
                //@TODO
                //Remove workaround for getting fails, not skipping tests if shipping methods are not available
                $this->skipTestWithScreenshot($shipService . ': This shipping method is currently not displayed');
                //$this->addVerificationMessage($shipService . ': This shipping method is currently not displayed');
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Navigates to 'Create new Order page'. Selects the provided customer and the store view.
     *
     * NOTE. The first found store view with $storeView name will be selected.
     * You may need to generate a unique store view name to avoid this.
     *
     * @param array|string $customerData    Array with customer data to search or string with dataset to load
     * @param string $storeView
     */
    public function navigateToCreateOrderPage($customerData, $storeView)
    {
        $this->clickButton('create_new_order');
        if ($customerData == null) {
            $this->clickButton('create_new_customer', false);
            $this->pleaseWait();
        } else {
            if (is_string($customerData)) {
                $elements = explode('/', $customerData);
                $fileName = (count($elements) > 1) ? array_shift($elements) : '';
                $customerData = $this->loadDataSet($fileName, implode('/', $elements));
            }
            $this->searchAndOpen($customerData, false, 'order_customer_grid');
        }

        // Select a store if there is more then one default store
        $this->addParameter('elementXpath', $this->_getControlXpath('fieldset', 'order_store_selector'));
        if ($this->controlIsPresent('pageelement', 'element_not_disabled_style')) {
            if ($storeView) {
                $this->addParameter('storeName', $storeView);
                $this->clickControl('radiobutton', 'choose_main_store', false);
                $this->pleaseWait();
            } else {
                $this->fail('Store View is not set');
            }
        }
    }

    /**
     * Reconfigure already added to order products (change quantity, add discount, etc)
     *
     * @param string $productSku
     * @param array $productData Array with the products and data to reconfigure
     */
    public function reconfigureProduct($productSku, array $productData)
    {
        $this->addParameter('sku', $productSku);
        $this->fillForm($productData);
        $this->clickButton('update_items_and_quantity', false);
        $this->pleaseWait();
    }

    /**
     * Adding gift messaged to products during creating order at the backend.
     *
     * @param array $giftMessages Array with the gift messages for the products
     */
    public function addGiftMessage(array $giftMessages)
    {
        if (array_key_exists('entire_order', $giftMessages)) {
            $this->fillForm($giftMessages['entire_order']);
        }
        if (array_key_exists('individual', $giftMessages)) {
            foreach ($giftMessages['individual'] as $options) {
                if (is_array($options) && isset($options['sku_product'])) {
                    $this->addParameter('sku', $options['sku_product']);
                    $this->clickControl('link', 'gift_options', false);
                    $this->waitForAjax();
                    $this->fillForm($options);
                    $this->clickButton('ok', false);
                    $this->pleaseWait();
                }
            }
        }
    }

    /**
     * Verify gift message
     *
     * @param array $giftMessages
     */
    public function verifyGiftMessage(array $giftMessages)
    {
        if (array_key_exists('entire_order', $giftMessages)) {
            $this->verifyForm($giftMessages['entire_order']);
        }
        if (array_key_exists('individual', $giftMessages)) {
            foreach ($giftMessages['individual'] as $options) {
                if (is_array($options) && isset($options['sku_product'])) {
                    $this->addParameter('sku', $options['sku_product']);
                    $this->clickControl('link', 'gift_options', false);
                    $this->waitForAjax();
                    $this->verifyForm($options);
                    $this->clickButton('ok', false);
                    $this->pleaseWait();
                }
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Applying coupon for the products in order
     *
     * @param array $coupons
     * @param bool $validate
     */
    public function applyCoupon($coupons, $validate = true)
    {
        if (is_string($coupons)) {
            $coup[] = $coupons;
            $coupons = $coup;
        }
        if (!$this->controlIsPresent('fieldset', 'order_apply_coupon_code') && $coupons) {
            $this->fail('Can not add coupon(Product is not added)');
        }

        foreach ($coupons as $code) {
            $this->fillField('coupon_code', $code);
            $this->clickButton('apply', false);
            $this->pleaseWait();
            if ($validate) {
                $this->addParameter('couponCode', $code);
                $this->assertMessagePresent('success', 'success_applying_coupon');
            }
        }
    }

    /**
     * Verifies the prices in product total grid
     *
     * @param array $verificationData
     */
    public function verifyProductsTotal(array $verificationData)
    {
        $actualData = array();

        $needColumnNames = array('Product', 'Subtotal', 'Discount', 'Row Subtotal');
        $names = $this->getTableHeadRowNames("//*[@id='order-items_grid']/table");
        foreach ($needColumnNames as $value) {
            $number = array_search($value, $names);
            if ($value == 'Product') {
                $number += 1;
            }
            $key = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '_', $value)), '_');
            $this->addParameter('tableLineXpath', $this->_getControlXpath('pageelement', 'product_table_tfoot'));
            $this->addParameter('cellIndex', $number);
            $actualData[$key] = $this->getTabAttribute('pageelement', 'table_line_cell_index', 'text');
        }
        $this->shoppingCartHelper()->compareArrays($actualData, $verificationData, 'Total');

        $this->assertEmptyVerificationErrors();
    }


    /**
     * Compare arrays
     *
     * @param string $httpHelperPath
     * @param string $logFileName
     * @param array $inputArray
     *
     * @return bool|array
     */
    public function compareArraysFromLog($httpHelperPath, $logFileName, $inputArray)
    {
        $subject = $this->getLastRecord($httpHelperPath, $logFileName);
        $responseParams = $this->getResponse($subject);
        $resultArray = array_diff($inputArray, $responseParams);
        return (count($resultArray)) ? $resultArray : true;
    }

    /**
     * Define correct array for compare
     *
     * @param string $subject
     *
     * @return array
     */
    protected function getParamsArray($subject)
    {
        preg_match_all('/\[(.*)\] => (.*)/', $subject, $arr);

        $result = array();
        foreach ($arr[1] as $key => $value) {
            if (!empty($value)) {
                $result[$value] = $arr[2][$key];
            }
        }
        return $result;
    }

    /**
     * Define request array
     *
     * @param string $subject
     *
     * @return array
     */
    protected function getRequest($subject)
    {
        $requestSubject = substr($subject, strpos($subject, '[request]'),
            strpos($subject, ")\n") - strpos($subject, '[request]') + 1);
        $requestSubject = substr($requestSubject, strpos($requestSubject, "(\n"), strpos($requestSubject, ")"));
        return $this->getParamsArray($requestSubject);
    }

    /**
     * Define response array
     *
     * @param string $subject
     *
     * @return array
     */
    protected function getResponse($subject)
    {
        $responseSubject = substr($subject, strpos($subject, '[response]'),
            strpos($subject, ")\n") - strpos($subject, '[request]') + 1);
        $responseSubject = substr($responseSubject, strpos($responseSubject, "(\n"),
            strpos($responseSubject, ")") - strpos($responseSubject, "(\n"));
        return $this->getParamsArray($responseSubject);
    }

    /**
     * Find last record into Log File
     *
     * @param string $httpHelperPath
     * @param string $logFileName
     *
     * @return string
     */
    protected function getLastRecord($httpHelperPath, $logFileName)
    {
        $arrayResult = file_get_contents($httpHelperPath . '?log_file_name=' . $logFileName);
        $pathVerification = strcmp(trim($arrayResult), 'Could not open File');
        if ($pathVerification == 0) {
            $this->fail("Log file could not be opened");
        }
        return $arrayResult;
    }

    /**
     * 3D Secure log verification
     *
     * @param array $verificationData
     *
     * @return bool
     */
    public function verify3DSecureLog($verificationData)
    {
        $this->setArea('frontend');
        $fileUrl =
            preg_replace('|/index.php/?|', '/', $this->_configHelper->getBaseUrl()) . '3DSecureLogVerification.php';
        $logFileName = 'card_validation_3d_secure.log';
        $result = $this->compareArraysFromLog($fileUrl, $logFileName, $verificationData['response']);
        if (is_array($result)) {
            $this->fail("Arrays are not identical:\n" . var_export($result, true));
        }
        return true;
    }

    /**
     * Check empty fields for credit card during reorder
     *
     * @param array $cardData
     */
    public function verifyIfCreditCardFieldsAreEmpty(array $cardData)
    {
        $fieldsetElements = $this->_findUimapElement('fieldset', 'order_payment_method')->getFieldsetElements();
        foreach ($cardData as $fieldName => $fieldData) {
            foreach ($fieldsetElements as $elementType => $elementsData) {
                if (array_key_exists($fieldName, $elementsData)) {
                    $selectedValue = $this->getControlAttribute($elementType, $fieldName, 'selectedValue');
                    if ($selectedValue == $fieldData) {
                        $this->addVerificationMessage(
                            "Value for field " . $fieldName . " should be empty, but now is $selectedValue");
                    }
                    continue 1;
                }
            }
        }
    }
}
