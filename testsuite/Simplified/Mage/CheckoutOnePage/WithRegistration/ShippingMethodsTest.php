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
class Simplified_Mage_CheckoutOnePage_WithRegistration_ShippingMethodsTest extends Core_Mage_CheckoutOnePage_WithRegistration_ShippingMethodsTest
{
    
    /**
     * <p>Provide the simplified list ot shipping methods</p>
     * @see Core_Mage_CheckoutOnePage_WithRegistration_ShippingMethodsTest::shipmentDataProvider()
     */
    public function shipmentDataProvider()
    {
        return $this->correctionHelper()->getShippingMethodsForDataProvider();
    }
    
}