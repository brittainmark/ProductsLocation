<?php

// -----
// An observer-class to delete, update and create entries from the very simple Products location table when a product is deleted, copied created or modified.
//
//
class VerySimpleProductsLocation extends base
{

    public function __construct()
    {
        $this->attach($this, array(
            'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS',
            'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT',
            'NOTIFY_PRODUCT_MUSIC_UPDATE_PRODUCT_END',
            'NOTIFY_PRODUCT_MUSIC_COPY_TO_CONFIRM_DUPLICATE',
            'NOTIFY_MODULES_COPY_TO_CONFIRM_DUPLICATE',
            'NOTIFY_MODULES_UPDATE_PRODUCT_END',
            'NOTIFY_ADMIN_PROD_LISTING_HEADERS_B4_QTY',
            'NOTIFY_ADMIN_PROD_LISTING_PRODUCTS_QUERY',
            'NOTIFY_ADMIN_PROD_LISTING_DATA_B4_QTY'
        ));
    }

    public function update(&$class, $eventID, $p1, &$p2, &$p3, &$p4, &$p5, &$p6)
    {
        global $db;
        switch ($eventID) {
            case 'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT':
                // Delete from vs_products_location table
                if (defined('VS_PRODUCTS_LOCATION_VERSION')) {
                    $db->Execute("delete from " . TABLE_VS_PRODUCTS_LOCATION . "
				  where products_id = '" . (int) $p2 . "'");
                }
                break;
            case 'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS':
                // Update vs_products_location table
                //array(
                //    'label' => array(
                //        'text' => 'The label text',   (required)
                //        'field_name' => 'The name of the field associated with the label', (required)
                //        'addl_class' => {Any additional class to be applied to the label} (optional)
                //        'parms' => {Any additional parameters for the label, e.g. 'style="font-weight: 700;"} (optional)
                //    ),
                //    'input' => 'The HTML to be inserted' (required)
                // )
                $location =  (isset($p1->vs_products_location) ? $p1->vs_products_location :"");
                if (isset($_GET['pID']) && $p1->products_id !="") {
                        $products_id = $p1->products_id;
                        $location_query = 'SELECT products_location FROM ' . TABLE_PRODUCTS_LOCATION . ' WHERE products_id = ' . $products_id ;
                        $location_result =  $db->Execute($location_query);
                        $location = $location_result->fields['vs_products_location'] ;
                }
                $input =  zen_draw_input_field('vs_products_location', htmlspecialchars(stripslashes($location), ENT_COMPAT, CHARSET, TRUE),
                    zen_set_field_length(TABLE_VS_PRODUCTS_LOCATION, 'vs_products_location') . ' class="form-control"') ;
                $p2 = array('vs_products_location'=>array(
                       'label'=> array(
                         'text' => TEXT_VS_PRODUCTS_LOCATION,
                         'field_name' =>'vs_products_location'),
                       'input' => $input));
                break;
            case 'NOTIFY_MODULES_UPDATE_PRODUCT_END':
            case 'NOTIFY_PRODUCT_MUSIC_UPDATE_PRODUCT_END':
                // Update or insert new product location
                $sql_data_array = array('vs_products_location' => zen_db_prepare_input($_POST['vs_products_location']));
                $products_id = $p1['products_id'];
                $action = $p1['action'];
                switch ($action){
                    case 'insert_product':
                    $insert_sql_data = array('products_id' => $products_id);
                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
                    zen_db_perform(TABLE_VS_PRODUCTS_LOCATION, $sql_data_array);
                    break;
                    case 'update_product':
                        zen_db_perform(TABLE_VS_PRODUCTS_LOCATION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
                    break;
                }
                break;
            case 'NOTIFY_MODULES_COPY_TO_CONFIRM_DUPLICATE':
            case 'NOTIFY_PRODUCT_MUSIC_COPY_TO_CONFIRM_DUPLICATE':
                //Get the product id's
                $old_products_id = $p1['products_id'];
                $dup_products_id = $p1['dup_products_id'];
                /*
                //Duplicate a full list of categories
                $categories =  $db->Execute("SELECT categories_id
                                     FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                     WHERE products_id = '" . (int)$old_products_id . "'");
                while (!$categories->EOF) {
                    $categories_id=$categories->fields['categories_id'];
                    $db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
                          (products_id, categories_id)
                          values ('" . (int)$dup_products_id . "', '" . (int)$categories_id . "')");
                    //            $products_id = $dup_products_id;
                    $categories->MoveNext();
                }
                */
                //Copy the loaction
                $location = $db->Execute("SELECT vs_products_location
                                      FROM " . TABLE_VS_PRODUCTS_LOCATION . "
                                      WHERE products_id = '" . (int)$old_products_id . "'");
                $db->Execute("REPLACE INTO " . TABLE_VS_PRODUCTS_LOCATION . "
                          (products_id, vs_products_location)
                          VALUES ('" . (int)$dup_products_id . "',
                          '" . zen_db_input($location->fields['vs_products_location']) . "');");
                break;
            case 'NOTIFY_ADMIN_PROD_LISTING_HEADERS_B4_QTY':
                // Add header field to category products listing
                if (defined('PRODUCTS_LOCATION_CATEGORY_DISPLAY') && PRODUCTS_LOCATION_CATEGORY_DISPLAY == 'true') {
                    $alignment = 'right';
                    $value = TABLE_HEADING_LOCATION;
                    $header = array(
                        'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
                        'text' => $value
                    );
                    if ($p2 === false) $p2=array();
                    $p2[] = $header;
                }
                break;
            case 'NOTIFY_ADMIN_PROD_LISTING_PRODUCTS_QUERY':
                // Add selection to category products listing
                //         p2 = $extra_select, p3 = $extra_from, p4= $extra_joins, p5 = $extra_ands, p6 = $order_by
                if (defined('PRODUCTS_LOCATION_CATEGORY_DISPLAY') && PRODUCTS_LOCATION_CATEGORY_DISPLAY == 'true') {
                    $p2 = $p2 . ", pl.products_location";
                    $p4 = $p4 . " LEFT JOIN " . TABLE_PRODUCTS_LOCATION . " pl ON (p.products_id = pl.products_id)";
                }
                break;
            case 'NOTIFY_ADMIN_PROD_LISTING_DATA_B4_QTY':
                // Add datafield to category products listing
                if (defined('PRODUCTS_LOCATION_CATEGORY_DISPLAY') && PRODUCTS_LOCATION_CATEGORY_DISPLAY == 'true') {
                    
                    $alignment = 'right';
                    $value = $p1['products_location'];
                    $header = array(
                        'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
                        'text' => $value
                    );
                    if ($p2 === false) $p2=array();
                    $p2[] = $header;
                }
                break;
        }
    }
}