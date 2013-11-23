<?php

/**
 * Bundle Dynamic product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Product_Create_BundleTest extends Core_Mage_Product_Create_BundleTest
{

	/**
	 * <p>Simplified list of empty fields.</p>
	 * 
	 * @see Core_Mage_Product_Create_BundleTest::emptyRequiredFieldInBundleDataProvider()
	 */
    public function emptyRequiredFieldInBundleDataProvider()
    {
        return array(
            array(array('general_name' => '%noValue%'), 'field'),
            array(array('prices_price' => '%noValue%'), 'field')
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     * 
     * @see Core_Mage_Product_Create_BundleTest::invalidNumericFieldDataProvider()
     */
    public function invalidNumericFieldDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:'))
        );
    }
}