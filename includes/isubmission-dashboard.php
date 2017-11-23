<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');


// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Create tab's dashboard                -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// ----------------------------------------
$dashboardTab->createOption( array(
	'name'  => __( 'API', ISUBMISSION_ID_LANGUAGES ),
    'type'  => 'heading',
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'    => 'isubmission_api_key',
	'name'  => __( 'Api key', ISUBMISSION_ID_LANGUAGES ),
	'type'  => 'text',
	'desc'  => __( 'Fill in your Api key', ISUBMISSION_ID_LANGUAGES ),
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'name'  => __( 'Options', ISUBMISSION_ID_LANGUAGES ),
    'type'  => 'heading',
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'    => 'isubmission_categories',
	'name'  => __( 'Authorized Categories for Editors', ISUBMISSION_ID_LANGUAGES ),
    'type'  => 'multicheck-categories',
    'desc'  =>  __( 'select at least one category', ISUBMISSION_ID_LANGUAGES ),
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'     => 'isubmission_endpoint',
	'type'   => 'text',
	'hidden' => true
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'      => 'isubmission_post_status',
	'name'    => __( 'Post status', ISUBMISSION_ID_LANGUAGES ),
	'options' => array(
		'publish' => __( 'Published (recommended)', ISUBMISSION_ID_LANGUAGES ),
		'pending' => __( 'Pending Review', ISUBMISSION_ID_LANGUAGES ),
	),
	'type'    => 'radio',
	'desc'    => __( 'ATTENTION!', ISUBMISSION_ID_LANGUAGES ),
	'default' => 'publish'
) );
// ----------------------------------------

if (!function_exists("isubmission_admin_notice_error")) {
	function isubmission_admin_notice_error() {
		$isubmission_options = TitanFramework::getInstance( 'isubmission' );
		$isubmission_class   = 'notice notice-error';
		$isubmission_message = strtoupper(ISUBMISSION_NAME) . ': ' . sprintf( __( 'Fill in all <a href="%s">dashboard options</a>', ISUBMISSION_ID_LANGUAGES ), get_admin_url(get_current_blog_id(), 'admin.php?page=isubmission&tab=dashboard') ) ;
	
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $isubmission_class ), $isubmission_message ); 
	}
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
//     Check if options are not empty    -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
if (empty($isubmission_options->getOption( 'isubmission_categories' ))
	|| empty($isubmission_options->getOption( 'isubmission_api_key' ))
) {
	// WP Alert
	add_action( 'admin_notices', 'isubmission_admin_notice_error' );
}
?>