<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');

// ----------------------------------------
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Add CSS stylesheet to admin           -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// ----------------------------------------
add_action( 'admin_enqueue_scripts', 'isubmission_admin_styles' );
function isubmission_admin_styles() {
  wp_register_style( 'isubmission_admin_css', ISUBMISSION_URL . 'css/isubmission-style-admin.css', false, ISUBMISSION_VERSION );
  wp_enqueue_style( 'isubmission_admin_css' );
}
// ----------------------------------------
add_action( 'admin_enqueue_scripts', 'isubmission_script_admin_method' );
function isubmission_script_admin_method() {
	wp_enqueue_script(
		'isubmission-script-admin',
		ISUBMISSION_URL . 'js/isubmission-script-admin.js',
		array( 'jquery' )
	);
}
// ----------------------------------------
?>