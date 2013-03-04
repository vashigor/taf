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
 * Category deletion tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Category_DeleteTest extends Mage_Selenium_TestCase
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
     * <p>Deleting Root Category</p>
     * <p>Pre-Conditions:</p>
     * <p>Root Category created</p>
     * <p>Steps:</p>
     * <p>Select Root Category</p>
     * <p>Click "Delete" button</p>
     * <p>Expected result</p>
     * <p>Root category Deleted, Success message appears</p>
     *
     * @test
     */
    public function deleteRootCategory()
    {
        //Data
        $rootCategoryData = $this->loadDataSet('Category', 'root_category_required');
        //Steps
        $this->categoryHelper()->createCategory($rootCategoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();
        //Steps
        $this->categoryHelper()->selectCategory($rootCategoryData['name']);
        $this->categoryHelper()->deleteCategory('delete_category', 'confirm_delete');
        $this->assertMessagePresent('success', 'success_deleted_category');
    }

    /**
     * <p>Deleting  Subcategory</p>
     * <p>Pre-Conditions:</p>
     * <p>Subcategory created</p>
     * <p>Steps:</p>
     * <p>Select created Subcategory</p>
     * <p>Click "Delete" button</p>
     * <p>Expected result</p>
     * <p>Subcategory Deleted, Success message appears</p>
     *
     * @test
     */
    public function deleteSubCategory()
    {
        //Data
        $subCategoryData = $this->loadDataSet('Category', 'sub_category_required');
        //Steps
        $this->categoryHelper()->createCategory($subCategoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();
        //Steps
        $this->categoryHelper()->selectCategory($subCategoryData['parent_category'] . '/' . $subCategoryData['name']);
        $this->categoryHelper()->deleteCategory('delete_category', 'confirm_delete');
        $this->assertMessagePresent('success', 'success_deleted_category');
    }

    /**
     * <p>Deleting Root Category that assigned to store</p>
     * <p>Pre-Conditions:</p>
     * <p>Root Category created and assigned to store</p>
     * <p>Steps:</p>
     * <p>Select Root Category</p>
     * <p>Expected result</p>
     * <p>Verify that button "Delete" is absent on the page</p>
     *
     * @test
     */
    public function rootCategoryThatCannotBeDeleted()
    {
        //Data
        $rootCategoryData = $this->loadDataSet('Category', 'root_category_required');
        $storeData = $this->loadDataSet('Store', 'generic_store', array('root_category' => $rootCategoryData['name']));
        //Steps
        $this->categoryHelper()->createCategory($rootCategoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();
        //Steps
        $this->navigate('manage_stores');
        //Verifying
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->getParsedMessages());
        //Steps
        $this->storeHelper()->createStore($storeData, 'store');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_store');
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->getParsedMessages());
        //Steps
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->selectCategory($rootCategoryData['name']);
        //Verifying
        $this->assertFalse($this->buttonIsPresent('delete_category'), 'There is "Delete Category" button on the page');
    }

    /**
     * <p>Deleting Root Category with Subcategory</p>
     * <p>Pre-Conditions:</p>
     * <p>Root Category Created</p>
     * <p>Subcategory created</p>
     * <p>Steps:</p>
     * <p>Select created Root Category</p>
     * <p>Click "Delete" button</p>
     * <p>Expected result</p>
     * <p>Subcategory Deleted, Success message appears</p>
     *
     * @test
     */
    public function deleteRootCategoryWithSubcategories()
    {
        //Data
        $rootCategoryData = $this->loadDataSet('Category', 'root_category_required');
        $subCategoryData = $this->loadDataSet('Category', 'sub_category_required',
            array('parent_category'=> $rootCategoryData['name']));
        //Steps
        $this->categoryHelper()->createCategory($rootCategoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->categoryHelper()->createCategory($subCategoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->categoryHelper()->selectCategory($rootCategoryData['name']);
        $this->categoryHelper()->deleteCategory('delete_category', 'confirm_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_category');
    }

    /**
     * <p>Deleting Root Category with Subcategory</p>
     * <p>Pre-Conditions:</p>
     * <p>Root Category Created</p>
     * <p>Subcategory created</p>
     * <p>Steps:</p>
     * <p>Select created Root Category</p>
     * <p>Click "Delete" button</p>
     * <p>Expected result</p>
     * <p>Subcategory Deleted, Success message appears</p>
     *
     * @test
     */
    public function deleteRootCategoryWithSubcategoriesHavingProducts()
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $rootCategoryData = $this->loadDataSet('Category', 'root_category_required');
        $subCategoryData = $this->loadDataSet('Category', 'sub_category_all',
            array('category_products_search_sku' => $productData['general_sku'],
                  'parent_category'              => $rootCategoryData['name']));
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->assertPreConditions();
        $this->categoryHelper()->createCategory($rootCategoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->categoryHelper()->createCategory($subCategoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->categoryHelper()->selectCategory($rootCategoryData['name']);
        $this->categoryHelper()->deleteCategory('delete_category', 'confirm_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_category');
    }
}