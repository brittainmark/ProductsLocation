<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
  protected function executeInstall()
  {
      // check if Very Simple product location previously installed and get/set the gId and Sort value
      IF (defined ( 'VS_PRODUCTS_LOCATION_VERSION' )) {
          $version = VS_PRODUCTS_LOCATION_VERSION;
        $sql = 'SELECT configuration_group_id FROM ' . TABLE_CONFIGURATION . ' WHERE configuration_key = "VS_PRODUCTS_LOCATION_VERSION";';
        $this->executeInstallerSql($sql);
        $gid = $this->dbConn->fields['configuration_group_id'];
      }
      else {
        $version = '0.0.0';
        $sql =  'INSERT INTO ' . TABLE_CONFIGURATION_GROUP . ' (configuration_group_title, configuration_group_description, sort_order, visible)
          VALUES ("Very Simple Product Location", "Set Very Simple Product Location Options", "1", "1");';
        $gid = $this->dbConn->Insert_ID ();
        $this->executeInstallerSql($sql);
//set sort order to group id to podition at end.
        $sql =  'UPDATE ' . TABLE_CONFIGURATION_GROUP . ' SET sort_order = ' . $gid . ' WHERE configuration_group_id = ' . $gid . ';';
        $this->executeInstallerSql($sql);
//insert version into configuration table
        $sql = 'INSERT IGNORE INTO ' . TABLE_CONFIGURATION . ' (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES
          ("Version", "VS_PRODUCTS_LOCATION_VERSION", "0.0.0", "Version installed:", ' . $gid . ', 0, NOW(), NOW(), NULL, "zen_cfg_select_option(array(\'0.0.0\'),");';
      }

/*
 * deregister page just in case present
 */
      zen_deregister_admin_pages('configVSProductLocation');
/*
 * register page
 */
      zen_register_admin_page(
        'configVSProductLocation', 'BOX_CONFIGURATION_VS_PRODUCTS_LOCATION', 'FILENAME_CONFIGURATION', 'gID='.$gid, 'configuration', 'Y', $gid);
/*
 * Create table to hold product locations and initialise with empty values
 */
      $sql = 'CREATE TABLE IF NOT EXISTS ' . TABLE_VS_PRODUCTS_LOCATION . ' (
        products_id int(11) NOT NULL,
		vs_products_location varchar(10) NOT NULL,
		PRIMARY KEY (products_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;';
      $this->executeInstallerSql($sql);
// Fill with blank locations Insert ignore will not overwrite any existing records.
      $sql = 'INSERT IGNORE INTO ' . TABLE_VS_PRODUCTS_LOCATION . ' (products_id, vs_products_location)
        SELECT products_id,"" FROM ' . TABLE_PRODUCTS .';';
      $this->executeInstallerSql($sql);
/*
 * add configuration values
 */
      $sql = "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
            ( configuration_group_id, sort_order, date_added, configuration_title, configuration_key, configuration_value, configuration_description, date_added, use_function, set_function )
            VALUES
            ( $gid, 10, NOW(), 'VS_PRODUCTS_LOCATION_PLUGIN_CHECK', configuration_group_id, sort_order, 'Very Simple Product Location (Update Check)', " . SHOW_VERSION_UPDATE_IN_HEADER . ",  'Allow version checking if Zen Cart version checking enabled<br><br>If false, no version checking performed.<br>If true, then only if Zen Cart version checking is on:', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
            ( $gid, 20, NOW(), 'VS_PRODUCTS_LOCATION_VERSION', 'Very simple Product Location<b> Version</b>', '1.0.0', 'Very Simple Product Location Version', NULL, 'zen_cfg_select_option(array(\'1.0.0\'),'),
            ( $gid, 20, NOW(),  'VS_PRODUCTS_LOCATION_PACKING_DISPLAY', 'type' => 'string'), 'Display location on Packing Slip', 'true',  '<br>If true, the products location will be displayed on the Packing Slip.<br><b>Default: true</b>', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
            ( $gid, 20, NOW(), 'VS_PRODUCTS_LOCATION_INVOICE_DISPLAY', 'Display location on invoice',  'true', '<br>If true, the products location will be displayed on the invoice.<br><b>Default: true</b>', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
            ( $gid, 20, NOW(), 'VS_PRODUCTS_LOCATION_CATEGORY_DISPLAY', 'Display location on catalog products category', 'true', '<br>If true, the products location will be displayed on the catalog products categories display when desplaying lists of products.<br><b>Default: true</b>', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
            ( $gid, 20, NOW(), 'VS_PRODUCTS_LOCATION_SORT_DISPLAY', 'Sort Invoice and packing slipt by location', 'true', '<br>If true, the Invoice and the Picking list will be sorted by products location.<br><b>Default: true</b>', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),')";

      $this->executeInstallerSql($sql);
      
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['configProductLocation']);

        $deleteMap = "'VS_PRODUCTS_LOCATION_PLUGIN_CHECK','VS_PRODUCTS_LOCATION_VERSION','VS_PRODUCTS_LOCATION_PACKING_DISPLAY','VS_PRODUCTS_LOCATION_INVOICE_DISPLAY','VS_PRODUCTS_LOCATION_CATEGORY_DISPLAY','VS_PRODUCTS_LOCATION_SORT_DISPLAY'";
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN (" . $deleteMap . ")";
        $this->executeInstallerSql($sql);

        $sql = 'DROP TABLE IF EXISTS' . TABLE_VS_PRODUCTS_LOCATION .';';
        $this->executeInstallerSql($sql);
    }
}