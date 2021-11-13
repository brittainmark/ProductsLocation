<?php
/*
 *
 * @package zencart_auto_installer
 * @copyright Copyright 2003-2015 ZenCart.Codes a Pro-Webs Company
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: products_location.php 1.0 2019-02-20 Mark Brittain $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$autoLoadConfig[190][] = array('autoType'=>'class',
    'classPath' => 'include/classes/observers/',
    'loadFile'=>'product_location.php');

$autoLoadConfig[190][] = array('autoType'=>'classInstantiate',
    'className'=>'ProductsLocation',
    'objectName'=>'ProductsLocation');


$autoLoadConfig[999][] = array(
  'autoType' => 'init_script',
  'loadFile' => 'init_products_location.php'
);