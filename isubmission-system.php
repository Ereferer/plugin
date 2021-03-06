<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined( 'ABSPATH' ) or die( 'Are you crazy!' );

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Plugin DB Version / Table name       -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
global $isubmission_db_version, $plugin_table_isub;
$isubmission_db_version = '7.1';
$plugin_table_isub      = "isubmission";

/**
 * Install the plugin DB
 * @return DB Insert/Upgrade
 */
if ( ! function_exists( 'isubmission_install' ) ) {
	function isubmission_install() {
		global $wpdb, $isubmission_db_version, $plugin_table_isub;

		$table_name_isub = $wpdb->prefix . $plugin_table_isub;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name_isub (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) NOT NULL,
				place_post_id BIGINT(20) NOT NULL,
				`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				image int(11) DEFAULT NULL,
				PRIMARY KEY (id)
			   ) $charset_collate;
		";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( 'isubmission_db_version', $isubmission_db_version );

		/**
		 * [OPTIONAL] Example of updating to x.x.x version
		 *
		 * If you develop new version of plugin
		 * just increment $isubmission_db_version variable
		 * and add following block of code
		 */
		$installed_ver = get_option( 'isubmission_db_version' );
		if ( $installed_ver != $isubmission_db_version ) {

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			// notice that we are updating option, rather than adding it
			update_option( 'isubmission_db_version', $isubmission_db_version );
		}
	}
}

if ( ! function_exists( 'isubmission_uninstall' ) ) {
	function isubmission_uninstall() {
	}
}

/**
 * Trick to update plugin database
 * @return DB Insert/Upgrade DB datas
 */
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Trick to update plugin database       -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
add_action( 'plugins_loaded', 'isubmission_update_db_check' );

if ( ! function_exists( 'isubmission_update_db_check' ) ) {

	function isubmission_update_db_check() {

		global $isubmission_db_version;

		if ( get_site_option( 'isubmission_db_version' ) != $isubmission_db_version ) {

			isubmission_install();
		}

		isubmission_check_file_endpoint();

		isubmission_save_options( null, null, null );
	}
}

function isubmission_check_file_endpoint() {

	$isubmission_options = maybe_unserialize( get_option( 'isubmission_options' ) );

	if ( empty( $isubmission_options['isubmission_file_endpoint'] ) && ! empty( $isubmission_options['isubmission_endpoint'] ) ) {

		$random_file = basename( $isubmission_options['isubmission_endpoint'] );

		$isubmission_options['isubmission_file_endpoint'] = $random_file;

		update_option( 'isubmission_options', maybe_serialize( $isubmission_options ) );

        if ( defined( 'ABSPATH' ) ) {
            $previous_file_endpoint = ABSPATH . $random_file;

            if ( file_exists( $previous_file_endpoint ) ) {

                unlink( $previous_file_endpoint );
            }
        }
	}

    if ( ! empty( $isubmission_options['isubmission_file_endpoint'] ) ) {

	    $file_endpoint = ISUBMISSION_PATH . $isubmission_options['isubmission_file_endpoint'];

	    if ( ! file_exists( $file_endpoint ) ) {

            $content = "<?php require_once '" . ISUBMISSION_PATH . "isubmission-post-endpoint.php';";

            file_put_contents( $file_endpoint, $content );
        }
    }
}

// cron
function isubmission_cron_add_hook() {

	if ( ! wp_next_scheduled( 'isubmission_check_connection' ) ) {

		wp_schedule_event( time(), 'isubmission_cron_worker', 'isubmission_check_connection' );
	}
}

add_action( 'init', 'isubmission_cron_add_hook' );

function isubmission_add_schedule() {

	$schedules['isubmission_cron_worker'] = array(
		'interval' => HOUR_IN_SECONDS * 7,
		'display'  => 'Every 7 hours'
	);

	return $schedules;
}

add_filter( 'cron_schedules', 'isubmission_add_schedule' );

function isubmission_check_connection_func() {

	$isubmission_options = maybe_unserialize( get_option( 'isubmission_options' ) );

	if ( empty( $isubmission_options['isubmission_api_key'] ) || empty( $isubmission_options['isubmission_file_endpoint'] ) ) {

		return false;
	}

	$data = array(
		'website_url'     => get_site_url(),
		'plugin_url'      => ISUBMISSION_URL . $isubmission_options['isubmission_file_endpoint'],
		'categories'      => array(),
        'is_posts_editable' => $isubmission_options['isubmission_is_posts_editable'] === "yes" ? true : false,
		'test_connection' => true,
        'version' => isubmission_get_version(),
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
	$parsed_response = json_decode($response, true);

    isubmission_set_connection_status( $parsed_response );
}

add_action( 'isubmission_check_connection', 'isubmission_check_connection_func' );

function isubmission_set_connection_status( $parsed_response ) {

	if ( ! empty( $parsed_response['status'] ) ) {

		if ( $parsed_response['status'] === "ok" ) {

			$connection_status = 'ok';
		} else if ( ! empty( $parsed_response['code'] ) ) {

			$connection_status = $parsed_response['code'];
		} else {

			$connection_status = 'fail';
		}

	} else {

		$connection_status = 'fail';
	}

	$isubmission_options = maybe_unserialize( get_option( 'isubmission_options' ) );
	$isubmission_options['isubmission_connection_status'] = $connection_status;
	update_option( 'isubmission_options', maybe_serialize( $isubmission_options ) );
}
