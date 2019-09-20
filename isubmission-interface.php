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
		'save'      => __( 'Enregistrer', ISUBMISSION_ID_LANGUAGES ),
		'use_reset' => false,
	) );

} // END isubmission_create_options


function isubmission_save_options( $container, $activeTab, $options ) {

	if ( empty( $activeTab ) ) {

		return;
	}

	$isubmission_options = maybe_unserialize( get_option( 'isubmission_options' ) );

	if ( empty( $isubmission_options['isubmission_api_key'] ) ||
	     empty( $isubmission_options['isubmission_categories'] ) ||
	     empty( $isubmission_options['isubmission_file_endpoint'] ) ) {

		return;
	}

	$data = array(
		'website_url' => get_site_url(),
		'plugin_url'  => ISUBMISSION_URL . $isubmission_options['isubmission_file_endpoint'],
		'categories'  => array(),
        'is_posts_editable' => $isubmission_options['isubmission_is_posts_editable'] === "yes" ? true : false,
        'version' => isubmission_get_version(),
	);

	$data['categories'] = array();
	$categories = maybe_unserialize( $isubmission_options['isubmission_categories'] );

	foreach ( $categories as $category_id ) {

		$data['categories'][] = array(
			'name'        => get_cat_name( $category_id ),
			'internal_id' => $category_id
		);
	}

	$response = isubmission_curl( $isubmission_options['isubmission_api_key'], $data );

	$parsed_response = json_decode($response, true);

	isubmission_set_connection_status( $parsed_response );
}

add_action( 'tf_save_admin_isubmission', 'isubmission_save_options', 10, 3 );

function isubmission_curl( $api_key, $data ) {

	$data_json = json_encode( $data );

	$curl = curl_init( 'https://ereferer.com/bo/exchange-site/update-partner' );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Authorization: Bearer ' . $api_key
	) );
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PATCH' );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_json );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 2);
    curl_setopt( $curl, CURLOPT_COOKIE, '_language_redirect=1;' );

	$response = curl_exec( $curl );

	curl_close( $curl );

	return $response;
}

function isubmission_get_connection_status() {

	$connection_statuses = array(
		'fail'                => __( 'Connexion unsuccessful!', ISUBMISSION_ID_LANGUAGES ),
		'ok'                  => __( 'Connexion réussie !', ISUBMISSION_ID_LANGUAGES ),
		'blocked_by_firewall' => __( 'A firewall seems to block the connection of the plugin. You must unblock the IP 5.179.192.81 so that the connection can be made!', ISUBMISSION_ID_LANGUAGES ),
		'wrong_token'         => __( 'La connexion a échoué. Vérifiez que vous ayez bien ajouté votre site sur Ereferer.', ISUBMISSION_ID_LANGUAGES ),
	);

	$isubmission_options = maybe_unserialize( get_option( 'isubmission_options' ) );

	if ( ! empty( $isubmission_options['isubmission_connection_status'] ) && array_key_exists( $isubmission_options['isubmission_connection_status'], $connection_statuses ) ) {

		if ( 'ok' === $isubmission_options['isubmission_connection_status'] ) {

			return isubmission_get_styled_status( $connection_statuses[ $isubmission_options['isubmission_connection_status'] ] );
		} else {

			return isubmission_get_styled_status( $connection_statuses[ $isubmission_options['isubmission_connection_status'] ], false );
		}
	}

	return isubmission_get_styled_status( $connection_statuses['fail'], false );
}

function isubmission_get_styled_status( $message, $is_successful = true ) {

	if ( $is_successful ) {

		return '<span style="color: #00FF00;"><span style="font-size: 25px; vertical-align: middle;">&#10003;</span>' . $message . '</span>';
	}

	return '<span style="color: #FF0000;"><span style="font-size: 25px; vertical-align: middle;">&#10005;</span>' . $message . '</span>';
}

function isubmission_pre_save_admin( $container, $activeTab, $options ) {

	$isubmission_options = TitanFramework::getInstance( 'isubmission' );

	$api_key    = $isubmission_options->getOption( 'isubmission_api_key' );
	$categories = $isubmission_options->getOption( 'isubmission_categories' );

	if ( empty( $api_key ) || empty( $categories ) ) {

		isubmission_redirect_to_form();
		exit();
	}

	$random_file = isubmission_random3() . '.php';

	$previous_file_endpoint = ISUBMISSION_PATH . $isubmission_options->getOption( 'isubmission_file_endpoint' );

	$container->owner->setOption( 'isubmission_file_endpoint', $random_file );

	$new_file_endpoint = ISUBMISSION_PATH . $random_file;

    if ( !file_exists( $previous_file_endpoint ) || $previous_file_endpoint == ISUBMISSION_PATH) {
        $content = "<?php require_once '" . ISUBMISSION_PATH . "isubmission-post-endpoint.php';";

        file_put_contents( $new_file_endpoint, $content );
    } else {
        rename( $previous_file_endpoint, $new_file_endpoint );
    }
}

add_action( 'tf_pre_save_admin_isubmission', 'isubmission_pre_save_admin', 10, 3 );

function isubmission_redirect_to_form() {

	$url = wp_get_referer();
	$url = add_query_arg( 'page', urlencode( ISUBMISSION_ID ), $url );
	$url = add_query_arg( 'tab', urlencode( 'dashboard' ), $url );

	wp_redirect( esc_url_raw( $url ) );
}
