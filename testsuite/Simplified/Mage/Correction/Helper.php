<?php

/**
 * Helper class
 * This class provide the lists of tests' parameters for Simplified mode.
 *
 * @package     simplified
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Correction_Helper extends Mage_Selenium_TestCase
{

    /**
     * <p>Emulating credit card we get from PayPal Sandbox.</p>
     *
     * @return array
     */
    public function getPaypalLikeVisaAccount()
    {
        $cardData = $this->loadDataSet('Payment', 'saved_visa');
        $account = array(
                'visa' => array(
                        'credit_card' => $cardData
                )
        );
        return $account;
    }

    /**
     * <p>Providing simplified data set for payment method tests.</p>
     *
     * @return array
     */
    public function getPaymentMethodsForDataProvider()
    {
        return array(
                array('savedcc'),
                array('checkmoney')
        );
    }

    /**
     * <p>Providing simplified data set for shipping method tests.</p>
     *
     * @return array
     */
    public function getShippingMethodsForDataProvider()
    {
        return array(
            array('flatrate', null, 'usa'),
            array('free', null, 'usa')
        );
    }

}