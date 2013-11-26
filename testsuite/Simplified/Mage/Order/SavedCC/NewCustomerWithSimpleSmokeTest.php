<?php
/**
 * Test admin order workflow with SavedCC payment method
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Order_SavedCC_NewCustomerWithSimpleSmokeTest extends Core_Mage_Order_SavedCC_NewCustomerWithSimpleSmokeTest
{

    /**
     * <p>We don't need tests with 3D secure in the Simplified mode.</p>
     * @see Core_Mage_Order_SavedCC_NewCustomerWithSimpleSmokeTest::createOrderWith3DSecure()
     */
    public function createOrderWith3DSecure($card, $needSetUp, $orderData)
    {
    }

}