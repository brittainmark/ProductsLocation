<?php

// -----
// An observer-class to delete, update and create entries from the very simple Products location table when a product is deleted, copied created or modified.
//
//
class VerySimpleProductsLocation extends base
{

    public function __construct()
    {
        $this->attach($this, [
            'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS',
            'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT',
            'NOTIFY_PRODUCT_MUSIC_UPDATE_PRODUCT_END',
            'NOTIFY_PRODUCT_MUSIC_COPY_TO_CONFIRM_DUPLICATE',
            'NOTIFY_MODULES_COPY_TO_CONFIRM_DUPLICATE',
            'NOTIFY_MODULES_UPDATE_PRODUCT_END',
            'NOTIFY_ADMIN_PROD_LISTING_HEADERS_B4_QTY',
            'NOTIFY_ADMIN_PROD_LISTING_PRODUCTS_QUERY',
            'NOTIFY_ADMIN_PROD_LISTING_DATA_B4_QTY',
            'NOTIFY_ADMIN_INVOICE_HEADING_B4_TAX',
            'NOTIFY_ADMIN_INVOICE_SORT_DISPLAY',
            'NOTIFY_ADMIN_INVOICE_DATA_B4_TAX',
            'NOTIFY_ADMIN_PACKINGSLIP_HEADING',
            'NOTIFY_ADMIN_PACKINGSLIP_SORT_DISPLAY',
            'NOTIFY_ADMIN_PACKINGSLIP_DATA'
        ]);
    }

