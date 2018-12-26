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
	$menu_name = ISUBMISSION_NAME;
	$isubmission_current_options = maybe_unserialize( get_option( 'isubmission_options' ) );

	if ( ! empty( $isubmission_current_options['isubmission_menu_name'] ) ) {
		$menu_name = $isubmission_current_options['isubmission_menu_name'];
	}

	$isubmission_panel = $isubmission_options->createAdminPanel( array(
		'menu_title' => $menu_name,
		'name'       => $menu_name . ' <a class="add-new-h2" href="./admin.php?page=isubmission_list">' . __( 'All items', ISUBMISSION_ID_LANGUAGES ) . '</a>',
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

	$apy_key    = $isubmission_options->getOption( 'isubmission_api_key' );
	$categories = $isubmission_options->getOption( 'isubmission_categories' );
	$endpoint   = $isubmission_options->getOption( 'isubmission_file_endpoint' );

	if ( empty( $apy_key ) || empty( $categories ) || empty( $endpoint ) ) {

		return;
	}

	$data = array(
		'website_url' => get_site_url(),
		'plugin_url'  => ISUBMISSION_URL . $endpoint,
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
}

add_action( 'tf_save_admin_isubmission', 'isubmission_save_options', 10, 3 );

function isubmission_curl( $apy_key, $data ) {

	$data_json = json_encode( $data );

	$curl = curl_init( 'http://ereferer.com/bo/exchange-site/update-partner' );
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

function isubmission_is_connected() {

	$isubmission_options = maybe_unserialize( get_option( 'isubmission_options' ) );

	if ( empty( $isubmission_options['isubmission_api_key'] ) || empty( $isubmission_options['isubmission_file_endpoint'] ) ) {

		return false;
	}

	$data = array(
		'website_url'     => get_site_url(),
		'plugin_url'      => ISUBMISSION_URL . $isubmission_options['isubmission_file_endpoint'],
		'categories'      => array(),
		'test_connection' => true
	);

	$categories = maybe_unserialize( $isubmission_options['isubmission_categories'] );

	if ( ! empty( $categories ) ) {

		$data['categories'] = array();

		foreach ( $categories as $category_id ) {

			$data['categories'][] = array(
				'name'        => get_cat_name( $category_id ),
				'internal_id' => $category_id
			);
		}
	}

	$response = isubmission_curl( $isubmission_options['isubmission_api_key'], $data );

	if ( $response === '"OK"' ) {

		return true;
	}

	return false;
}

function isubmission_pre_save_admin( $container, $activeTab, $options ) {

	$isubmission_options = TitanFramework::getInstance( 'isubmission' );

	$apy_key    = $isubmission_options->getOption( 'isubmission_api_key' );
	$categories = $isubmission_options->getOption( 'isubmission_categories' );

	if ( empty( $apy_key ) || empty( $categories ) ) {

		isubmission_redirect_to_form();
		exit();
	}

	$random_file = isubmission_random3() . '.php';

	$previous_file_endpoint = ISUBMISSION_PATH . $isubmission_options->getOption( 'isubmission_file_endpoint' );

	$container->owner->setOption( 'isubmission_file_endpoint', $random_file );

	$new_file_endpoint = ISUBMISSION_PATH . $random_file;

	if ( file_exists( $previous_file_endpoint ) ) {

		rename( $previous_file_endpoint, $new_file_endpoint );
	} else {

		$content = "<?php require_once '" . ISUBMISSION_PATH . "isubmission-post-endpoint.php';";

		file_put_contents( $new_file_endpoint, $content );
	}
}

add_action( 'tf_pre_save_admin_isubmission', 'isubmission_pre_save_admin', 10, 3 );

function isubmission_redirect_to_form() {

	$url = wp_get_referer();
	$url = add_query_arg( 'page', urlencode( ISUBMISSION_ID ), $url );
	$url = add_query_arg( 'tab', urlencode( 'dashboard' ), $url );

	wp_redirect( esc_url_raw( $url ) );
}
