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
 * Tags Validation on the frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tags_FrontendCreateTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTest()
    {
        $this->loginAdminUser();
        $this->navigate('all_tags');
        $this->tagsHelper()->deleteAllTags();
        $this->logoutCustomer();
    }

    /**
     * @return array
     * @test
     * @skipTearDown
     */
    public function preconditionsForTests()
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        //Steps and Verification
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');
        $simple = $this->productHelper()->createSimpleProduct(true);
        $this->reindexInvalidedData();
        $this->flushCache();

        return array('user'     => array('email'    => $userData['email'],
                                         'password' => $userData['password']),
                     'simple'   => $simple['simple']['product_name'],
                     'category' => $simple['category']['path']);
    }

    /**
     * <p>Tag creating with Logged Customer</p>
     * <p>1. Login to Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Tag to product</p>
     * <p>4. Check confirmation message</p>
     * <p>5. Goto "My Account"</p>
     * <p>6. Check tag displaying in "My Recent Tags"</p>
     * <p>7. Goto "My Tags" tab</p>
     * <p>8. Check tag displaying on the page</p>
     * <p>9. Open current tag - page with assigned product opens</p>
     * <p>10. Tag is assigned to correct product</p>
     *
     * @param string $tags
     * @param array $testData
     *
     * @test
     * @dataProvider tagNameDataProvider
     * @depends preconditionsForTests
     */
    public function frontendTagVerificationLoggedCustomer($tags, $testData)
    {
        //Setup
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->productHelper()->frontOpenProduct($testData['simple']);
        //Steps
        $this->tagsHelper()->frontendAddTag($tags);
        //Verification
        $this->assertMessagePresent('success', 'tag_accepted_success');
        $this->tagsHelper()->frontendTagVerification($tags, $testData['simple']);
    }

    public function tagNameDataProvider()
    {
        return array(
            //1 simple word
            array($this->generate('string', 4, ':alpha:')),
            //1 tag enclosed within quotes
            array("'" . $this->generate('string', 4, ':alpha:') . "'"),
            //2 tags separated with a space
            array($this->generate('string', 4, ':alpha:') . ' ' . $this->generate('string', 7, ':alpha:')),
            //1 tag with a space; enclosed within quotes
            array("'" . $this->generate('string', 4, ':alpha:') . ' ' . $this->generate('string', 7, ':alpha:') . "'"),
            //3 tags = 1 word + 1 phrase with a space + 1 word; enclosed within quotes
            array($this->generate('string', 4, ':alpha:') . ' ' . "'" . $this->generate('string', 4, ':alpha:') . ' '
                  . $this->generate('string', 7, ':alpha:') . "'" . ' ' . $this->generate('string', 4, ':alpha:'))
        );
    }

    /**
     * Tag creating with Not Logged Customer
     * <p>1. Goto Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Tag to product</p>
     * <p>4. Login page opened</p>
     * <p>Expected result:</p>
     * <p>Customer is redirected to the login page.</p>
     * <p>The tag has not been added for moderation in backend.</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function frontendTagVerificationNotLoggedCustomer($testData)
    {
        //Data
        $tag = $this->generate('string', 8, ':alpha:');
        $searchTag = $this->loadDataSet('Tag', 'backend_search_tag', array('tag_name' => $tag));
        //Setup
        $this->frontend();
        $this->productHelper()->frontOpenProduct($testData['simple']);
        //Steps
        $this->tagsHelper()->frontendAddTag($tag);
        //Verification
        $this->assertTrue($this->checkCurrentPage('customer_login'), $this->getParsedMessages());
        $this->loginAdminUser();
        $this->navigate('all_tags');
        $this->assertNull($this->search($searchTag, 'tags_grid'), $this->getMessagesOnPage());
        $this->customerHelper()->frontLoginCustomer($testData['user']);
    }

    /**
     * <p>Tags Verification in Category</p>
     * <p>1. Login to Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Tag to product</p>
     * <p>4. Check confirmation message</p>
     * <p>5. Logout;</p>
     * <p>6. Login to backend;</p>
     * <p>7. Navigate to "Catalog->Tags->Pending Tags";</p>
     * <p>8. Change the status of created Tag;</p>
     * <p>9. Goto Frontend;</p>
     * <p>10. Check Tag displaying on category page;</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function frontendTagVerificationInCategory($testData)
    {
        //Data
        $tag = $this->generate('string', 10, ':alpha:');
        $tagToApprove = $this->loadDataSet('Tag', 'backend_search_tag', array('tag_name' => $tag));
        //Setup
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->productHelper()->frontOpenProduct($testData['simple']);
        //Steps
        $this->tagsHelper()->frontendAddTag($tag);
        //Verification
        $this->assertMessagePresent('success', 'tag_accepted_success');
        //Steps
        $this->loginAdminUser();
        $this->navigate('pending_tags');
        $this->tagsHelper()->changeTagsStatus(array($tagToApprove), 'Approved');
        //Verification
        $this->frontend();
        $this->tagsHelper()->frontendTagVerificationInCategory($tag, $testData['simple'], $testData['category']);
    }
}