    public function update(&$class, $eventID, $p1, &$p2, &$p3, &$p4, &$p5, &$p6)
    {
        global $db;
        switch ($eventID) {
            case 'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT':
                // Delete from vs_products_location table
                if (defined('VS_PRODUCTS_LOCATION_VERSION')) {
                    $db->Execute("DELETE FROM " . TABLE_VS_PRODUCTS_LOCATION . "
				  where products_id = '" . (int) $p2 . "'");
                }
                break;
            case 'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS':
                // Update vs_products_location table
                // array(
                // 'label' => array(
                // 'text' => 'The label text', (required)
                // 'field_name' => 'The name of the field associated with the label', (required)
                // 'addl_class' => {Any additional class to be applied to the label} (optional)
                // 'parms' => {Any additional parameters for the label, e.g. 'style="font-weight: 700;"} (optional)
                // ),
                // 'input' => 'The HTML to be inserted' (required)
                // )
                $location =  $p1->vs_products_location ?? '';
                if (isset($_GET['pID']) && $p1->products_id !== '') {
                    $products_id = $p1->products_id;
                    $location = $this->get_product_location($products_id);
                }
                $input = zen_draw_input_field('vs_products_location', htmlspecialchars(stripslashes($location), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_VS_PRODUCTS_LOCATION, 'vs_products_location') . ' class="form-control"');
                $p2 = [
                    'vs_products_location' => [
                        'label' => [
                            'text' => TEXT_HEADING_VS_LOCATION,
                            'field_name' => 'vs_products_location'
                        ],
                        'input' => $input
                    ]
                ];
                break;
            case 'NOTIFY_MODULES_UPDATE_PRODUCT_END':
            case 'NOTIFY_PRODUCT_MUSIC_UPDATE_PRODUCT_END':
                // Update or insert new product location                
                $products_id = $p1['products_id'];
                $location_query = "INSERT INTO " . TABLE_VS_PRODUCTS_LOCATION . "(products_id,vs_products_location) VALUES (" .
                    $products_id . ", '" . zen_db_input($_POST['vs_products_location']) . "') ON DUPLICATE KEY UPDATE vs_products_location = VALUES (vs_products_location);";
                $location_result = $db->Execute($location_query);
                break;
            case 'NOTIFY_MODULES_COPY_TO_CONFIRM_DUPLICATE':
            case 'NOTIFY_PRODUCT_MUSIC_COPY_TO_CONFIRM_DUPLICATE':
                // Get the product id's
                $old_products_id = $p1['products_id'];
                $dup_products_id = $p1['dup_products_id'];
                // Copy the loaction
                $location = $this->get_product_location($old_products_id);
                $db->Execute("REPLACE INTO " . TABLE_VS_PRODUCTS_LOCATION . "
                          (products_id, vs_products_location)
                          VALUES ('" . (int) $dup_products_id . "',
                          '" . zen_db_input($location) . "');");
                break;
            case 'NOTIFY_ADMIN_PROD_LISTING_HEADERS_B4_QTY':
                // Add header field to category products listing
                if (defined('VS_PRODUCTS_LOCATION_CATEGORY_DISPLAY') && VS_PRODUCTS_LOCATION_CATEGORY_DISPLAY === 'true') {
                    $value = TEXT_HEADING_VS_LOCATION;
                    $p2 = $this->set_extra_pl_field_array($p2, $value);
                }
                break;
            case 'NOTIFY_ADMIN_PROD_LISTING_PRODUCTS_QUERY':
                // Add selection to category products listing
                // p2 = $extra_select, p3 = $extra_from, p4= $extra_joins, p5 = $extra_ands, p6 = $order_by
                if (defined('VS_PRODUCTS_LOCATION_CATEGORY_DISPLAY') && VS_PRODUCTS_LOCATION_CATEGORY_DISPLAY === 'true') {
                    $p2 = $p2 . ", pl.vs_products_location";
                    $p4 = $p4 . " LEFT JOIN " . TABLE_VS_PRODUCTS_LOCATION . " pl ON (p.products_id = pl.products_id)";
                }
                break;
            case 'NOTIFY_ADMIN_PROD_LISTING_DATA_B4_QTY':
                // Add datafield to category products listing
                if (defined('VS_PRODUCTS_LOCATION_CATEGORY_DISPLAY') && VS_PRODUCTS_LOCATION_CATEGORY_DISPLAY === 'true') {
                    $value = $p1['vs_products_location'];
                    $p2 = $this->set_extra_pl_field_array($p2, $value);
                }
                break;
            case 'NOTIFY_ADMIN_INVOICE_HEADING_B4_TAX':
                // Add loaction heading to invoice
                if (defined('VS_PRODUCTS_LOCATION_INVOICE_DISPLAY') && VS_PRODUCTS_LOCATION_INVOICE_DISPLAY === 'true') {
                    $value = TEXT_HEADING_VS_LOCATION;
                    $p2 = $this->set_extra_pl_field_array($p2, $value);
                }
                break;
            case 'NOTIFY_ADMIN_INVOICE_DATA_B4_TAX':
                // Add the location data to invoice
                if (defined('VS_PRODUCTS_LOCATION_INVOICE_DISPLAY') && VS_PRODUCTS_LOCATION_INVOICE_DISPLAY === 'true') {
                    $products_id = $p1;
                    $value = $this->get_product_location($products_id);
                    $p2 = $this->set_extra_pl_field_array($p2, $value);
                }
                break;
            case 'NOTIFY_ADMIN_PACKINGSLIP_HEADING':
                // Add loaction heading to packing slip
                if (defined('VS_PRODUCTS_LOCATION_PACKING_DISPLAY') && VS_PRODUCTS_LOCATION_PACKING_DISPLAY === 'true') {
                    $value = TEXT_HEADING_VS_LOCATION;
                    $p2 = $this->set_extra_pl_field_array($p2, $value);
                }
                break;
            case 'NOTIFY_ADMIN_PACKINGSLIP_DATA':
                // Add the location data to packing slip
                if (defined('VS_PRODUCTS_LOCATION_PACKING_DISPLAY') && VS_PRODUCTS_LOCATION_PACKING_DISPLAY == 'true') {
                    $products_id = $p1;
                    $value = $this->get_product_location($products_id);
                    $p2 = $this->set_extra_pl_field_array($p2, $value);
                }
                break;
            case 'NOTIFY_ADMIN_INVOICE_SORT_DISPLAY':
                // Sort the products order by location
                // $p1 = order->products $p2 = $sort_order
                $n = sizeof($p1);
                // Only sore if more than 1 product and sort requested
                if ($n > 1 && defined('VS_PRODUCTS_LOCATION_INVOICE_DISPLAY') && VS_PRODUCTS_LOCATION_INVOICE_DISPLAY === 'true' && defined('VS_PRODUCTS_LOCATION_SORT_DISPLAY') && VS_PRODUCTS_LOCATION_SORT_DISPLAY === 'true') {
                    $p2 = $this->set_pl_sort_order($p1);
                }
                break;
            case 'NOTIFY_ADMIN_PACKINGSLIP_SORT_DISPLAY':
                // Sort the products order by location
                // $p1 = order->products $p2 = $sort_order
                $n = sizeof($p1);
                // Only sore if more than 1 product and sort requested
                if ($n > 1 && defined('VS_PRODUCTS_LOCATION_PACKING_DISPLAY') && VS_PRODUCTS_LOCATION_PACKING_DISPLAY === 'true' && defined('VS_PRODUCTS_LOCATION_SORT_DISPLAY') && VS_PRODUCTS_LOCATION_SORT_DISPLAY === 'true') {
                    $p2 = $this->set_pl_sort_order($p1);
                }
                break;
        }
    }
    /*
     * read the products location table to extract the products loaction given a products id.
     */

    protected function get_product_location($id)
    {
        global $db;
        $location_query = "SELECT vs_products_location FROM " . TABLE_VS_PRODUCTS_LOCATION . " WHERE products_id = " . (INT) $id . " Limit 1";
        $location_result = $db->Execute($location_query);
        $location = ($location_result->EOF ? '' : $location_result->fields['vs_products_location']);
        return $location;
    }
    /*
     * Set the array for an extra field
     */

    protected function set_extra_pl_field_array($initial, $value)
    {
        $alignment = 'right';
        $header = [
            'align' => $alignment, // One of 'center', 'right', or 'left' (optional)
            'text' => $value
        ];
        if ($initial === false) {
            $field_array = [];
        } else {
            $field_array = $initial;
        }
        $field_array[] = $header;
        return $field_array;
    }
    /*
     * set the sort order for display
     */

    protected function set_pl_sort_order($products)
    {
        $vs_products_location = [];
        $n = sizeof($products);
        for ($i = 0; $i < $n; $i++) {
            $vs_products_location[$i]['order'] = $i;
            $vs_products_location[$i]['model'] = $products[$i]['model'];
            $vs_products_location[$i]['location'] = $this->get_product_location($products[$i]['id']);
        }
        array_multisort(array_column($vs_products_location, 'location'), SORT_ASC, array_column($vs_products_location, 'model'), SORT_ASC, $vs_products_location);
        return array_column($vs_products_location, 'order');
    }
}
