<?php

/**
 * Creating Order with specific shipment
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Order_Create_ShippingMethodsTest extends Core_Mage_Order_Create_ShippingMethodsTest
{
    
    public function shipmentDataProvider()
    {
        return array(
            array('flatrate', null, 'usa'),
            array('free', null, 'usa')
        );
    }
    
}