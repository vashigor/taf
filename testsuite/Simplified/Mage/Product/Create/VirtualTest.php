<?php

/**
 * Virtual product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Product_Create_VirtualTest extends Core_Mage_Product_Create_VirtualTest
{

	/**
	 * <p>Simplified list of empty fields.</p>
	 *
	 * @see Core_Mage_Product_Create_VirtualTest::withRequiredFieldsEmptyDataProvider()
	 */
    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('general_short_description', 'field'),
            array('prices_price', 'field')
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     *
     * @see Core_Mage_Product_Create_VirtualTest::invalidQtyDataProvider()
     */
    public function invalidQtyDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':alpha:'))
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     *
     * @see Core_Mage_Product_Create_VirtualTest::invalidNumericFieldDataProvider()
     */
    public function invalidNumericFieldDataProvider()
    {
        return array(
            array('-128')
        );
    }
    
}