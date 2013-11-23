<?php

/**
 * Product creation with custom options tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Product_Create_CustomOptionsTest extends Core_Mage_Product_Create_CustomOptionsTest
{

	/**
	 * <p>Simplified list of empty options.</p>
	 * 
	 * @see Core_Mage_Product_Create_CustomOptionsTest::emptyOptionRowTitleInCustomOptionDataProvider()
	 */
    public function emptyOptionRowTitleInCustomOptionDataProvider()
    {
        return array(
            array('custom_options_multipleselect')
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     *
     * @see Core_Mage_Product_Create_CustomOptionsTest::invalidNumericValueDataProvider()
     */
    public function invalidNumericValueDataProvider()
    {
        return array(
            array('g3648GJTest')
        );
    }

    /**
     * <p>Simplified list of options with negative price.</p>
     *
     * @see Core_Mage_Product_Create_CustomOptionsTest::negativeNumberInCustomOptionsPricePosDataProvider()
     */
    public function negativeNumberInCustomOptionsPricePosDataProvider()
    {
        return array(
            array('custom_options_area')
        );
    }
}