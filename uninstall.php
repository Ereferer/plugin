<?php
// ------------------------------------------------
// if uninstall.php is not called by WordPress, die
// ------------------------------------------------
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
// ------------------------------------------------

// ----------------------------
// Initialization
// ----------------------------
global $wpdb;
$plugin_table_isub_list = $wpdb->prefix . "isubmission";

// ----------------------------
// Drop database table
// ----------------------------
// Table LIST
$wpdb->query("DROP TABLE IF EXISTS $plugin_table_isub_list");
// ----------------------------

// -------------- 
// Delete Options
// --------------
$options = ['isubmission_api_key'
           ,'isubmission_categories'
		   ,'isubmission_website_url'
		   ,'isubmission_website_plugin_url'
		   ,'isubmission_endpoint'
		   ,'isubmission_db_version'
		   ,'isubmission_status'
		   ,'isubmission_options'
];
// --------------
foreach ( $options as $option ) {
	if ( get_option( $option ) ) {
		delete_option( $option );
	}
}

?>