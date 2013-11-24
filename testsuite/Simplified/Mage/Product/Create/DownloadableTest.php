<?php

/**
 * Downloadable product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Simplified_Mage_Product_Create_DownloadableTest extends Core_Mage_Product_Create_DownloadableTest
{

    /**
     * <p>Simplified list of empty fields.</p>
     *
     * @see Core_Mage_Product_Create_DownloadableTest::withRequiredFieldsEmptyDataProvider()
     */
    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('general_short_description', 'field'),
            array('prices_tax_class', 'dropdown')
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     *
     * @see Core_Mage_Product_Create_DownloadableTest::invalidQtyDataProvider()
     */
    public function invalidQtyDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:'))
        );
    }
    
    /**
     * <p>Simplified list of empty fields for sample data.</p>
     *
     * @see Core_Mage_Product_Create_DownloadableTest::emptyFieldForSamplesDataProvider()
     */
    public function emptyFieldForSamplesDataProvider()
    {
        return array(
            array('downloadable_sample_row_title')
        );
    }

    /**
     * <p>Simplified list of empty fields for link data.</p>
     *
     * @see Core_Mage_Product_Create_DownloadableTest::emptyFieldForLinksDataProvider()
     */
    public function emptyFieldForLinksDataProvider()
    {
        return array(
            array('downloadable_link_row_file_url')
        );
    }

    /**
     * <p>Simplified list of invalid numeric fields.</p>
     *
     * @see Core_Mage_Product_Create_DownloadableTest::invalidQtyDataProvider()
     */
    public function invalidNumericFieldDataProvider()
    {
        return array(
            array('-128')
        );
    }
    
}