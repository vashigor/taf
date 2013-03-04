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
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * UIMap helper class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Helper_Uimap extends Mage_Selenium_Helper_Abstract
{
    /**
     * Array of files paths to fixtures
     * @var array
     */
    protected $_configFixtures = array();

    /**
     * Uimap data
     * @var array
     */
    protected $_uimapData = array();

    /**
     * Initialize process
     */
    protected function _init()
    {
        $this->_configFixtures = $this->getConfig()->getConfigFixtures();
        $config = $this->getConfig()->getHelper('config')->getConfigFramework();
        if ($config['load_all_uimaps']) {
            $this->_loadUimapData();
        }
    }

    /**
     * Load and merge data files
     * @return Mage_Selenium_Helper_Uimap
     */
    protected function _loadUimapData()
    {
        if ($this->_uimapData) {
            return $this;
        }
        //Form include elements array
        $uimapInclude =
            (isset($this->_configFixtures['uimapInclude'])) ? $this->_configFixtures['uimapInclude'] : array();
        $includeElements = array();
        foreach ($uimapInclude as $area => $files) {
            $includeElements[$area] = array();
            foreach ($files as $file) {
                $pages = $this->getConfig()->getHelper('file')->loadYamlFile($file);
                //Skip if file is empty
                if (!$pages) {
                    continue;
                }
                foreach ($pages as $content) {
                    //Skip if page content is empty
                    if (!$content) {
                        continue;
                    }
                    $this->_mergeUimapIncludes($includeElements[$area], $content);
                }
            }
        }
        //Form uimap files array
        $uimapFiles = array();
        foreach ($this->_configFixtures as $codePoolName => $codePoolData) {
            if ($codePoolName == 'uimapInclude') {
                continue;
            }
            foreach ($codePoolData as $type => $dataFiles) {
                if ($type == 'uimap') {
                    $uimapFiles[$codePoolName] = $dataFiles;
                }
            }
        }
        $this->loadAndMergeUimaps($uimapFiles, $includeElements);
        return $this;
    }

    /**
     * @param array $uimapFiles
     * @param array $includeElements
     *
     * @return void
     */
    public function loadAndMergeUimaps(array $uimapFiles, $includeElements)
    {
        //Uimaps loading for first project
        $codePoolNames = array_keys($uimapFiles);
        $baseCodePoolName = array_shift($codePoolNames);
        $baseUimapFiles = array_shift($uimapFiles);
        foreach ($baseUimapFiles as $area => $files) {
            foreach ($files as $file) {
                $pages = $this->getConfig()->getHelper('file')->loadYamlFile($file);
                foreach ($codePoolNames as $codePoolName) {
                    $loadedUimaps = array();
                    //Skip if area is not exist for current project
                    if (!isset($uimapFiles[$codePoolName][$area])) {
                        continue;
                    }
                    $additionalFile = str_replace($baseCodePoolName, $codePoolName, $file);
                    //Skip if file is not exist for current project
                    if (!in_array($additionalFile, $uimapFiles[$codePoolName][$area])) {
                        continue;
                    }
                    $additionalPages = $this->getConfig()->getHelper('file')->loadYamlFile($additionalFile);
                    $loadedUimaps[] = $additionalFile;
                    //Skip if file is empty for current project
                    if (!$additionalPages) {
                        continue;
                    }
                    if ($pages) {
                        foreach ($additionalPages as $pageName => $content) {
                            //Skip if page content is empty for current project
                            if (!$content) {
                                continue;
                            }
                            if (isset($pages[$pageName])) {
                                $this->_mergeUimapIncludes($pages[$pageName], $content);
                            } else {
                                $pages[$pageName] = $content;
                            }
                        }
                    } else {
                        $pages = $additionalPages;
                    }
                    $uimapFiles[$codePoolName][$area] = array_diff($uimapFiles[$codePoolName][$area], $loadedUimaps);
                }
                if (!$pages) {
                    continue;
                }
                foreach ($pages as $pageKey => $content) {
                    //Skip if page content is empty
                    if (!$content) {
                        continue;
                    }
                    if (isset($includeElements[$area])) {
                        $this->_mergeUimapIncludes($content, $includeElements[$area]);
                    }
                    $this->_uimapData[$area][$pageKey] = new Mage_Selenium_Uimap_Page($pageKey, $content);
                }
            }
        }
        if (!empty($uimapFiles)) {
            $this->loadAndMergeUimaps($uimapFiles, $includeElements);
        }
    }

    /**
     * Merge uimap elements
     *
     * @param array $replaceArrayTo
     * @param array $replaceArrayFrom
     *
     * @return array
     */
    protected function _mergeUimapIncludes(array &$replaceArrayTo, array $replaceArrayFrom)
    {
        foreach ($replaceArrayFrom as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists($key, $replaceArrayTo) && $replaceArrayTo[$key] != null) {
                    if (is_string($key)) {
                        $this->_mergeUimapIncludes($replaceArrayTo[$key], $value);
                    } else {
                        list($keyFrom) = array_keys($value);
                        $keysTo = array();
                        foreach ($replaceArrayTo as $number => $content) {
                            foreach ($replaceArrayTo[$number] as $name => $contentTo) {
                                $keysTo[$number] = $name;
                            }
                        }
                        if (in_array($keyFrom, $keysTo)) {
                            $ddd = array_search($keyFrom, $keysTo);
                            $this->_mergeUimapIncludes($replaceArrayTo[$ddd], $value);
                        } else {
                            $replaceArrayTo[] = $value;
                        }
                    }
                } else {
                    $replaceArrayTo[$key] = $value;
                }
            } else {
                if ($value == null) {
                    unset($replaceArrayTo[$key]);
                } else {
                    $replaceArrayTo[$key] = $value;
                }
            }
        }
    }

    /**
     * Retrieve array with UIMap data
     *
     * @param string $area Application area
     *
     * @return array
     * @throws OutOfRangeException
     */
    public function getAreaUimaps($area)
    {
        if (!array_key_exists($area, $this->_uimapData)) {
            throw new OutOfRangeException('UIMaps for "' . $area . '" area do not exist');
        }

        return $this->_uimapData[$area];
    }

    /**
     * Retrieve Page from UIMap data configuration by path
     *
     * @param string $area Application area
     * @param string $pageKey UIMap page key
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return Mage_Selenium_Uimap_Page
     * @throws OutOfRangeException
     */
    public function getUimapPage($area, $pageKey, $paramsDecorator = null)
    {
        $areaUimaps = $this->getAreaUimaps($area);
        if (!array_key_exists($pageKey, $areaUimaps)) {
            throw new OutOfRangeException('Cannot find page "' . $pageKey . '" in area "' . $area . '"');
        }
        $page = $areaUimaps[$pageKey];
        if ($paramsDecorator) {
            $page->assignParams($paramsDecorator);
        }
        return $page;
    }

    /**
     * Retrieve Page from UIMap data configuration by MCA
     *
     * @param string $area Application area
     * @param string $mca a part of current URL opened in browser
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return Mage_Selenium_Uimap_Page
     * @throws OutOfRangeException
     */
    public function getUimapPageByMca($area, $mca, $paramsDecorator = null)
    {
        $mca = trim($mca, ' /\\');
        $appropriatePages = array();
        foreach ($this->_uimapData[$area] as $page) {
            //Get mca without any modifications
            $pageMca = trim($page->getMca(new Mage_Selenium_Helper_Params()), ' /\\');
            if ($pageMca === false || $pageMca === null) {
                continue;
            }
            $pageMca = preg_quote($pageMca);
            if ($paramsDecorator) {
                $pageMca = $paramsDecorator->replaceParametersWithRegexp($pageMca);
            }
            if (preg_match(';^' . $pageMca . '$;', $mca)) {
                $appropriatePages[] = $page;
            }
        }
        if (!empty($appropriatePages)) {
            if (count($appropriatePages) == 1) {
                return array_shift($appropriatePages);
            }
            foreach ($appropriatePages as $page) {
                //Get mca with actual modifications
                $pageMca = trim($page->getMca($paramsDecorator), ' /\\');
                if ($pageMca === $mca) {
                    $page->assignParams($paramsDecorator);
                    return $page;
                }
            }
        }
        throw new OutOfRangeException('Cannot find page with mca "' . $mca . '" in "' . $area . '" area');
    }

    /**
     * Compares mca from current url and from area mca array
     *
     * @param string $mca
     * @param string $page_mca
     *
     * @deprecated
     *
     * @return bool
     */
    protected function _compareMcaAndPageMca($mca, $page_mca)
    {
        if (parse_url($page_mca, PHP_URL_PATH) == parse_url($mca, PHP_URL_HOST) . parse_url($mca, PHP_URL_PATH)) {
            parse_str(parse_url($mca, PHP_URL_QUERY), $mca_params);
            parse_str(parse_url($page_mca, PHP_URL_QUERY), $page_mca_params);
            if (array_keys($mca_params) == array_keys($page_mca_params)) {
                foreach ($page_mca_params as $key => $value) {
                    if ($mca_params[$key] != $value && $value != '%anyValue%') {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Get URL of the specified page
     *
     * @param string $area Application area
     * @param string $page UIMap page key
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return string
     */
    public function getPageUrl($area, $page, $paramsDecorator = null)
    {
        $baseUrl = $this->getConfig()->getHelper('config')->getBaseUrl();
        return $baseUrl . $this->getPageMca($area, $page, $paramsDecorator);
    }

    /**
     * Get Page Mca
     *
     * @param string $area Application area
     * @param string $page UIMap page key
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return mixed
     */
    public function getPageMca($area, $page, $paramsDecorator = null)
    {
        $pageUimap = $this->getUimapPage($area, $page, $paramsDecorator);
        return $pageUimap->getMca($paramsDecorator);
    }

    /**
     * Get XPath that opens the specified page on click
     *
     * @param string $area Application area
     * @param string $page UIMap page key
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return mixed
     */
    public function getPageClickXpath($area, $page, $paramsDecorator = null)
    {
        $pageUimap = $this->getUimapPage($area, $page, $paramsDecorator);
        return $pageUimap->getClickXpath($paramsDecorator);
    }
}
