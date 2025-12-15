=== WP Link Status Pro ===

Stable tag: 1.0.6
Tested up to: WP 5.1
Requires at least: WP 3.4
License: GPLv2 or later
Author: Pau Iglesias, SeedPlugins

https://seedplugins.com/wp-link-status/

== Changelog ==

= 1.0.6 =
March 16th, 2019

* Show a message in activation if any other WP Link Status version is active
* Changed the way a constant is checked to avoid old PHP versions issues
* Fixed bug filtering elements attributes on extracting content data
* More info about debug behavior in the constants.php file

= 1.0.5 =
March 7th, 2019

* Tested with PHP 7.0 and PHP 7.2
* Tested with WP 5.x and Gutenberg
* Coding style corrections
* Fixed several soft bugs
* Allow activation/deactivation/uninstall without admin area restriction
* Prevent network-wide plugin activation for multisite installs
* Remove threads with off status to avoid TEXT field overflow
* More time to HTTP CURL spawn method to run
* Improvements in debug and trace mode

= 1.0.4 =
September 11th, 2016

* Fixed cURL options to avoid problems with GoDaddy hosted sites

= 1.0.3 =
August 7th, 2016

* Changed permissions for generated files to avoid hosting conflicts like Hostgator.
* Fixed crawling process issues when running on HTTPS sites with or without valid certificate.

= 1.0.2 =
May 30th, 2016

* Solved bug for missing Custom Post Types when saving scan data.
* Solved bug enabling Custom Post Types under scan crawling.

= 1.0.1 =
February 25th, 2016

* Solved config bug when saving a running scan.
* Security improvements for nonces system.

= 1.0 =
Release Date: February 11th, 2016

* First and tested released until WordPress 4.4.2
* Tested code from WordPress 3.4 version.