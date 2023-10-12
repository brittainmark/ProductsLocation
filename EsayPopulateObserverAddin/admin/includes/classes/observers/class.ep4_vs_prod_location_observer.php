<?php
/* This is an observer class to take action when the vs_product_location is processed/determined.
  It is expected to set the variables needed within EP4 to support inserting the location of the product, updating is not currently supported.
 */

class ep4_vs_prod_location extends base
{

    public function __construct()
    {
        $attachThis = [];
        $attachThis[] = 'EP4_EASYPOPULATE_4_LINK';
        $attachThis[] = 'EP4_DISPLAY_STATUS';
        $attachThis[] = 'EP4_IMPORT_START';
        $attachThis[] = 'EP4_IMPORT_GENERAL_FILE_ALL';
        $attachThis[] = 'EP4_IMPORT_PRODUCT_DEFAULT_SELECT_FIELDS';
        $attachThis[] = 'EP4_IMPORT_PRODUCT_DEFAULT_SELECT_TABLES';
        $attachThis[] = 'EP4_IMPORT_AFTER_CATEGORY';
        $attachThis[] = 'EP4_IMPORT_FILE_NEW_PRODUCT_PRODUCT_TYPE';
        $attachThis[] = 'EP4_IMPORT_FILE_PRODUCTS_DESCRIPTION_ADD_OR_CHANGE_DATA';
        $attachThis[] = 'EP4_EXTRA_FUNCTIONS_SET_FILELAYOUT_FULL_FILELAYOUT';
        $attachThis[] = 'EP4_EXTRA_FUNCTIONS_SET_FILELAYOUT_FULL_SQL_SELECT';
        $attachThis[] = 'EP4_EXTRA_FUNCTIONS_SET_FILELAYOUT_FULL_SQL_TABLE';

        $this->attach($this, $attachThis);
    }

    public function update(&$callingClass, $notifier, $p1, &$p2, &$p3, &$p4, &$p5, &$p6)
    {
        global $ep_supported_mods;
        switch ($notifier) {
            // EP4_EASYPOPULATE_4_LINK
            // This was the first opportunity where this information could be defined before
            //  being used and after the array had been declared/defined.
            case ('EP4_EASYPOPULATE_4_LINK'):
            // Needed as EP4_EASYPOPULATE_4_LINK not called on Import
            case ('EP4_IMPORT_START'):
                if (empty($ep_supported_mods)) {
                    $ep_supported_mods = [];
                } elseif (array_key_exists('vspl', $ep_supported_mods)) {
                    return;
                }
                $ep_supported_mods['vspl'] = defined('TABLE_VS_PRODUCTS_LOCATION');
        }
    }

    // EP4_DISPLAY_STATUS
    public function updateEp4DisplayStatus(&$callingClass, $notifier)
    {
        global $ep_supported_mods;

        echo 'Very Simple Product Location mod:' . ($ep_supported_mods['vspl'] ? '<font color="green">TRUE</font>' : "FALSE") . '<br/>';
    }

    // EP4_IMPORT_GENERAL_FILE_ALL
    public function updateEp4ImportGeneralFileAll(&$callingClass, $notifier)
    {
        global $ep_supported_mods;

        if (empty($ep_supported_mods['vspl'])) { // Products Location Mod
            return;
        }

        global $default_these;
        $default_these[] = 'v_vs_products_location';
    }

    // EP4_IMPORT_PRODUCT_DEFAULT_SELECT_FIELDS
    public function updateEp4ImportProductDefaultSelectFields(&$callingClass, $notifier)
    {
        global $ep_supported_mods;

        if (empty($ep_supported_mods['vspl'])) {
            return;
        }

        global $sql;
        // Products Location Mod
        $sql .= "pl.vs_products_location as v_vs_products_location,";
    }

    // EP4_IMPORT_PRODUCT_DEFAULT_SELECT_TABLES
    public function updateEp4ImportProductDefaultSelectTables(&$callingClass, $notifier)
    {
        global $ep_supported_mods;

        if (empty($ep_supported_mods['vspl'])) { // Products Location Mod
            return;
        }

        global $sql, $items, $filelayout;
        $sql .= " LEFT JOIN " . TABLE_VS_PRODUCTS_LOCATION . " AS pl ON (pl.products_id = p.products_id) ";
    }

    // EP4_IMPORT_AFTER_CATEGORY
    public function updateEp4ImportAfterCategory()
    {
        $this->currentProductStatus = 'UPDATE';
    }

