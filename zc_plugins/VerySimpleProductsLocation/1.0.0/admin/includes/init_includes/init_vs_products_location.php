<?php
// Version Checking
$module_file_for_version_check = ($module_file_for_version_check != '') ? DIR_FS_ADMIN . $module_file_for_version_check : '';
if ($zencart_com_plugin_id != 0 && ($module_file_for_version_check == '' || $_SERVER ["PHP_SELF"] == $module_file_for_version_check)) {
  $new_version_details = plugin_version_check_for_updates ( $zencart_com_plugin_id, $current_version );
  if ($_GET ['gID'] == $configuration_group_id && $new_version_details != false) {
  $messageStack->add ( 'Version ' . $new_version_details ['latest_plugin_version'] . ' of ' . $new_version_details ['title'] . ' is available at <a href="' . $new_version_details ['link'] . '" target="_blank">[Details]</a>', 'caution' );
  }
}

if (! function_exists ( 'plugin_version_check_for_updates' )) {
  function plugin_version_check_for_updates($plugin_file_id = 0, $version_string_to_compare = '') {
    if ($plugin_file_id == 0) {
      return false;
    }
    $new_version_available = false;
    $lookup_index = 0;
    $url = 'https://www.zen-cart.com/downloads.php?do=versioncheck' . '&id=' . ( int ) $plugin_file_id;
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_VERBOSE, 0 );
    curl_setopt ( $ch, CURLOPT_HEADER, false );
    curl_setopt ( $ch, CURLOPT_USERAGENT, 'Plugin Version Check [' . ( int ) $plugin_file_id . '] ' . HTTP_SERVER );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
    $response = curl_exec ( $ch );
    $error = curl_error ( $ch );
    if ($error > 0) {
      curl_setopt ( $ch, CURLOPT_URL, str_replace ( 'tps:', 'tp:', $url ) );
      $response = curl_exec ( $ch );
      $error = curl_error ( $ch );
    }
    curl_close ( $ch );
    if ($error > 0 || $response == '') {
      $response = file_get_contents ( $url );
    }
    if ($response === false) {
      $response = file_get_contents ( str_replace ( 'tps:', 'tp:', $url ) );
    }
    if ($response === false) {
      return false;
    }
    $data = json_decode ( $response, true );
    if (! $data || ! is_array ( $data )) {
      return false;
    }
 // compare versions
    if (strcmp ( $data [$lookup_index] ['latest_plugin_version'], $version_string_to_compare ) > 0) {
      $new_version_available = true;
    }
// check whether present ZC version is compatible with the latest available plugin version
    if (! in_array ( 'v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR, $data [$lookup_index] ['zcversions'] )) {
      $new_version_available = false;
    }

    return ($new_version_available) ? $data [$lookup_index] : false;
  }
}
