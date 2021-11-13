<?php

// Create Table products_location
$db->Execute('CREATE TABLE IF NOT EXISTS ' . TABLE_PRODUCTS_LOCATION . ' (
		products_id int(11) NOT NULL,
		products_location varchar(10) NOT NULL,
		PRIMARY KEY (products_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ');

// Fill with blank locations Insert ignore will not overwrite any existing records.
$db->Execute('INSERT  IGNORE INTO ' . TABLE_PRODUCTS_LOCATION . ' (products_id, products_location)
SELECT products_id,"" FROM ' . TABLE_PRODUCTS );

//
$ProductLocation_PageExists = false;

// Attempt to use the ZC function to test for the existence of the page otherwise detect using SQL.
if (function_exists('zen_page_key_exists'))
{
    $ProductLocation_PageExists = zen_page_key_exists('config' . $admin_page);
} else {
    $ProductLocation_PageExists_result = $db->Execute("SELECT FROM " . TABLE_ADMIN_PAGES . " WHERE page_key = 'config" . $admin_page . "' LIMIT 1");
    if ($ProductLocation_PageExists_result->EOF && $ProductLocation_PageExists_result->RecordCount() == 0) {
    } else {
        $ProductLocation_PageExists = true;
    }
}

// if the admin page is not installed, then insert it using either the ZC function or straight SQL.
if (!$ProductLocation_PageExists)
{
    if ((int)$configuration_group_id > 0 ) {

        $page_sort_query = "SELECT MAX(sort_order) + 1 as max_sort FROM `". TABLE_ADMIN_PAGES ."` WHERE menu_key='configuration'";
        $page_sort = $db->Execute($page_sort_query);
        $page_sort = $page_sort->fields['max_sort'];

        zen_register_admin_page('config' . $admin_page,
            'BOX_CONFIGURATION_' . str_replace(' ', '_', strtoupper($module_name)),
            'FILENAME_CONFIGURATION',
            'gID=' . $configuration_group_id,
            'configuration',
            'Y',
            $page_sort);

        $messageStack->add('Enabled ' . $module_name . ' Configuration Menu.', 'success');
    }
}
// Initialize the variable.
$sort_order = array();

/*
 * Add Values to Products Location Configuration Group (Admin > Configuration > Products Location)
 *   Identify the order in which the keys should be added for display.
 */
$sort_order = array(

    array('configuration_group_id' => array('value' => $configuration_group_id, 'type' => 'integer'),
        'configuration_key' => array('value' => $module_constant . '_PLUGIN_CHECK', 'type' => 'string'),
        'configuration_title' => array('value' => $module_name . ' (Update Check)', 'type' => 'string'),
        'configuration_value' => array('value' => SHOW_VERSION_UPDATE_IN_HEADER, 'type' => 'string'),
        'configuration_description' => array('value' => 'Allow version checking if Zen Cart version checking enabled<br/><br/>If false, no version checking performed.<br/>If true, then only if Zen Cart version checking is on:',
            'type' => 'string'),
        'date_added' => array('value' => 'NOW()', 'type' => 'noquotestring'),
        'use_function' => array('value' => 'NULL', 'type' => 'noquotestring'),
        'set_function' => array('value' => 'zen_cfg_select_option(array(\'true\', \'false\'),', 'type' => 'string'),
    ),
    array('configuration_group_id' => array('value' => $configuration_group_id, 'type' => 'integer'),
        'configuration_key' => array('value' => $module_constant . '_VERSION', 'type' => 'string'),
        'configuration_title' => array('value' => $module_name . '<b> Version</b>', 'type' => 'string'),
        'configuration_value' => array('value' => '0.0.0', 'type' => 'string'),
        'configuration_description' => array('value' => $module_name . ' Version', 'type' => 'string'),
        'date_added' => array('value' => 'NOW()', 'type' => 'noquotestring'),
        'use_function' => array('value' => 'NULL', 'type' => 'noquotestring'),
        'set_function' => array('value' => 'zen_cfg_select_option(array(\'0.0.0\'),', 'type' => 'string'),
    ),
    array('configuration_group_id' => array('value' => $configuration_group_id, 'type' => 'integer'),
        'configuration_key' => array('value' => 'PRODUCTS_LOCATION_PACKING_DISPLAY', 'type' => 'string'),
        'configuration_title' => array('value' => 'Display location on Packing Slip', 'type' => 'string'),
        'configuration_value' => array('value' => 'true', 'type' => 'string'),
        'configuration_description' => array('value' => '<br />If true, the products location will be displayed on the Packing Slip.<br /><b>Default: true</b>',
            'type' => 'string'),
        'date_added' => array('value' => 'NOW()', 'type' => 'noquotestring'),
        'use_function' => array('value' => 'NULL', 'type' => 'noquotestring'),
        'set_function' => array('value' => 'zen_cfg_select_option(array(\'true\', \'false\'),', 'type' => 'string'),
    ),
    array('configuration_group_id' => array('value' => $configuration_group_id, 'type' => 'integer'),
        'configuration_key' => array('value' => 'PRODUCTS_LOCATION_INVOICE_DISPLAY', 'type' => 'string'),
        'configuration_title' => array('value' => 'Display location on invoice', 'type' => 'string'),
        'configuration_value' => array('value' => 'true', 'type' => 'string'),
        'configuration_description' => array('value' => '<br />If true, the products location will be displayed on the invoice.<br /><b>Default: true</b>',
            'type' => 'string'),
        'date_added' => array('value' => 'NOW()', 'type' => 'noquotestring'),
        'use_function' => array('value' => 'NULL', 'type' => 'noquotestring'),
        'set_function' => array('value' => 'zen_cfg_select_option(array(\'true\', \'false\'),', 'type' => 'string'),
    ),
    array('configuration_group_id' => array('value' => $configuration_group_id, 'type' => 'integer'),
        'configuration_key' => array('value' => 'PRODUCTS_LOCATION_CATEGORY_DISPLAY', 'type' => 'string'),
        'configuration_title' => array('value' => 'Display location on catalog products category', 'type' => 'string'),
        'configuration_value' => array('value' => 'true', 'type' => 'string'),
        'configuration_description' => array('value' => '<br />If true, the products location will be displayed on the catalog products categories display when desplaying lists of products.<br /><b>Default: true</b>',
            'type' => 'string'),
        'date_added' => array('value' => 'NOW()', 'type' => 'noquotestring'),
        'use_function' => array('value' => 'NULL', 'type' => 'noquotestring'),
        'set_function' => array('value' => 'zen_cfg_select_option(array(\'true\', \'false\'),', 'type' => 'string'),
    ),
    array('configuration_group_id' => array('value' => $configuration_group_id, 'type' => 'integer'), 'configuration_key' => array('value' => 'PRODUCTS_LOCATION_SORT_DISPLAY', 'type' => 'string'),
        'configuration_title' => array('value' => 'Sort Invoice and packing slipt by location', 'type' => 'string'),
        'configuration_value' => array('value' => 'true', 'type' => 'string'),
        'configuration_description' => array('value' => '<br>If true, the Invoice and the Picking list will be sorted by products location.<br><b>Default: true</b>',
            'type' => 'string'),
        'date_added' => array('value' => 'NOW()', 'type' => 'noquotestring'),
        'use_function' => array('value' => 'NULL', 'type' => 'noquotestring'),
        'set_function' => array('value' => 'zen_cfg_select_option(array(\'true\', \'false\'),', 'type' => 'string'),
    ));

foreach ($sort_order as $config_key => $config_item) {

    $sql = "INSERT IGNORE INTO " . TABLE_CONFIGURATION . " (configuration_group_id, configuration_key, configuration_title, configuration_value, configuration_description, sort_order, date_added, use_function, set_function)
          VALUES (:configuration_group_id:, :configuration_key:, :configuration_title:, :configuration_value:, :configuration_description:, :sort_order:, :date_added:, :use_function:, :set_function:)
          ON DUPLICATE KEY UPDATE configuration_group_id = :configuration_group_id:, sort_order = :sort_order:";
    $sql = $db->bindVars($sql, ':configuration_group_id:', $config_item['configuration_group_id']['value'], $config_item['configuration_group_id']['type']);
    $sql = $db->bindVars($sql, ':configuration_key:', $config_item['configuration_key']['value'], $config_item['configuration_key']['type']);
    $sql = $db->bindVars($sql, ':configuration_title:', $config_item['configuration_title']['value'], $config_item['configuration_title']['type']);
    $sql = $db->bindVars($sql, ':configuration_value:', $config_item['configuration_value']['value'], $config_item['configuration_value']['type']);
    $sql = $db->bindVars($sql, ':configuration_description:', $config_item['configuration_description']['value'], $config_item['configuration_description']['type']);
    $sql = $db->bindVars($sql, ':sort_order:', ((int)$config_key + 1) * 10, 'integer');
    $sql = $db->bindVars($sql, ':date_added:', $config_item['date_added']['value'], $config_item['date_added']['type']);
    $sql = $db->bindVars($sql, ':use_function:', $config_item['use_function']['value'], $config_item['use_function']['type']);
    $sql = $db->bindVars($sql, ':set_function:', $config_item['set_function']['value'], $config_item['set_function']['type']);
    $db->Execute($sql);
}

