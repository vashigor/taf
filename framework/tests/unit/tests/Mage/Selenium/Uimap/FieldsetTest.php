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
 * @package     selenium unit tests
 * @subpackage  Mage_PHPUnit
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Uimap_FieldsetTest extends Mage_PHPUnit_TestCase
{
    /**
     * @covers Mage_Selenium_Uimap_Fieldset::__construct
     */
    public function test__construct()
    {
        $fileHelper = new Mage_Selenium_Helper_File($this->_config);
        $dataArray = $fileHelper->loadYamlFile
                (SELENIUM_TESTS_BASEDIR . '\fixture\default\core\Mage\UnitTest\data\UimapTests.yml');
        $fieldsetContainer = $dataArray['fieldset'];
        $instance = new Mage_Selenium_Uimap_Fieldset('fieldsetId', $fieldsetContainer);
        $this->assertInstanceOf('Mage_Selenium_Uimap_Fieldset', $instance);
        $this->assertEquals("//div[@class='the-fieldset']", $instance->getXPath());
    }

    /**
     * @covers Mage_Selenium_Uimap_Fieldset::getFieldsetElements
     */
    public function testGetFieldsetElements()
    {
        $fileHelper = new Mage_Selenium_Helper_File($this->_config);
        $dataArray = $fileHelper->loadYamlFile
                (SELENIUM_TESTS_BASEDIR . '\fixture\default\core\Mage\UnitTest\data\UimapTests.yml');
        $fieldsetContainer = $dataArray['fieldset'];
        $instance = new Mage_Selenium_Uimap_Fieldset('fieldsetId', $fieldsetContainer);
        $elements = $instance->getFieldsetElements();
        $this->assertInternalType('array', $elements);
        $this->assertArrayHasKey('button', $elements);
        $this->assertArrayHasKey('checkbox', $elements);
        $this->assertEquals($elements['checkbox']['first_checkbox'], "//div[@class='the-fieldset']//input[@id='the-first-checkbox']");

        $fieldsetContainer = array();
        $instance = new Mage_Selenium_Uimap_Fieldset('fieldsetId', $fieldsetContainer);
        $elements = $instance->getFieldsetElements();
        $this->assertInternalType('array', $elements);
        $this->assertEmpty($elements);
    }
}