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
class Mage_Selenium_Uimap_TabTest extends Mage_PHPUnit_TestCase
{
    /**
     * @covers Mage_Selenium_Uimap_Tab::__construct
     * @covers Mage_Selenium_Uimap_Tab::getTabId
     */
    public function test__construct()
    {
        $fileHelper = new Mage_Selenium_Helper_File($this->_config);
        $dataArray = $fileHelper->loadYamlFile
                (SELENIUM_TESTS_BASEDIR . '\fixture\default\core\Mage\UnitTest\data\UimapTests.yml');
        $tabContainer = $dataArray['tab'];
        $tabId = 'tabId';
        $instance = new Mage_Selenium_Uimap_Tab($tabId, $tabContainer);
        $this->assertInstanceOf('Mage_Selenium_Uimap_Tab', $instance);
        $this->assertEquals($tabId, $instance->getTabId());
    }

    /**
     * @covers Mage_Selenium_Uimap_Tab::getFieldsetNames
     */
    public function testGetFieldsetNames()
    {
        $fileHelper = new Mage_Selenium_Helper_File($this->_config);
        $dataArray = $fileHelper->loadYamlFile
                (SELENIUM_TESTS_BASEDIR . '\fixture\default\core\Mage\UnitTest\data\UimapTests.yml');
        $tabContainer = $dataArray['tab'];
        $instance = new Mage_Selenium_Uimap_Tab('tabId', $tabContainer);
        $elements = $instance->getFieldsetNames();
        $this->assertInternalType('array', $elements);
        $this->assertContains('first_fieldset', $elements);
        $this->assertContains('second_fieldset', $elements);

        $tabContainer = array();
        $instance = new Mage_Selenium_Uimap_Tab('tabId', $tabContainer);
        $elements = $instance->getFieldsetNames();
        $this->assertInternalType('array', $elements);
        $this->assertEmpty($elements);
    }
}