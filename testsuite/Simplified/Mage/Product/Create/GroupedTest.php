<?php

/**
 * Grouped product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Product_Create_GroupedTest extends Core_Mage_Product_Create_GroupedTest
{

	/**
	 * <p>Simplified list of empty fields.</p>
	 *
	 * @see Core_Mage_Product_Create_GroupedTest::withRequiredFieldsEmptyDataProvider()
	 */
    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('general_name', 'field')
        );
    }

}