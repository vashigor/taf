<?php

/**
 * Simple product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Product_Create_SimpleTest extends Core_Mage_Product_Create_SimpleTest
{

    /**
     * <p>Simplified list of empty fields.</p>
     *
     * @see Core_Mage_Product_Create_SimpleTest::withRequiredFieldsEmptyDataProvider()
     */
    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('general_weight', 'field'),
            array('inventory_qty', 'field')
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     *
     * @see Core_Mage_Product_Create_SimpleTest::invalidQtyDataProvider()
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
     * @see Core_Mage_Product_Create_SimpleTest::invalidNumericFieldDataProvider()
     */
    public function invalidNumericFieldDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:'))
        );
    }

}