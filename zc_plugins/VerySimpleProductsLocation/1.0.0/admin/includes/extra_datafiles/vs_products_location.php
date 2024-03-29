<?php
/**
 * @package products_location_module
* @copyright Portions Copyright 2015 Mark Brittain
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: products_location.php 1.0 2019-02-20 Mark Brittain $
*/
define('TABLE_VS_PRODUCTS_LOCATION', DB_PREFIX . 'very_simple_products_location');

$sanitizer = AdminRequestSanitizer::getInstance();
$group = array(
    'vs_products_location' => array('sanitizerType' => 'ALPHANUM_DASH_UNDERSCORE', 'method' => 'post'),
);
$sanitizer->addComplexSanitization($group);