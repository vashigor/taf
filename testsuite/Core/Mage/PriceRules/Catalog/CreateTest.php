<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Price Rule creation
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_PriceRules_Catalog_CreateTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_catalog_price_rules');
    }

    protected function tearDownAfterTestClass()
    {
        $this->loginAdminUser();
        $this->navigate('manage_catalog_price_rules');
        $this->priceRulesHelper()->deleteAllRules();
        $this->clickButton('apply_rules', false);
        $this->waitForNewPage();
        $this->assertMessagePresent('success', 'success_applied_rule');
    }

    /**
     * <p>Create a new catalog price rule</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Rule"</p>
     * <p>2. Fill in only required fields in all tabs</p>
     * <p>3. Click "Save Rule" button</p>
     * <p>Expected result:</p>
     * <p>New rule is created. Success message appears.</p>
     *
     * @return array
     * @test
     */
    public function requiredFields()
    {
        //Data
        $priceRuleData = $this->loadDataSet('CatalogPriceRule', 'test_catalog_rule');
        //Steps
        $this->priceRulesHelper()->createRule($priceRuleData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->assertMessagePresent('success', 'notification_message');
        return $priceRuleData;
    }

    /**
     * <p>Validation of Discount Amount field</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Rule"</p>
     * <p>2. Fill in "General Information" tab</p>
     * <p>3. Specify "Conditions"</p>
     * <p>4. Enter invalid data into "Discount Amount" and "Sub Discount Amount" fields</p>
     * <p>Expected result: Validation messages appears</p>
     *
     * @param string $invalidDiscountData
     *
     * @test
     * @dataProvider invalidDiscountAmountDataProvider
     */
    public function invalidDiscountAmount($invalidDiscountData)
    {
        //Data
        $priceRuleData = $this->loadDataSet('CatalogPriceRule', 'test_catalog_rule',
                                            array('sub_discount_amount' => $invalidDiscountData,
                                                 'discount_amount'      => $invalidDiscountData));
        //Steps
        $this->priceRulesHelper()->createRule($priceRuleData);
        //Verification
        $this->assertMessagePresent('validation', 'invalid_discount_amount');
        $this->assertMessagePresent('validation', 'invalid_sub_discount_amount');
        $this->assertTrue($this->verifyMessagesCount(2), $this->getParsedMessages());
    }

    public function invalidDiscountAmountDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:')),
            array($this->generate('string', 9, ':alpha:')),
            array('g3648GJTest'),
            array('-128')
        );
    }
}
