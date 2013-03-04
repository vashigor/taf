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
 * Delete Rating in Backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Rating_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Log in to Backend.</p>
     */
    public function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     *
     * @test
     * @return string
     */
    public function preconditionsForTests()
    {
        //Data
        $simpleData = $this->loadDataSet('Product', 'simple_product_visible');
        $storeView = $this->loadDataSet('StoreView', 'generic_store_view');
        //Steps
        $this->navigate('manage_stores');
        $this->storeHelper()->createStore($storeView, 'store_view');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_store_view');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simpleData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');

        return array('store' => $storeView['store_view_name'],
                     'sku'   => $simpleData['general_sku']);
    }

    /**
     * <p>Delete rating that is used in Review</p>
     * <p>Preconditions:</p>
     * <p>Rating created</p>
     * <p>Review created using Rating</p>
     * <p>Steps:</p>
     * <p>1. Open created rating;</p>
     * <p>2. Click "Delete" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - Rating removed from the list</p>
     *
     * @param $data
     *
     * @test
     * @depends preconditionsForTests
     */
    public function deleteRatingUsedInReview($data)
    {
        $rating = $this->loadDataSet('ReviewAndRating', 'default_rating', array('visible_in' => $data['store']));
        $review = $this->loadDataSet('ReviewAndRating', 'review_required_with_rating',
            array('rating_name' => $rating['default_value'],
                  'visible_in'  => $data['store'],
                  'filter_sku'  => $data['sku']));
        $searchRating = $this->loadDataSet('ReviewAndRating', 'search_rating',
            array('filter_rating_name' => $rating['default_value']));
        $searchReview = $this->loadDataSet('ReviewAndRating', 'search_review_admin',
            array('filter_nickname'    => $review['nickname'],
                  'filter_product_sku' => $data['sku']));
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($rating);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rating');
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->createReview($review);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->deleteRating($searchRating);
        //Verification
        $this->assertMessagePresent('success', 'success_deleted_rating');
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->openReview($searchReview);
        //Verification
        $this->assertMessagePresent('success', 'not_available_rating');
    }

    /**
     * <p>Delete rating</p>
     * <p>Preconditions:</p>
     * <p>Rating created</p>
     * <p>Steps:</p>
     * <p>1. Open created rating;</p>
     * <p>2. Click "Delete" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - Rating removed from the list</p>
     *
     * @test
     */
    public function deleteRatingNotUsedInReview()
    {
        $rating = $this->loadDataSet('ReviewAndRating', 'rating_required_fields');
        $search = $this->loadDataSet('ReviewAndRating', 'search_rating',
            array('filter_rating_name' => $rating['default_value']));
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($rating);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rating');
        //Steps
        $this->ratingHelper()->deleteRating($search);
        //Verification
        $this->assertMessagePresent('success', 'success_deleted_rating');
    }
}