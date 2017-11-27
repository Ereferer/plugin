<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');


add_action( 'tf_create_options', 'isubmission_create_options' );
function isubmission_create_options() {
	
	remove_filter( 'admin_footer_text', 'addTitanCreditText' );

    /***************************************************************
     * Launch options framework instance
     ***************************************************************/
    $isubmission_options = TitanFramework::getInstance( 'isubmission' );
    /***************************************************************
     * Create option menu item
     ***************************************************************/
    $isubmission_panel = $isubmission_options->createAdminPanel( array(
        'name'       => ISUBMISSION_NAME,
		'title'      => ISUBMISSION_NAME . ' <a class="add-new-h2" href="./admin.php?page=isubmission_list">' . __( 'All items', ISUBMISSION_ID_LANGUAGES ) . '</a>',
        'icon'       => 'dashicons-upload',
        'id'         => ISUBMISSION_ID,
		'capability' => 'manage_options',
		'desc'       => '',
            ) );
	
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    // Create option panel tabs              -=
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    $dashboardTab = $isubmission_panel->createTab( array(
        'name' => __( 'Options', ISUBMISSION_ID_LANGUAGES ),
        'id'   => 'dashboard',
            ) );
			
	// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	// Create tab's plugin                   -=
	// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	$isubmissionOptions = ['dashboard', 'options', 'lists'];
	foreach ($isubmissionOptions as $isubmissionOption) {
		$isubmissionOptionFile = ISUBMISSION_PATH . 'includes/' . ISUBMISSION_ID . '-' . $isubmissionOption . '.php';
		if (file_exists($isubmissionOptionFile))
			require_once($isubmissionOptionFile);
	}

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	// Launch options framework instance     -=
	// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    $dashboardTab->createOption( array(
        'type'      => 'save',
        'save'      => __( 'Sauvegardez les changements', ISUBMISSION_ID_LANGUAGES ),
		'use_reset' => false,
    ) );
	
} // END isubmission_create_options


function isubmission_save_options( $container, $activeTab, $options ) {

	$isubmission_options = TitanFramework::getInstance( 'isubmission' );

	$apy_key     = $isubmission_options->getOption( 'isubmission_api_key' );
	$categories  = $isubmission_options->getOption( 'isubmission_categories' );
	$endpoint    = $isubmission_options->getOption( 'isubmission_endpoint' );

	if ( empty( $apy_key ) || empty( $categories ) || empty( $endpoint ) ) {
		return;
	}

	$data = array(
		'website_url' => get_site_url(),
		'plugin_url'  => $endpoint,
		'categories'  => array(),
	);

	if ( ! empty( $categories ) ) {

		$data['categories'] = array();

		foreach ( $categories as $category_id ) {

			$data['categories'][] = array(
				'name'        => get_cat_name( $category_id ),
				'internal_id' => $category_id
			);
		}
	}

	$response = isubmission_curl( $apy_key, $data );

//	echo '<pre>';
//	print_r( $response );
//	echo '</pre>';
//
//	exit;
}

add_action( 'tf_save_admin_isubmission', 'isubmission_save_options', 10, 3 );

function isubmission_curl( $apy_key, $data ) {

	$data_json = json_encode( $data );

	$curl = curl_init( 'http://prod.ereferer.fr/partners.html' );
//	$curl = curl_init( 'http://ereferer.loc/partners.html' );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Authorization: Bearer ' . $apy_key
	) );
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PATCH' );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_json );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

	$response = curl_exec( $curl );

	curl_close( $curl );

	return $response;
}

function isubmission_add_external_rule() {

	// is titan framework loaded - when after_setup_theme hook not triggered
	if ( ! class_exists( 'TitanFramework' ) ) {
		return;
	}

	global $wp_rewrite;

	$api_url = plugins_url( 'isubmission-post-endpoint.php', __FILE__ );
	$api_url = substr( $api_url, strlen( home_url() ) + 1 );

	$isubmission_options = TitanFramework::getInstance( 'isubmission' );

	$isubmission_endpoint = $isubmission_options->getOption( 'isubmission_endpoint' );
//	var_dump($isubmission_endpoint);
	$isubmission_endpoint = str_replace( home_url() . '/', '', $isubmission_endpoint );

	$wp_rewrite->add_external_rule( $isubmission_endpoint . '$', $api_url );
}

add_action( 'init', 'isubmission_add_external_rule' );

function isubmission_pre_save_admin( $container, $activeTab, $options ) {

	$random_endpoint = isubmission_random3() . '.php';

	$container->owner->setOption( 'isubmission_endpoint', home_url() . '/' . $random_endpoint );

	global $wp_rewrite;

	$api_url = plugins_url( 'isubmission-post-endpoint.php', __FILE__ );
	$api_url = substr( $api_url, strlen( home_url() ) + 1 );

	$wp_rewrite->add_external_rule( $random_endpoint . '$', $api_url );

	flush_rewrite_rules();
}

add_action( 'tf_pre_save_admin_isubmission', 'isubmission_pre_save_admin', 10, 3 );