    // EP4_IMPORT_FILE_NEW_PRODUCT_PRODUCT_TYPE
    public function updateEp4ImportFileNewProductProductType(&$callingClass, $notifier)
    {
        $this->currentProductStatus = 'INSERT';

        return;

        global $ep_supported_mods;

        if (empty($ep_supported_mods['vspl'])) { // Products Location Mod
            return;
        }
        global $db;

        $plquery = "INSERT INTO " . TABLE_VS_PRODUCTS_LOCATION . " SET
                        products_id = :products_id: ,
                        vs_products_location = :vs_products_location: ";
        $plquery = $db->bindVars($plquery, ':products_id:', $v_products_id, 'integer');
        $plquery = $db->bindVars($plquery, ':vs_products_location:', $v_vs_products_location, 'string');
        $plresult = ep_4_query($plquery);
        if ($plresult === false) {
            $query = "DELETE " . TABLE_PRODUCTS . " WHERE products_id = :products_id:";
            $query = $db->bindVars($query, ':products_id:', $v_products_id, 'integer');
            $result = ep_4_query($query);
            $display_output .= sprintf(EASYPOPULATE_4_DISPLAY_RESULT_NEW_PRODUCT_FAIL, $v_products_model);
            $ep_error_count++;
            // new categories however have been created by now... Adding into product table needs to be 1st action?
        }
    }

    //$this->currentStatus = 'UPDATE';
    // EP4_IMPORT_FILE_PRODUCTS_DESCRIPTION_ADD_OR_CHANGE_DATA
    public function updateEp4ImportFileProductsDescriptionAddOrChangeData(&$callingClass, $notifier)
    {
        global $ep_supported_mods;

        if (empty($ep_supported_mods['vspl'])) { // Products Location Mod
            return;
        }
        global $db, $v_products_id, $v_vs_products_location;
        if (empty($this->currentProductStatus)) {
            return;
        } elseif ($this->currentProductStatus === 'UPDATE') {
            $plquery = "REPLACE INTO " . TABLE_VS_PRODUCTS_LOCATION . " SET
                            products_id = :products_id: ,
                            vs_products_location = :vs_products_location: ";
        } else {
            $plquery = "INSERT IGNORE INTO " . TABLE_VS_PRODUCTS_LOCATION . " SET
                            products_id = :products_id: ,
                            vs_products_location = :vs_products_location: ";
        }
        $plquery = $db->bindVars($plquery, ':products_id:', $v_products_id, 'integer');
        $plquery = $db->bindVars($plquery, ':vs_products_location:', $v_vs_products_location, 'string');
        $plresult = ep_4_query($plquery);
        unset($this->currentProductStatus);

        if (!empty($plresult)) {
            return;
        }

        // Provide error notification
        global $display_output, $v_products_model, $ep_error_count;

        $display_output .= sprintf(EASYPOPULATE_4_DISPLAY_RESULT_NEW_PRODUCT_FAIL, $v_products_model);
        $ep_error_count++;
    }

    // EP4_EXTRA_FUNCTIONS_SET_FILELAYOUT_FULL_FILELAYOUT
    public function updateEp4ExtraFunctionsSetFilelayoutFullFilelayout(&$callingClass, $notifier)
    {
        global $ep_supported_mods;

        if (empty($ep_supported_mods['vspl'])) { // Products Location Mod
            return;
        }

        global $filelayout;

        $filelayout[] = 'v_vs_products_location';
    }

    // EP4_EXTRA_FUNCTIONS_SET_FILELAYOUT_FULL_SQL_SELECT
    public function updateEp4ExtraFunctionsSetFilelayoutFullSqlSelect(&$callingClass, $notifier)
    {
        global $ep_supported_mods;

        if (empty($ep_supported_mods['vspl'])) { // Products Location Mod
            return;
        }

        global $filelayout_sql;
        $filelayout_sql .= "pl.vs_products_location as v_vs_products_location,";
    }

    // EP4_EXTRA_FUNCTIONS_SET_FILELAYOUT_FULL_SQL_TABLE
    public function updateEp4ExtraFunctionsSetFilelayoutFullSqlTable(&$callingClass, $notifier)
    {
        global $ep_supported_mods;

        if (empty($ep_supported_mods['vspl'])) { // Products Location Mod
            return;
        }

        global $filelayout_sql;
        $filelayout_sql .= " LEFT JOIN " . TABLE_PRODUCTS_LOCATION . " as pl ON (p.products_id = p1.products_id) ";
    }
}
