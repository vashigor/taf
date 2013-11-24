<?php

/**
 * Configurable product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Product_Create_ConfigurableTest extends Core_Mage_Product_Create_ConfigurableTest
{

    /**
     * <p>Simplified list of empty fields.</p>
     *
     * @see Core_Mage_Product_Create_ConfigurableTest::emptyRequiredFieldInConfigurableDataProvider()
     */
    public function emptyRequiredFieldInConfigurableDataProvider()
    {
        return array(
            array('general_sku', 'field'),
            array('general_visibility', 'dropdown')
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     *
     * @see Core_Mage_Product_Create_ConfigurableTest::invalidNumericFieldDataProvider()
     */
    public function invalidNumericFieldDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':alpha:'))
        );
    }

}