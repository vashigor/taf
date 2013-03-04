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
 * Test for related, up-sell and cross-sell products.
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_Linking_BundleLinkingTest extends Mage_Selenium_TestCase
{
    private static $productTypes = array('simple', 'virtual', 'downloadable',
                                         'bundle', 'configurable', 'grouped');

    protected function assertPreconditions()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Create all types of products</p>
     *
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        $forLinking = array();
        $linking = $this->productHelper()->createBundleProduct();
        foreach (self::$productTypes as $product) {
            $method = 'create' . ucfirst($product) . 'Product';
            $forLinking[$product] = $this->productHelper()->$method();
        }

        return array($linking, $forLinking);
    }

    /**
     * <p>Review Related products(inStock) on frontend assigned to bundle product.</p>
     * <p>Preconditions:</p>
     * <p>Created All Types of products (in stock) and realized next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Open product in stock; Attach all types of products to the product as Related products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>4. Validate names of Related products in "Related products block";</p>
     * <p>Expected result:</p>
     * <p>The product contains block with Related products; Names of Related products are correct</p>
     *
     * @param array $testData
     * @param string $linkingType
     *
     * @test
     * @dataProvider linkingTypeDataProvider
     * @depends preconditionsForTests
     */
    public function relatedInStock($linkingType, $testData)
    {
        //Data
        $assignType = 'related';
        $assignProductType = 'bundle';
        list($linking, $forLinking) = $testData;
        $forLinking = $forLinking[$linkingType][$linkingType];
        $search = $this->loadDataSet('Product', 'product_search', $linking[$assignProductType]);
        $assign = $this->loadDataSet('Product', $assignType . '_1',
                                     array($assignType . '_search_name' => $forLinking['product_name'],
                                          $assignType . '_search_sku'   => $forLinking['product_sku']));
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->openProduct($search);
        $this->productHelper()->unselectAssociatedProduct($assignType);
        $this->productHelper()->assignProduct($assign, $assignType);
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        $this->assertSame($assignType, $this->_getActiveTabUimap()->getTabId(),
            'Wrong Tab is opened after saving product');
        $this->productHelper()->isAssignedProduct($assign, $assignType);
        $this->assertEmptyVerificationErrors();
        $this->clearInvalidedCache();
        $this->reindexInvalidedData();
        $this->productHelper()->frontOpenProduct($linking[$assignProductType]['product_name']);
        $this->addParameter('productName', $forLinking['product_name']);
        if (!$this->controlIsPresent('link', $assignType . '_product')) {
            $this->addVerificationMessage($assignType . ' product ' . $forLinking['product_name']
                                              . ' is not on "' . $this->getCurrentPage() . '" page');
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Review Cross-sell products(inStock) on frontend assigned to bundle product.</p>
     * <p>Preconditions:</p>
     * <p>Created All Types of products (in stock) and realized next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Open product in stock; Attach all types of products to the product as Cross-sell products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>4. Validate names of Cross-sell products in "Cross-sell products block";</p>
     * <p>Expected result:</p>
     * <p>The product contains block with Cross-sell products; Names of Cross-sell products are correct</p>
     *
     * @param array $testData
     * @param string $linkingType
     *
     * @test
     * @dataProvider linkingTypeDataProvider
     * @depends preconditionsForTests
     */
    public function crossSellsInStock($linkingType, $testData)
    {
        //Data
        $assignType = 'cross_sells';
        $assignProductType = 'bundle';
        list($linking, $forLinking) = $testData;
        $dataForBuy = $this->loadDataSet('Product', $assignProductType . '_options_to_add_to_shopping_cart', null,
                                         $linking[$assignProductType . 'Option']);
        $forLinking = $forLinking[$linkingType][$linkingType];
        $search = $this->loadDataSet('Product', 'product_search', $linking[$assignProductType]);
        $assign = $this->loadDataSet('Product', $assignType . '_1',
                                     array($assignType . '_search_name' => $forLinking['product_name'],
                                          $assignType . '_search_sku'   => $forLinking['product_sku']));
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->openProduct($search);
        $this->productHelper()->unselectAssociatedProduct($assignType);
        $this->productHelper()->assignProduct($assign, $assignType);
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        $this->assertSame($assignType, $this->_getActiveTabUimap()->getTabId(),
            'Wrong Tab is opened after saving product');
        $this->productHelper()->isAssignedProduct($assign, $assignType);
        $this->assertEmptyVerificationErrors();
        $this->clearInvalidedCache();
        $this->reindexInvalidedData();
        $this->frontend();
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->productHelper()->frontOpenProduct($linking[$assignProductType]['product_name']);
        $this->productHelper()->frontAddProductToCart($dataForBuy);
        $this->addParameter('productName', $forLinking['product_name']);
        if (!$this->controlIsPresent('link', $assignType . '_product')) {
            $this->addVerificationMessage($assignType . ' product ' . $forLinking['product_name']
                                              . ' is not on "' . $this->getCurrentPage() . '" page');
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Review Up-sell products(inStock) on frontend assigned to bundle product.</p>
     * <p>Preconditions:</p>
     * <p>Created All Types of products (in stock) and realized next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Open product in stock; Attach all types of products to the product as Up-sell products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>4. Validate names of Up-sell products in "Up-sell products block";</p>
     * <p>Expected result:</p>
     * <p>The product contains block with Up-sell products; Names of Up-sell products are correct</p>
     *
     * @param array $testData
     * @param string $linkingType
     *
     * @test
     * @dataProvider linkingTypeDataProvider
     * @depends preconditionsForTests
     */
    public function upSellsInStock($linkingType, $testData)
    {
        //Data
        $assignType = 'up_sells';
        $assignProductType = 'bundle';
        list($linking, $forLinking) = $testData;
        $forLinking = $forLinking[$linkingType][$linkingType];
        $search = $this->loadDataSet('Product', 'product_search', $linking[$assignProductType]);
        $assign = $this->loadDataSet('Product', $assignType . '_1',
                                     array($assignType . '_search_name' => $forLinking['product_name'],
                                          $assignType . '_search_sku'   => $forLinking['product_sku']));
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->openProduct($search);
        $this->productHelper()->unselectAssociatedProduct($assignType);
        $this->productHelper()->assignProduct($assign, $assignType);
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        $this->assertSame($assignType, $this->_getActiveTabUimap()->getTabId(),
            'Wrong Tab is opened after saving product');
        $this->productHelper()->isAssignedProduct($assign, $assignType);
        $this->assertEmptyVerificationErrors();
        $this->clearInvalidedCache();
        $this->reindexInvalidedData();
        $this->productHelper()->frontOpenProduct($linking[$assignProductType]['product_name']);
        $this->addParameter('productName', $forLinking['product_name']);
        if (!$this->controlIsPresent('link', $assignType . '_product')) {
            $this->addVerificationMessage($assignType . ' product ' . $forLinking['product_name']
                                              . ' is not on "' . $this->getCurrentPage() . '" page');
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * <p>Review Related products(OutStock) on frontend assigned to bundle product.</p>
     * <p>Preconditions:</p>
     * <p>Created All Types of products (in stock) and realized next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Open product in stock; Attach all types of products to the product as Related products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>Expected result:</p>
     * <p>The product does not contain block with Related products</p>
     *
     * @param array $testData
     * @param string $linkingType
     *
     * @test
     * @dataProvider linkingTypeDataProvider
     * @depends preconditionsForTests
     */
    public function relatedOutStock($linkingType, $testData)
    {
        //Data
        $assignType = 'related';
        $assignProductType = 'bundle';
        list($linking, $forLinking) = $testData;
        $forLinking = $forLinking[$linkingType][$linkingType];
        $search = $this->loadDataSet('Product', 'product_search', $linking[$assignProductType]);
        $assign = $this->loadDataSet('Product', $assignType . '_1',
                                     array($assignType . '_search_name' => $forLinking['product_name'],
                                          $assignType . '_search_sku'   => $forLinking['product_sku']));
        $searchAssigned = $this->loadDataSet('Product', 'product_search', $forLinking);
        //Steps
        $this->navigate('manage_products');
        //Set product to 'Out of Stock';
        $this->productHelper()->openProduct($searchAssigned);
        $this->openTab('inventory');
        $this->fillDropdown('inventory_stock_availability', 'Out of Stock');
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        //Assign product
        $this->navigate('manage_products');
        $this->productHelper()->openProduct($search);
        $this->productHelper()->unselectAssociatedProduct($assignType);
        $this->productHelper()->assignProduct($assign, $assignType);
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        $this->assertSame($assignType, $this->_getActiveTabUimap()->getTabId(),
            'Wrong Tab is opened after saving product');
        $this->productHelper()->isAssignedProduct($assign, $assignType);
        $this->assertEmptyVerificationErrors();
        $this->clearInvalidedCache();
        $this->reindexInvalidedData();
        //Verify
        $this->productHelper()->frontOpenProduct($linking[$assignProductType]['product_name']);
        $this->addParameter('productName', $forLinking['product_name']);
        if ($this->controlIsPresent('link', $assignType . '_product')) {
            $this->addVerificationMessage($assignType . ' product ' . $forLinking['product_name']
                                              . ' is on "' . $this->getCurrentPage() . '" page');
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Review Cross-sell products(OutStock) on frontend assigned to bundle product.</p>
     * <p>Preconditions:</p>
     * <p>Created All Types of products (in stock) and realized next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Open product in stock; Attach all types of products to the product as Cross-sell products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>Expected result:</p>
     * <p>The product does not contain block with Cross-sell products</p>
     *
     * @param array $testData
     * @param string $linkingType
     *
     * @test
     * @dataProvider linkingTypeDataProvider
     * @depends preconditionsForTests
     */
    public function crossSellsOutStock($linkingType, $testData)
    {
        //Data
        $assignType = 'cross_sells';
        $assignProductType = 'bundle';
        list($linking, $forLinking) = $testData;
        $dataForBuy = $this->loadDataSet('Product', $assignProductType . '_options_to_add_to_shopping_cart', null,
                                         $linking[$assignProductType . 'Option']);
        $forLinking = $forLinking[$linkingType][$linkingType];
        $search = $this->loadDataSet('Product', 'product_search', $linking[$assignProductType]);
        $assign = $this->loadDataSet('Product', $assignType . '_1',
                                     array($assignType . '_search_name' => $forLinking['product_name'],
                                          $assignType . '_search_sku'   => $forLinking['product_sku']));
        //Steps
        $searchAssigned = $this->loadDataSet('Product', 'product_search', $forLinking);
        //Steps
        $this->navigate('manage_products');
        //Set product to 'Out of Stock';
        $this->productHelper()->openProduct($searchAssigned);
        $this->openTab('inventory');
        $this->fillDropdown('inventory_stock_availability', 'Out of Stock');
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        //Assign product
        $this->navigate('manage_products');
        $this->productHelper()->openProduct($search);
        $this->productHelper()->unselectAssociatedProduct($assignType);
        $this->productHelper()->assignProduct($assign, $assignType);
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        $this->assertSame($assignType, $this->_getActiveTabUimap()->getTabId(),
            'Wrong Tab is opened after saving product');
        $this->productHelper()->isAssignedProduct($assign, $assignType);
        $this->assertEmptyVerificationErrors();
        $this->clearInvalidedCache();
        $this->reindexInvalidedData();
        $this->frontend();
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->productHelper()->frontOpenProduct($linking[$assignProductType]['product_name']);
        $this->productHelper()->frontAddProductToCart($dataForBuy);
        $this->addParameter('productName', $forLinking['product_name']);
        if ($this->controlIsPresent('link', $assignType . '_product')) {
            $this->addVerificationMessage($assignType . ' product ' . $forLinking['product_name']
                                              . ' is on "' . $this->getCurrentPage() . '" page');
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Review Up-sell products(OutStock) on frontend assigned to bundle product.</p>
     * <p>Preconditions:</p>
     * <p>Created All Types of products (in stock) and realized next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Open product in stock; Attach all types of products to the product as Up-sell products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>Expected result:</p>
     * <p>The product does not contain block with Up-sell products</p>
     *
     * @param array $testData
     * @param string $linkingType
     *
     * @test
     * @dataProvider linkingTypeDataProvider
     * @depends preconditionsForTests
     */
    public function upSellsOutStock($linkingType, $testData)
    {
        //Data
        $assignType = 'up_sells';
        $assignProductType = 'bundle';
        list($linking, $forLinking) = $testData;
        $forLinking = $forLinking[$linkingType][$linkingType];
        $search = $this->loadDataSet('Product', 'product_search', $linking[$assignProductType]);
        $assign = $this->loadDataSet('Product', $assignType . '_1',
                                     array($assignType . '_search_name' => $forLinking['product_name'],
                                          $assignType . '_search_sku'   => $forLinking['product_sku']));
        //Steps
        $searchAssigned = $this->loadDataSet('Product', 'product_search', $forLinking);
        //Steps
        $this->navigate('manage_products');
        //Set product to 'Out of Stock';
        $this->productHelper()->openProduct($searchAssigned);
        $this->openTab('inventory');
        $this->fillDropdown('inventory_stock_availability', 'Out of Stock');
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        //Assign product
        $this->navigate('manage_products');
        $this->productHelper()->openProduct($search);
        $this->productHelper()->unselectAssociatedProduct($assignType);
        $this->productHelper()->assignProduct($assign, $assignType);
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        $this->assertSame($assignType, $this->_getActiveTabUimap()->getTabId(),
            'Wrong Tab is opened after saving product');
        $this->productHelper()->isAssignedProduct($assign, $assignType);
        $this->assertEmptyVerificationErrors();
        $this->clearInvalidedCache();
        $this->reindexInvalidedData();
        $this->productHelper()->frontOpenProduct($linking[$assignProductType]['product_name']);
        $this->addParameter('productName', $forLinking['product_name']);
        if ($this->controlIsPresent('link', $assignType . '_product')) {
            $this->addVerificationMessage($assignType . ' product ' . $forLinking['product_name']
                                              . ' is on "' . $this->getCurrentPage() . '" page');
        }
        $this->assertEmptyVerificationErrors();
    }

    public function linkingTypeDataProvider()
    {
        return array(
            array('simple'),
            array('virtual'),
            array('downloadable'),
            array('bundle'),
            array('configurable'),
            array('grouped')
        );
    }
}
