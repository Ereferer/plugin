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
$isubmission_db_version = '7.0';
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
				id int(11) NOT NULL AUTO_INCREMENT,
				post_id int(11) NOT NULL,
				place_post_id int(11) NOT NULL,
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

if ( ! function_exists( 'isubmission_upgrade_completed' ) ) {

	function isubmission_upgrade_completed( $upgrader_object, $options ) {

		// The path to our plugin's main file
		$our_plugin = plugin_basename( __FILE__ );
		// If an update has taken place and the updated type is plugins and the plugins element exists
		if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
			// Iterate through the plugins being updated and check if ours is there
			foreach ( $options['plugins'] as $plugin ) {
				if ( $plugin == $our_plugin ) {
					// Set a transient to record that our plugin has just been updated
					set_transient( 'wp_upe_updated', 1 );
				}
			}
		}
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
	}
}

function isubmission_check_file_endpoint() {

	$isubmission_options = maybe_unserialize( get_option( 'isubmission_options' ) );

	if ( empty( $isubmission_options['isubmission_endpoint'] ) ) {

		return;
	}

	$file_endpoint = plugin_dir_path( __FILE__ ) . '../../../' . substr( $isubmission_options['isubmission_endpoint'], strlen( home_url() ) + 1 );

	if ( file_exists( $isubmission_options['isubmission_endpoint'] ) ) {

		return;
	}

	$content = "<?php require_once '" . plugin_dir_path( __FILE__ ) . "isubmission-post-endpoint.php';";

	file_put_contents( $file_endpoint, $content );
}