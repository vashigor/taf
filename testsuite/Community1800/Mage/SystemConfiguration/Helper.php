<?php

/**
 * This method is overriden to support Magento 1.8.0.0 configuration tabs.
 *
 * @author igor
 */
class Community1800_Mage_SystemConfiguration_Helper extends Community1701_Mage_SystemConfiguration_Helper
{

    /**
     * Get attribute value(like @id, @class) in tab xpath
     * This method is overriden to support Magento 1.8.0.0 configuration tabs.
     *
     * @param string $tabName
     * @param string $attribute
     *
     * @return string
     * @throws OutOfRangeException
     * @see Mage_Selenium_TestCase::getTabAttribute()
     */
    public function getTabAttribute($tabName, $attribute)
    {
        $tabXpath = $this->_getControlXpath('tab', $tabName);
        list($present,$result) = $this->_checkAndGetAttribute1800($tabXpath, $attribute);
        return $present ? $result : parent::getTabAttribute($tabName, $attribute);
    }

    /**
     * Get part of UIMap for opened tab
     * This method is overriden to support Magento 1.8.0.0 configuration tabs.
     *
     * @return Mage_Selenium_Uimap_Tab
     * @see Mage_Selenium_TestCase::_getActiveTabUimap()
     */
    protected function _getActiveTabUimap()
    {
        $tabData = $this->getCurrentUimapPage()->getAllTabs($this->_paramsHelper);
        foreach ($tabData as $tabUimap) {
            $tabXpath = $tabUimap->getXpath();
            list($present,$result) = $this->_checkAndGetAttribute1800($tabXpath, 'class');
            $isTabOpened = $present ? $result : '';
            if (preg_match('/active/', $isTabOpened)) {
                return $tabUimap;
            }
        }
        return parent::_getActiveTabUimap();
    }

    /**
     * Check if attribute presented and return its value.
     *
     * @param unknown $tabXpath
     * @param unknown $attribute
     * @return boolean string|NULL
     */
    protected function _checkAndGetAttribute1800( $tabXpath , $attribute )
    {
        $present = false;
        $result = null;
        if ($this->isElementPresent($tabXpath . '//span[@' . $attribute . ']')) {
            $present = true;
            $result = $this->getAttribute($tabXpath . '//span' . '@' . $attribute);
        }
        return array($present,$result);
    }

}