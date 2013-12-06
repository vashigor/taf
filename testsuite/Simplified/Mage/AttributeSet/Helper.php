<?php
/**
 * MTAF Simplified
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to vash.igor(at)gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * The Original Work is provided under this License on an "AS IS" BASIS and WITHOUT WARRANTY,
 * either express or implied, including, without limitation, the warranties of non-infringement,
 * merchantability or fitness for a particular purpose.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Igor Tkachenko <vash.igor(at)gmail.com>
 * @copyright   Copyright (c) 2013 Igor Tkachenko (https://github.com/vashigor/taf)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_AttributeSet_Helper extends Core_Mage_AttributeSet_Helper
{

    /**
     * <p>Add attribute to attribute Set</p>
     * <p>This method is overriden to fix test for the long list of attributes.</p>
     *
     * @param array $attributes Array which contains DataSet for filling folder of attribute set
     * @see Core_Mage_AttributeSet_Helper::addAttributeToSet();
     */
    public function addAttributeToSet(array $attributes)
    {
        foreach ($attributes as $groupName => $attributeCode) {
            if ($attributeCode == '%noValue%') {
                continue;
            }
            if (is_string($attributeCode)) {
                $attributeCode = explode(',', $attributeCode);
                $attributeCode = array_map('trim', $attributeCode);
            }
            $this->addParameter('folderName', $groupName);
            foreach ($attributeCode as $value) {
                $this->addParameter('attributeName', $value);
                if (!$this->controlIsPresent('link', 'group_folder')) {
                    $this->addNewGroup($groupName);
                }
                if (!$this->controlIsPresent('link', 'unassigned_attribute')) {
                    $this->fail("Attribute with title '$value' does not exist");
                }
                $this->moveElementOverTree('link', 'unassigned_attribute', 'fieldset', 'unassigned_attributes');
                $this->moveElementOverTree('link', 'group_folder', 'fieldset', 'groups_content');
                $elFrom = $this->_getControlXpath('link', 'unassigned_attribute');
                $elTo = $this->_getControlXpath('link', 'group_folder');
                $this->clickAt($elFrom, '1,1');
                $this->keyPress($elFrom,'36'); // Press home to fix test for the long list of attributes
                $this->clickAt($elTo, '1,1');
                $this->keyPress($elTo,'36');   // Press home to fix test for the long list of attributes
                $this->mouseDownAt($elFrom, '1,1');
                $this->mouseMoveAt($elTo, '1,1');
                $this->mouseUpAt($elTo, '10,10');
            }
        }
    }

}