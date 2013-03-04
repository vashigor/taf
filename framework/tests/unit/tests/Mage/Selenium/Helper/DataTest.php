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
class Mage_Selenium_Helper_DataTest extends Mage_PHPUnit_TestCase
{
    /**
     * @covers Mage_Selenium_Helper_Data::getDataValue
     */
    public function testGetDataValue()
    {
        $instance = new Mage_Selenium_Helper_Data($this->_config);
        $this->assertInternalType('array', $instance->getDataValue());
        $this->assertNotEmpty($instance->getDataValue());

        $this->assertFalse($instance->getDataValue('invalid-path'));

        $this->assertArrayHasKey('generic_admin_user', $instance->getDataValue());
        $this->assertInternalType('array', $instance->getDataValue('generic_admin_user'));
        $this->assertInternalType('string', $instance->getDataValue('generic_admin_user/user_name'));
    }

    /**
     * @covers Mage_Selenium_Helper_Data::loadTestDataSet
     */
    public function testLoadTestDataSet()
    {
        $instance = new Mage_Selenium_Helper_Data($this->_config);
        $dataSet = $instance->loadTestDataSet('default\core\Mage\UnitTest\data\UnitTestsData', 'unit_test_load_data');
        $this->assertInternalType('array', $dataSet);
        $this->assertArrayHasKey('another_key', $dataSet);
        $this->assertEquals($dataSet['another_key'], 'another Value');

        $this->assertEquals($dataSet, $instance->loadTestDataSet(
                                                    'default\core\Mage\UnitTest\data\UnitTestsData.yml',
                                                    'unit_test_load_data'));
        $this->assertEquals($dataSet, $instance->loadTestDataSet(
                                                    'default/core/Mage/UnitTest/data/UnitTestsData',
                                                    'unit_test_load_data'));
        $this->assertEquals($dataSet, $instance->loadTestDataSet(
                                                    'default/core/Mage/UnitTest/data/UnitTestsData.yml',
                                                    'unit_test_load_data'));
    }

    /**
     * @covers Mage_Selenium_Helper_Data::loadTestDataSet
     */
    public function testLoadTestDataSetEmpty()
    {
        $instance = new Mage_Selenium_Helper_Data($this->_config);
        $this->setExpectedException('RuntimeException', 'file is empty');
        $instance->loadTestDataSet('default\core\Mage\UnitTest\data\Empty', 'unit_test_load_data');
    }

    /**
     * @covers Mage_Selenium_Helper_Data::loadTestDataSet
     */
    public function testLoadTestDataSetNoDataset()
    {
        $instance = new Mage_Selenium_Helper_Data($this->_config);
        $this->setExpectedException('RuntimeException', 'DataSet with name "not_existing_dataset" is not present');
        $instance->loadTestDataSet('default\core\Mage\UnitTest\data\UnitTestsData', 'not_existing_dataset');
    }
}