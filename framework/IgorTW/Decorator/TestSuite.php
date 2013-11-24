<?php

class IgorTW_Decorator_TestSuite extends PHPUnit_Framework_TestSuite
{

    static public function suite( $class )
    {
        if (file_exists('phpunit.xml')) {
            $path = realpath('phpunit.xml');
        }
        $config = PHPUnit_Util_Configuration::getInstance($path);
        $suite = $config->getTestSuiteConfiguration();

        $decorator = new IgorTW_Decorator_Manager($suite);
        $suite = new PHPUnit_Framework_TestSuite;
        $suite->addTest($decorator);
        return $suite;
    }

}