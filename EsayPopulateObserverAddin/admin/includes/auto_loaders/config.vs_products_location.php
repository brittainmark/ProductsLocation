<?php
/*
 *
 * @package zencart_auto_installer
 * @copyright Copyright 2003-2015 ZenCart.Codes a Pro-Webs Company
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 1.0 2019-02-20 Mark Brittain $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$autoLoadConfig[190][] = [
    'autoType'=>'class',
    'classPath' => 'includes/classes/observers/',
    'loadFile'=>'VerySimpleProductsLocation.php',
    ];

$autoLoadConfig[190][] = [
    'autoType'=>'classInstantiate',
    'className'=>'VerySimpleProductsLocation',
    'objectName'=>'VerySimpleProductsLocation'
    ];
