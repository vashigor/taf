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
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_AdminUser_Helper extends Mage_Selenium_TestCase
{
    /**
     * Create Admin User.
     *
     * @param Array $userData
     */
    public function createAdminUser($userData)
    {
        $this->clickButton('add_new_admin_user');
        $this->fillForm($userData, 'user_info');
        $first = (isset($userData['first_name'])) ? $userData['first_name'] : '';
        $last = (isset($userData['last_name'])) ? $userData['last_name'] : '';
        $param = $first . ' ' . $last;
        $this->addParameter('elementTitle', $param);
        if (array_key_exists('role_name', $userData)) {
            $this->openTab('user_role');
            $this->searchAndChoose(array('role_name' => $userData['role_name']), 'permissions_user_roles');
        }
        $this->saveForm('save_admin_user');
    }

    /**
     * Login Admin User
     *
     * @param array $loginData
     */
    public function loginAdmin($loginData)
    {
        $waitCondition = array($this->_getMessageXpath('general_error'), $this->_getMessageXpath('general_validation'),
                               $this->_getControlXpath('pageelement', 'admin_logo'));
        $this->fillForm($loginData);
        $this->clickButton('login', false);
        $this->waitForElement($waitCondition);
        $this->validatePage();
    }

    /**
     * Forgot Password Admin User
     *
     * @param array $emailData
     */
    public function forgotPassword($emailData)
    {
        $waitCondition = array($this->_getMessageXpath('general_success'), $this->_getMessageXpath('general_error'),
                               $this->_getMessageXpath('general_validation'));
        $this->clickControl('link', 'forgot_password');
        $this->assertTrue($this->checkCurrentPage('forgot_password'));
        $this->fillForm($emailData);
        $this->clickButton('retrieve_password', false);
        $this->waitForElement($waitCondition);
        $this->validatePage();
    }
}