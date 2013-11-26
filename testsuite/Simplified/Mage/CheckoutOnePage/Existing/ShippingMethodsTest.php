<?php

/**
 * Tests for shipping methods. Frontend - OnePageCheckout
 *
 * @method Simplified_Mage_Correction_Helper correctionHelper()
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_CheckoutOnePage_Existing_ShippingMethodsTest extends Core_Mage_CheckoutOnePage_Existing_ShippingMethodsTest
{

    /**
     * <p>Providing the simplified list ot shipping methods.</p>
     * @see Core_Mage_CheckoutOnePage_Existing_ShippingMethodsTest::shipmentDataProvider()
     */
    public function shipmentDataProvider()
    {
        return $this->correctionHelper()->getShippingMethodsForDataProvider();
    }

}