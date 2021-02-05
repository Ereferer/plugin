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
	'name'  => __( 'API key', ISUBMISSION_ID_LANGUAGES ),
	'type'  => 'text',
	'desc'  => __( 'Fill in your key (API)', ISUBMISSION_ID_LANGUAGES ),
	'unit'  => isubmission_get_connection_status()
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'name'  => __( 'Options', ISUBMISSION_ID_LANGUAGES ),
    'type'  => 'heading',
) );
// ----------------------------------------
$isubmission_current_options = maybe_unserialize( get_option( 'isubmission_options' ) );

if ( empty( $isubmission_current_options['isubmission_categories'] ) ) {

	$dashboardTab->createOption( array(
		'type' => 'note',
		'desc' => '<span style="color: #FF0000;">' . __( 'Veuillez cocher au moins 1 catégorie.', ISUBMISSION_ID_LANGUAGES ) . '</span>'
	) );
}

$dashboardTab->createOption( array(
	'id'   => 'isubmission_categories',
	'name' => __( 'Categories where Ereferer can submit an article', ISUBMISSION_ID_LANGUAGES ),
	'type' => 'multicheck-categories',
	'desc' => __( 'Select at least one category', ISUBMISSION_ID_LANGUAGES ),
	'select_all' => true
) );
// ----------------------------------------
$users = new WP_User_Query( array(
	'fields'  => array( 'ID', 'display_name' ),
	'orderby' => 'display_name',
	'order'   => 'ASC'
) );

$users_select_array = array(
	'' => '---'
);

if ( ! empty( $users->get_results() ) ) {

	foreach ( $users->get_results() as $user ) {

		$users_select_array[ $user->ID ] = $user->display_name;
	}
}

$dashboardTab->createOption( array(
	'id'      => 'isubmission_post_author',
	'name'    => __( 'Author', ISUBMISSION_ID_LANGUAGES ),
	'type'    => 'select',
	'options' => $users_select_array
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'     => 'isubmission_file_endpoint',
	'type'   => 'text',
	'hidden' => true
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'      => 'isubmission_post_status',
	'name'    => __( 'Status of sent items', ISUBMISSION_ID_LANGUAGES ),
	'options' => array(
		'publish' => __( 'Directly published (recommended):', ISUBMISSION_ID_LANGUAGES ),
		'pending' => __( 'Waiting for proofreading:', ISUBMISSION_ID_LANGUAGES ),
	),
	'type'    => 'radio',
	'desc'    => __( 'WARNING! If you choose the status "pending replay", you only have 15 days to validate the article. After this time, your site will go into idle on Ereferer and will be disabled.', ISUBMISSION_ID_LANGUAGES ),
	'default' => 'publish'
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'     => 'isubmission_menu_name',
	'name'  => __( 'Wordpress menu name (optional)', ISUBMISSION_ID_LANGUAGES ),
	'type'   => 'text',
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'      => 'isubmission_is_posts_editable',
	'name'    => __( 'Are modifications possible?', ISUBMISSION_ID_LANGUAGES ),
	'options' => array(
		'yes' => __( 'Yes', ISUBMISSION_ID_LANGUAGES ),
		'no'  => __( 'No', ISUBMISSION_ID_LANGUAGES ),
	),
	'type'    => 'radio',
	'desc'    => __( 'Partners will be allowed to make corrections to their articles. However, they will not be able to add / modify / delete a link to prevent abuse.', ISUBMISSION_ID_LANGUAGES ),
	'default' => 'yes'
) );
// ----------------------------------------

if (!function_exists("isubmission_admin_notice_error")) {
	function isubmission_admin_notice_error() {
		$isubmission_options = TitanFramework::getInstance( 'isubmission' );
		$isubmission_class   = 'notice notice-error';

		$menu_name = ISUBMISSION_NAME;
		$isubmission_current_options = maybe_unserialize( get_option( 'isubmission_options' ) );

		if ( ! empty( $isubmission_current_options['isubmission_menu_name'] ) ) {
			$menu_name = $isubmission_current_options['isubmission_menu_name'];
		}

		$isubmission_message = strtoupper($menu_name) . ': ' . sprintf( __( 'Fill in all <a href="%s">dashboard options</a>', ISUBMISSION_ID_LANGUAGES ), get_admin_url(get_current_blog_id(), 'admin.php?page=isubmission&tab=dashboard') ) ;
	
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