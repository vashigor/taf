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
 * Wishlist tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Wishlist_Wishlist extends Mage_Selenium_TestCase
{
    protected function tearDownAfterTest()
    {
        $this->frontend();
        if ($this->controlIsPresent('link', 'log_out')) {
            $this->navigate('my_wishlist');
            $this->wishlistHelper()->frontClearWishlist();
            $this->shoppingCartHelper()->frontClearShoppingCart();
            $this->logoutCustomer();
        }
    }

    /**
     * Create all types of products
     * @return array
     * @test
     * @skipTearDown
     */
    public function preconditionsForTests()
    {
        //Data
        $category = $this->loadDataSet('Category', 'sub_category_required');
        $catPath = $category['parent_category'] . '/' . $category['name'];
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_dropdown_with_options');
        $attrCode = $attrData['attribute_code'];
        $associatedAttributes = $this->loadDataSet('AttributeSet', 'associated_attributes',
            array('General' => $attrData['attribute_code']));
        $productCat = array('categories' => $catPath);
        $simple = $this->loadDataSet('Product', 'simple_product_visible', $productCat);
        $simple['general_user_attr']['dropdown'][$attrCode] = $attrData['option_1']['admin_option_name'];
        $virtual = $this->loadDataSet('Product', 'virtual_product_visible', $productCat);
        $virtual['general_user_attr']['dropdown'][$attrCode] = $attrData['option_2']['admin_option_name'];
        $download = $this->loadDataSet('SalesOrder', 'downloadable_product_for_order',
            array('downloadable_links_purchased_separately' => 'No',
                  'categories'                              => $catPath));
        $download['general_user_attr']['dropdown'][$attrCode] = $attrData['option_3']['admin_option_name'];
        $downloadWithOption = $this->loadDataSet('SalesOrder', 'downloadable_product_for_order', $productCat);
        $bundle = $this->loadDataSet('SalesOrder', 'fixed_bundle_for_order', $productCat,
            array('add_product_1' => $simple['general_sku'],
                  'add_product_2' => $virtual['general_sku']));
        $configurable = $this->loadDataSet('SalesOrder', 'configurable_product_for_order',
            array('configurable_attribute_title' => $attrData['admin_title'],
                  'categories'                   => $catPath), array('associated_1' => $simple['general_sku'],
                                                                     'associated_2' => $virtual['general_sku'],
                                                                     'associated_3' => $download['general_sku']));
        $grouped = $this->loadDataSet('SalesOrder', 'grouped_product_for_order', $productCat,
            array('associated_1' => $simple['general_sku'],
                  'associated_2' => $virtual['general_sku'],
                  'associated_3' => $download['general_sku']));
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        $configurableOptionName = $attrData['option_1']['store_view_titles']['Default Store View'];
        $customOptions = $this->loadDataSet('Product', 'custom_options_data');
        $simpleWithCustomOptions =
            $this->loadDataSet('Product', 'simple_product_visible', array('categories'          => $catPath,
                                                                          'custom_options_data' => $customOptions));
        //Steps and Verification
        $this->loginAdminUser();
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        $this->assertMessagePresent('success', 'success_attribute_set_saved');
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->createCategory($category);
        $this->assertMessagePresent('success', 'success_saved_category');

        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($virtual, 'virtual');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($download, 'downloadable');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($downloadWithOption, 'downloadable');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($bundle, 'bundle');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($configurable, 'configurable');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($grouped, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($simpleWithCustomOptions);
        $this->assertMessagePresent('success', 'success_saved_product');

        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');
        $this->reindexInvalidedData();
        $this->flushCache();

        return array('productNames'       => array('simple'           => $simple['general_name'],
                                                   'virtual'          => $virtual['general_name'],
                                                   'bundle'           => $bundle['general_name'],
                                                   'downloadable'     => $download['general_name'],
                                                   'configurable'     => $configurable['general_name'],
                                                   'grouped'          => $grouped['general_name'],
                                                   'downloadable_opt' => $downloadWithOption['general_name']),
                     'configurableOption' => array('title'                 => $attrData['admin_title'],
                                                   'custom_option_dropdown'=> $configurableOptionName),
                     'groupedOption'      => array('subProduct_1' => $simple['general_name'],
                                                   'subProduct_2' => $virtual['general_name'],
                                                   'subProduct_3' => $download['general_name']),
                     'bundleOption'       => array('subProduct_1' => $simple['general_name'],
                                                   'subProduct_2' => $virtual['general_name'],
                                                   'subProduct_3' => $simple['general_name'],
                                                   'subProduct_4' => $virtual['general_name']),
                     'user'               => array('email'    => $userData['email'],
                                                   'password' => $userData['password']),
                     'withCustomOption'   => $simpleWithCustomOptions['general_name'],
                     'catName'            => $category['name'],
                     'catPath'            => $catPath);
    }

    /**
     * <p>Opens My Wishlist using the link in quick access bar</p>
     * <p>Steps:</p>
     * <p>1. Open home page</p>
     * <p>2. Click "My Wishlist" link</p>
     * <p>Expected result:</p>
     * <p>The wishlist is opened.</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function openMyWishlistViaQuickAccessLink($testData)
    {
        //Steps
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->clickControl('link', 'my_wishlist');
        //Verify
        $this->assertTrue($this->checkCurrentPage('my_wishlist'), $this->getParsedMessages());
    }

    /**
     * <p>Add products to Wishlist from Product Details page. For all types without additional options.</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to wishlist</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function addProductsWithoutAdditionalOptionsToWishlistFromProduct($testData)
    {
        //Steps and Verifying
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        foreach ($testData['productNames'] as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
            $this->assertMessagePresent('success', 'successfully_added_product');
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                'Product ' . $productName . ' is not added to wishlist.');
        }
        $this->navigate('my_wishlist');
        foreach ($testData['productNames'] as $productName) {
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                'Product ' . $productName . ' is not in the wishlist.');
        }
    }

    /**
     * <p>Add products to Wishlist from Category page. For all types without additional options.</p>
     * <p>Steps:</p>
     * <p>1. Open category</p>
     * <p>2. Find product</p>
     * <p>3. Add product to wishlist</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function addProductsWithoutAdditionalOptionsToWishlistFromCatalog($testData)
    {
        //Steps and Verifying
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        foreach ($testData['productNames'] as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromCatalogPage($productName, $testData['catName']);
            $this->assertMessagePresent('success', 'successfully_added_product');
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                'Product ' . $productName . ' is not added to wishlist.');
        }
        $this->navigate('my_wishlist');
        foreach ($testData['productNames'] as $productName) {
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                'Product ' . $productName . ' is not in the wishlist.');
        }
    }

    /**
     * <p>Removes all products from My Wishlist. For all product types</p>
     * <p>Steps:</p>
     * <p>1. Add products to the wishlist</p>
     * <p>2. Remove one product from the wishlist</p>
     * <p>Expected result:</p>
     * <p>The product is no longer in wishlist</p>
     * <p>3. Repeat for all products until the last one</p>
     * <p>4. Remove the last product from the wishlist</p>
     * <p>Expected result:</p>
     * <p>Message 'You have no items in your wishlist.' is displayed</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function removeProductsFromWishlist($testData)
    {
        //Steps and Verifying
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        foreach ($testData['productNames'] as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
            $this->assertMessagePresent('success', 'successfully_added_product');
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                'Product ' . $productName . ' is not added to wishlist.');
        }
        $this->navigate('my_wishlist');
        foreach ($testData['productNames'] as $productName) {
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                'Product ' . $productName . ' is not in the wishlist.');
        }
        $lastProductName = end($testData['productNames']);
        array_pop($testData['productNames']);
        foreach ($testData['productNames'] as $productName) {
            //Remove all except last
            $this->wishlistHelper()->frontRemoveProductsFromWishlist($productName);
            $this->assertTrue(is_array($this->wishlistHelper()->frontWishlistHasProducts($productName)),
                'Product ' . $productName . ' is in the wishlist, but should be removed.');
        }
        //Remove the last one
        $this->wishlistHelper()->frontRemoveProductsFromWishlist($lastProductName);
        $this->assertTrue($this->controlIsPresent('pageelement', 'no_items'), $this->getParsedMessages());
    }

    /**
     * <p>Adds products to Shopping Cart from Wishlist. For all product types without custom options
     *    (simple, virtual, downloadable)</p>
     * <p>Steps:</p>
     * <p>1. Empty the shopping cart</p>
     * <p>2. Add a product to the wishlist</p>
     * <p>3. Open the wishlist</p>
     * <p>4. Click 'Add to Cart' button for each product</p>
     * <p>Expected result:</p>
     * <p>The products are in the shopping cart</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function addProductsWithoutOptionsToShoppingCartFromWishlist($testData)
    {
        //Data
        $products = array($testData['productNames']['simple'], $testData['productNames']['downloadable'],
                          $testData['productNames']['virtual']);
        //Steps and Verifying
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        foreach ($products as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
            $this->assertMessagePresent('success', 'successfully_added_product');
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                'Product ' . $productName . ' is not added to wishlist.');
        }
        foreach ($products as $productName) {
            $this->navigate('my_wishlist');
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                'Product ' . $productName . ' is not in the wishlist.');
            $this->wishlistHelper()->frontAddToShoppingCartFromWishlist($productName);
            $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($productName),
                'Product ' . $productName . ' is not in the shopping cart.');
        }
    }

    /**
     * <p>Adds products to Shopping Cart from Wishlist. For all product types with additional options
     *    (downloadable, configurable, bundle, grouped)</p>
     * <p>Steps:</p>
     * <p>1. Empty the shopping cart</p>
     * <p>2. Add a product to the wishlist</p>
     * <p>3. Open the wishlist</p>
     * <p>4. Click 'Add to Cart' button for each product</p>
     * <p>Expected result:</p>
     * <p>The products are not in the shopping cart.
     *    Message 'Please specify the product's option(s) is displayed'</p>
     *
     * @param array $testData
     * @param string $product
     * @param string $message
     *
     * @test
     * @dataProvider productsWithOptionsNegativeDataProvider
     * @depends preconditionsForTests
     */
    public function addProductsWithOptionsToShoppingCartFromWishlistNegative($product, $message, $testData)
    {
        //Data
        $productName = $testData['productNames'][$product];
        //Steps and Verifying
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
        $this->assertMessagePresent('success', 'successfully_added_product');
        //Verifying
        $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
            'Product ' . $productName . ' is not in the wishlist.');
        $this->wishlistHelper()->frontAddToShoppingCartFromWishlist($productName);
        $this->assertTrue($this->checkCurrentPage('wishlist_configure_product'), $this->getMessagesOnPage());
        $this->assertMessagePresent('validation', 'specify_product_' . $message);
    }

    public function productsWithOptionsNegativeDataProvider()
    {
        return array(
            array('downloadable_opt', 'link'),
            array('configurable', 'config_option'),
            array('bundle', 'option'),
            array('grouped', 'quantity')
        );
    }

    /**
     * <p>Adds products to Shopping Cart from Wishlist. For all product types with additional options
     *    (downloadable, configurable, bundle, grouped)</p>
     * <p>Steps:</p>
     * <p>1. Empty the shopping cart</p>
     * <p>2. Add a product to the wishlist</p>
     * <p>3. Open the wishlist</p>
     * <p>4. Click 'Add to Cart' button for each product</p>
     * <p>Expected result:</p>
     * <p>The products are not in the shopping cart.
     *    Message 'Please specify the product's option(s) is displayed'</p>
     *
     * @param array $testData
     * @param string $product
     * @param string $option
     *
     * @test
     * @dataProvider productsWithOptionsDataProvider
     * @depends preconditionsForTests
     */
    public function addProductsWithOptionsToShoppingCartFromWishlist($product, $option, $testData)
    {
        //Data
        $productName = $testData['productNames'][$product];
        if (isset($testData[$product . 'Option'])) {
            if ($product == 'configurable') {
                $options = $this->loadDataSet('Product', $option, $testData[$product . 'Option']);
            } else {
                $options = $this->loadDataSet('Product', $option, null, $testData[$product . 'Option']);
            }
        } else {
            $options = $this->loadDataSet('Product', $option);
        }
        //Steps and Verifying
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName, $options);
        $this->assertMessagePresent('success', 'successfully_added_product');
        //Verifying
        $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
            'Product ' . $productName . ' is not in the wishlist.');
        $this->wishlistHelper()->frontAddToShoppingCartFromWishlist($productName);
        if ($product == 'grouped') {
            foreach ($testData[$product . 'Option'] as $name) {
                $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($name),
                    'Product ' . $name . ' is not in the shopping cart.');
            }
        } else {
            $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($productName),
                'Product ' . $productName . ' is not in the shopping cart.');
        }
    }

    public function productsWithOptionsDataProvider()
    {
        return array(
            array('downloadable_opt', 'downloadable_options_to_add_to_shopping_cart'),
            array('configurable', 'configurable_options_to_add_to_shopping_cart'),
            array('bundle', 'bundle_options_to_add_to_shopping_cart'),
            array('grouped', 'grouped_options_to_add_to_shopping_cart')
        );
    }

    /**
     * <p>Add all types of products to Wishlist from Shopping Cart.</p>
     * <p>Steps:</p>
     * <p>1. Add products to the shopping cart</p>
     * <p>2. Move the products to wishlist</p>
     * <p>3. Open the wishlist</p>
     * <p>Expected result:</p>
     * <p>Products are in the wishlist</p>
     *
     * @param array $testData
     * @param string $product
     * @param string $option
     *
     * @test
     * @dataProvider productsWithOptionsDataProvider
     * @depends preconditionsForTests
     */
    public function addProductWithOptionsToWishlistFromShoppingCart($product, $option, $testData)
    {
        //Data
        $productName = $testData['productNames'][$product];
        if (isset($testData[$product . 'Option'])) {
            if ($product == 'configurable') {
                $options = $this->loadDataSet('Product', $option, $testData[$product . 'Option']);
            } else {
                $options = $this->loadDataSet('Product', $option, null, $testData[$product . 'Option']);
            }
        } else {
            $options = $this->loadDataSet('Product', $option);
        }
        //Steps and Verifying
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->productHelper()->frontOpenProduct($productName);
        $this->productHelper()->frontAddProductToCart($options);
        if ($product == 'grouped') {
            foreach ($testData[$product . 'Option'] as $name) {
                $this->navigate('shopping_cart');
                $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($name),
                    'Product ' . $name . ' is not in the shopping cart.');
                $this->shoppingCartHelper()->frontMoveToWishlist($name);
                $this->navigate('my_wishlist');
                $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($name),
                    'Product ' . $name . ' is not in the wishlist.');
            }
        } else {
            $this->assertTrue($this->checkCurrentPage('shopping_cart'));
            $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($productName),
                'Product ' . $productName . ' is not in the shopping cart.');
            $this->shoppingCartHelper()->frontMoveToWishlist($productName);
            $this->navigate('my_wishlist');
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                'Product ' . $productName . ' is not in the wishlist.');
        }
    }

    /**
     * <p>Shares My Wishlist</p>
     * <p>Steps:</p>
     * <p>1. Add a product to the wishlist</p>
     * <p>2. Open My Wishlist</p>
     * <p>3. Click "Share Wishlist" button</p>
     * <p>4. Enter a valid email and a message</p>
     * <p>5. Click "Share Wishlist" button
     * <p>Expected result:</p>
     * <p>The success message is displayed</p>
     *
     * @param array $shareData
     * @param array $testData
     *
     * @test
     * @dataProvider shareWishlistDataProvider
     * @depends preconditionsForTests
     */
    public function shareWishlist($shareData, $testData)
    {
        //Setup
        $shareData = $this->loadDataSet('Wishlist', 'share_data', $shareData);
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($testData['productNames']['simple']);
        $this->assertMessagePresent('success', 'successfully_added_product');
        //Steps
        $this->wishlistHelper()->frontShareWishlist($shareData);
        //Verify
        $this->assertMessagePresent('success', 'successfully_shared_wishlist');
    }

    public function shareWishlistDataProvider()
    {
        return array(
            array(array('emails' => 'autotest@test.com', 'message' => 'autotest message')),
            array(array('message' => ''))
        );
    }

    /**
     * <p>Shares My Wishlist with invalid email(s) provided</p>
     * <p>Steps:</p>
     * <p>1. Add a product to the wishlist</p>
     * <p>2. Open My Wishlist</p>
     * <p>3. Click "Share Wishlist" button</p>
     * <p>4. Enter an invalid email and a message</p>
     * <p>5. Click "Share Wishlist" button
     * <p>Expected result:</p>
     * <p>An error message is displayed</p>
     *
     * @param string $emails
     * @param string $errorMessage
     * @param array $testData
     *
     * @test
     * @dataProvider withInvalidEmailDataProvider
     * @depends preconditionsForTests
     */
    public function withInvalidEmail($emails, $errorMessage, $testData)
    {
        //Setup
        $shareData = $this->loadDataSet('Wishlist', 'share_data', array('emails' => $emails));
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($testData['productNames']['simple']);
        $this->assertMessagePresent('success', 'successfully_added_product');
        $this->wishlistHelper()->frontShareWishlist($shareData);
        //Verify
        if ($errorMessage == 'invalid_emails') {
            $this->assertMessagePresent('validation', $errorMessage);
        } else {
            $this->assertMessagePresent('error', $errorMessage);
        }
    }

    public function withInvalidEmailDataProvider()
    {
        return array(
            array('email@@unknown-domain.com', 'invalid_emails_js'),
            array('.email@unknown-domain.com', 'invalid_emails')
        );
    }

    /**
     * <p>Shares My Wishlist with empty email provided</p>
     * <p>Steps:</p>
     * <p>1. Add a product to the wishlist</p>
     * <p>2. Open My Wishlist</p>
     * <p>3. Click "Share Wishlist" button</p>
     * <p>4. Enter an invalid email and a message</p>
     * <p>5. Click "Share Wishlist" button
     * <p>Expected result:</p>
     * <p>An error message is displayed</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function shareWishlistWithEmptyEmail($testData)
    {
        //Setup
        $shareData = $this->loadDataSet('Wishlist', 'share_data', array('emails' => ''));
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($testData['productNames']['simple']);
        $this->assertMessagePresent('success', 'successfully_added_product');
        //Steps
        $this->wishlistHelper()->frontShareWishlist($shareData);
        //Verify
        $this->assertMessagePresent('validation', 'required_emails');
    }

    /**
     * <p>Adds a product with custom options to Shopping Cart from Wishlist without selected options</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to wishlist</p>
     * <p>3. Open wishlist</p>
     * <p>4. Add product to Shopping Cart</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed. Product is added to Shopping Cart</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function addProductWithCustomOptionsToShoppingCartFromWishlist($testData)
    {
        $productName = $testData['withCustomOption'];
        $options = $this->loadDataSet('Product', 'custom_options_to_add_to_shopping_cart');
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName, $options);
        $this->assertMessagePresent('success', 'successfully_added_product');
        //Verifying
        $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
            'Product ' . $productName . ' is not in the wishlist.');
        $this->wishlistHelper()->frontAddToShoppingCartFromWishlist($productName);
        $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($productName),
            'Product ' . $productName . ' is not in the shopping cart.');
    }

    /**
     * <p>Add simple product with custom options to Wishlist from Shopping Cart.</p>
     * <p>Steps:</p>
     * <p>1. Add product to the shopping cart</p>
     * <p>2. Move the product to wishlist</p>
     * <p>3. Open the wishlist</p>
     * <p>Expected result:</p>
     * <p>Product is in the wishlist</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function addProductWithCustomOptionsToWishlistFromShoppingCart($testData)
    {
        //Data
        $productName = $testData['withCustomOption'];
        $options = $this->loadDataSet('Product', 'custom_options_to_add_to_shopping_cart');
        //Steps and Verifying
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->productHelper()->frontOpenProduct($productName);
        $this->productHelper()->frontAddProductToCart($options);
        $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($productName),
            'Product ' . $productName . ' is not in the shopping cart.');
        $this->shoppingCartHelper()->frontMoveToWishlist($productName);
        $this->navigate('my_wishlist');
        $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
            'Product ' . $productName . ' is not in the wishlist.');
    }

    /**
     * <p>Verifies that a guest cannot open My Wishlist.</p>
     * <p>Steps:</p>
     * <p>1. Logout customer</p>
     * <p>2. Navigate to My Wishlist</p>
     * <p>Expected result:</p>
     * <p>Guest is redirected to login/register page.</p>
     *
     * @test
     */
    public function guestCannotOpenWishlist()
    {
        //Steps
        $this->logoutCustomer();
        $this->clickControl('link', 'my_wishlist');
        //Verify
        $this->assertTrue($this->checkCurrentPage('customer_login'), $this->getParsedMessages());
    }

    /**
     * <p>Verifies that a guest cannot add a product to a wishlist.</p>
     * <p>Steps:</p>
     * <p>1. Logout customer</p>
     * <p>2. Open a product</p>
     * <p>3. Add products to the wishlist</p>
     * <p>Expected result:</p>
     * <p>Guest is redirected to login/register page.</p>
     *
     * @param array $testData
     *
     * @test
     * @depends preconditionsForTests
     */
    public function guestCannotAddProductToWishlist($testData)
    {
        //Steps
        $this->frontend();
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($testData['productNames']['simple']);
        //Verify
        $this->assertTrue($this->checkCurrentPage('customer_login'), $this->getParsedMessages());
        $this->customerHelper()->frontLoginCustomer($testData['user']);
        $this->assertMessagePresent('success', 'successfully_added_product');
    }
}