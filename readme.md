Simplified test-set for Magento 1.8.0.0 (extended MTAF)
=======================================================

This package contains:
-------------------------
1. tests/phpunit.xml.dist with testsuite preset that enables Simplified mode (481 tests).
2. Payment tests that are simplified and don’t require configuration of external services (PayPal, 3D Secure).
3. Magento 1.8.0.0 compatibility.

Purpose: 
------------------------
I need a test set without excessive cases, that allows to check the most important nodes of Magento 1.8 after I make a customization or extension. Also I need to run these tests locally without configuration of any external service.

Configuration:
------------------------
Selenium server should be configured and run (This article can be used by debian\ubuntu users: http://theplacefor.blogspot.com/2013/10/headless-selenium-server-and-phpunit-on.html).

1. One have to copy the directory with MTAF to your server with hosts.
Assume, you get something like this:
    /srv/www/m1800proj1
    /srv/www/m1800proj2
    /srv/www/MTAF

2. One should copy and rename directory MTAF/tests.dist to all your projects. And rename it to tests.
    /srv/www/m1800proj1/tests <-- to
    /srv/www/m1800proj1/app
    ...
    /srv/www/m1800proj2/tests <-- to
    /srv/www/m1800proj2/app
    ...
    /srv/www/MTAF/tests.dist <-- from

3. One have to open every tests folder and copy tests/bootstrap.php.dist to tests/bootstrap.php
Then one have to open it and replace all occurrences of [PATH_TO_MTAF] with path to your MTAF (In our example this path is /srv/www/MTAF/ ).

4. Then one should copy tests/phpunit.xml.dist to tests/phpunit.xml. He or she has to open the new file and replace all occurrences of [PATH_TO_MTAF] with path to the MTAF (In our example this path is /srv/www/MTAF/ ).
One can also exclude or replace some tests here. Every category of tests has its own test suite.

5. One should copy tests/config/config.yml.dist to tests/config/config.yml.
The values of “host:” and “port:” fields should be changed with real ones in the new file. Fields “url” should be configured also with urls of Magento site one’s going to test.

6. One should make sure that his or her user has write permissions for the tests/var folder with all its sub-folders.

7. Then one can run phpunit command within tests directory to start testing.
