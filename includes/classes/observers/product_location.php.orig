<?php

// -----
// An observer-class to delete entries from the Products location table when a product is deleted.
//
//
class ProductsLocation extends base
{

    public function __construct()
    {
        $this->attach($this, array(
            'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS',
            'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT',
            'NOTIFY_PRODUCT_MUSIC_UPDATE_PRODUCT_END',
            'NOTIFY_PRODUCT_MUSIC_COPY_TO_CONFIRM_DUPLICATE',
            'NOTIFY_MODULES_COPY_TO_CONFIRM_DUPLICATE',
            'NOTIFY_MODULES_UPDATE_PRODUCT_END'
        ));
    }

    public function update(&$class, $eventID, $p1, &$p2, $p3)
    {
        global $db;
        switch ($eventID) {
            case 'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT':
                // Delete from product location table
                if (defined('PRODUCTS_LOCATION_VERSION')) {
                    $db->Execute("delete from " . TABLE_PRODUCTS_LOCATION . "
				  where products_id = '" . (int) $p2 . "'");
                }
                break;
            case 'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS':
                // Update product location table
                //array(
                //    'label' => array(
                //        'text' => 'The label text',   (required)
                //        'field_name' => 'The name of the field associated with the label', (required)
                //        'addl_class' => {Any additional class to be applied to the label} (optional)
                //        'parms' => {Any additional parameters for the label, e.g. 'style="font-weight: 700;"} (optional)
                //    ),
                //    'input' => 'The HTML to be inserted' (required)
                // )
                $products_id=$p1->products_id;
                $location_query = 'SELECT products_location FROM ' . TABLE_PRODUCTS_LOCATION . ' WHERE products_id = ' . $products_id ;
                $location =  $db->Execute($location_query);
                $input =  zen_draw_input_field('products_location', htmlspecialchars(stripslashes($location->fields['products_location']), ENT_COMPAT, CHARSET, TRUE),
                    zen_set_field_length(TABLE_PRODUCTS_LOCATION, 'products_location') . ' class="form-control"') ;
                $p2 = array('products_location'=>array(
                       'label'=> array(
                         'text' => TEXT_PRODUCTS_LOCATION,
                         'field_name' =>'products_location'),
                       'input' => $input));
                break;
            case 'NOTIFY_MODULES_UPDATE_PRODUCT_END':
            case 'NOTIFY_PRODUCT_MUSIC_UPDATE_PRODUCT_END':
                // Update or insert new product location
                $sql_data_array = array('products_location' => zen_db_prepare_input($_POST['products_location']));
                $products_id = $p1['products_id'];
                $action = $p1['action'];
                switch ($action){
                    case 'insert_product':
                    $insert_sql_data = array('products_id' => $products_id);
                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
                    zen_db_perform(TABLE_PRODUCTS_LOCATION, $sql_data_array);
                    break;
                    case 'update_product':
                    zen_db_perform(TABLE_PRODUCTS_LOCATION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
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
                $location = $db->Execute("SELECT products_location
                                      FROM " . TABLE_PRODUCTS_LOCATION . "
                                      WHERE products_id = '" . (int)$old_products_id . "'");
                $db->Execute("REPLACE INTO " . TABLE_PRODUCTS_LOCATION . "
                          (products_id, products_location)
                          VALUES ('" . (int)$dup_products_id . "',
                          '" . zen_db_input($location->fields['products_location']) . "');");
                break;
        }
    }
}