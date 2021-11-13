<?php
/**
 * @package products_location_module
* @copyright Portions Copyright 2015 Mark Brittain
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: products_location.php 1.0 2019-02-20 Mark Brittain $
*/
define('TABLE_PRODUCTS_LOCATION', DB_PREFIX . 'products_location');

$sanitizer = AdminRequestSanitizer::getInstance();
$group = array(
    'products_location' => array('sanitizerType' => 'FILE_DIR_REGEX', 'method' => 'post'),
);
$sanitizer->addComplexSanitization($group);