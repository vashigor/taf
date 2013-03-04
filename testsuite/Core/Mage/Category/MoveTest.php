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
 * Category Move Tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Category_MoveTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Catalog -> Manage Categories</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
    }

    /**
     * <p>Move Root Category to Root category.</p>
     * <p>Steps:</p>
     * <p>1.Go to Manage Categories;</p>
     * <p>2.Create 2 root categories;</p>
     * <p>3.Move first root category to second one;</p>
     * <p>Expected result:</p>
     * <p>Category is moved successfully</p>
     *
     * @test
     */
    public function rootCategoryToRoot()
    {
        //Data
        $categoryDataFrom = $this->loadDataSet('Category', 'root_category_required');
        $categoryDataTo = $this->loadDataSet('Category', 'root_category_required');
        //Steps
        $this->categoryHelper()->createCategory($categoryDataFrom);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->categoryHelper()->createCategory($categoryDataTo);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->categoryHelper()->moveCategory($categoryDataFrom['name'], $categoryDataTo['name']);
        //Verification
        $this->categoryHelper()->selectCategory($categoryDataTo['name'] . '/' . $categoryDataFrom['name']);
    }

    /**
     * <p>Move Root with Sub Category to Root category.</p>
     * <p>Steps:</p>
     * <p>1.Go to Manage Categories;</p>
     * <p>2.Create 2 root categories and 1 sub category;</p>
     * <p>3.Move first root category with sub to second root;</p>
     * <p>Expected result:</p>
     * <p>Category is moved successfully</p>
     *
     * @test
     */
    public function rootWithSubToRoot()
    {
        //Data
        $categoryDataFrom = $this->loadDataSet('Category', 'root_category_required');
        $categoryDataSub = $this->loadDataSet('Category', 'sub_category_required',
            array('parent_category' => $categoryDataFrom['name']));
        $categoryDataTo = $this->loadDataSet('Category', 'root_category_required');
        //Steps
        $this->categoryHelper()->createCategory($categoryDataFrom);
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->createCategory($categoryDataSub);
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->createCategory($categoryDataTo);
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->moveCategory($categoryDataFrom['name'], $categoryDataTo['name']);
        //Verification
        $this->categoryHelper()->selectCategory(
            $categoryDataTo['name'] . '/' . $categoryDataFrom['name'] . '/' . $categoryDataSub['name']);
    }

    /**
     * <p>Move Sub Category to Sub category.</p>
     * <p>Steps:</p>
     * <p>1.Go to Manage Categories;</p>
     * <p>2.Create 2 root categories and 2 sub category;</p>
     * <p>3.Move first sub category to second sub;</p>
     * <p>Expected result:</p>
     * <p>Category is moved successfully</p>
     *
     * @test
     */
    public function subToSubNestedCategory()
    {
        //Data
        $categoryDataFrom = $this->loadDataSet('Category', 'root_category_required');
        $categoryDataSubFrom = $this->loadDataSet('Category', 'sub_category_required',
            array('parent_category' => $categoryDataFrom['name']));
        $categoryDataTo = $this->loadDataSet('Category', 'root_category_required');
        $categoryDataSubTo = $this->loadDataSet('Category', 'sub_category_required',
            array('parent_category' => $categoryDataTo['name']));
        //Steps
        $this->categoryHelper()->createCategory($categoryDataFrom);
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->createCategory($categoryDataSubFrom);
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->createCategory($categoryDataTo);
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->createCategory($categoryDataSubTo);
        $this->categoryHelper()->moveCategory($categoryDataSubFrom['name'], $categoryDataSubTo['name']);
        //Verification
        $this->categoryHelper()->selectCategory(
            $categoryDataTo['name'] . '/' . $categoryDataSubTo['name'] . '/' . $categoryDataSubFrom['name']);
    }

    /**
     * <p>Move Root Category assigned to store to Root category.</p>
     * <p>Steps:</p>
     * <p>1.Go to Manage Categories;</p>
     * <p>2.Create 1 root category;</p>
     * <p>3.Create website with store;</p>
     * <p>4.Assign created root category to store</p>
     * <p>4.Create another root category</p>
     * <p>3.Move first root category assigned to store to second root;</p>
     * <p>Expected result:</p>
     * <p>Category is not moved</p>
     *
     * @test
     */
    public function rootCategoryAssignedToWebsite()
    {
        //Data
        $categoryDataFrom = $this->loadDataSet('Category', 'root_category_required');
        $websiteData = $this->loadDataSet('Website', 'generic_website');
        $storeData = $this->loadDataSet('Store', 'generic_store', array('website'       => $websiteData['website_name'],
                                                                        'root_category' => $categoryDataFrom['name']));
        $categoryDataTo = $this->loadDataSet('Category', 'root_category_required');
        //Create categories
        $this->categoryHelper()->createCategory($categoryDataFrom);
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->createCategory($categoryDataTo);
        $this->assertMessagePresent('success', 'success_saved_category');
        //Create Website and Store. Assign root category to store
        $this->navigate('manage_stores');
        $this->storeHelper()->createStore($websiteData, 'website');
        $this->assertMessagePresent('success', 'success_saved_website');
        $this->storeHelper()->createStore($storeData, 'store');
        $this->assertMessagePresent('success', 'success_saved_store');
        //Try to move assigned to store root category
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->moveCategory($categoryDataFrom['name'], $categoryDataTo['name']);
        //Verification
        $this->categoryHelper()->selectCategory($categoryDataFrom['name']);
    }
}
