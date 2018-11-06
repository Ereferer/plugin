<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Load plugin translations              -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
add_action( 'plugins_loaded', 'isubmission_translate_load_textdomain', 1 );
if ( ! function_exists( 'isubmission_translate_load_textdomain' ) ) {
	function isubmission_translate_load_textdomain() {
		$path = basename( dirname( __FILE__ ) ) . '/languages/';
		load_plugin_textdomain( ISUBMISSION_ID_LANGUAGES, false, $path );
	}
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Load plugin files                     -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
if ( ! function_exists( 'is_plugin_active' ) )
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Include Titan Framework               -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$titan_check_framework_install = 'titan-framework/titan-framework.php';
// --- Check if plugin titan framework is installed
if (is_plugin_active($titan_check_framework_install)) {
	require_once(WP_CONTENT_DIR . '/plugins/titan-framework/titan-framework-embedder.php');
} else {
	require_once(ISUBMISSION_PATH . 'lib/titan-framework/titan-framework-embedder.php');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Initialize plugin SQL Debug Mode      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('_ISUBMISSION_DEBUG') or define('_ISUBMISSION_DEBUG', false);

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Initialize plugin Files               -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$isubmissionClasses = ['lists'];
foreach ($isubmissionClasses as $isubmissionClass) {
	$class = ISUBMISSION_PATH . 'class' . DIRECTORY_SEPARATOR . ISUBMISSION_ID . '-class-' . $isubmissionClass . '.php';
    if (file_exists($class)) require_once($class);
}
$isubmissionFiles = ['system', 'interface', 'functions', 'style'];
foreach ($isubmissionFiles as $isubmissionFile) {
	$file = ISUBMISSION_PATH . ISUBMISSION_ID . '-' . $isubmissionFile . '.php';
    if (file_exists($file)) require_once($file);
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
//         ISUBMISSION Get Infos         -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( 'isubmission_get_version' ) ) {
    function isubmission_get_version( $isubmission_infos = 'Version' ) {
    
        /* *************************************************************************************
         *
         * 'Name' - Name of the plugin, must be unique.
         * 'Title' - Title of the plugin and the link to the plugin's web site.
         * 'Description' - Description of what the plugin does and/or notes from the author.
         * 'Author' - The author's name
         * 'AuthorURI' - The authors web site address.
         * 'Version' - The plugin version number.
         * 'PluginURI' - Plugin web site address.
         * 'TextDomain' - Plugin's text domain for localization.
         * 'DomainPath' - Plugin's relative directory path to .mo files.
         * 'Network' - Boolean. Whether the plugin can only be activated network wide.
         *
         * ********************************************************************************** */
    
        $plugin_data = get_plugin_data( __FILE__ );
        $plugin_version = $plugin_data[ "$isubmission_infos" ];
        return $plugin_version;
    }
}
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

require ISUBMISSION_PATH . '/lib/plugin-update-checker/plugin-update-checker.php';

//$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
//	'http://example.com/path/to/details.json',
//	__FILE__, //Full path to the main plugin file or functions.php.
//	'unique-plugin-or-theme-slug'
//);



$myUpdateChecker = new Puc_v4p4_Vcs_PluginUpdateChecker(
	new Puc_v4p4_Vcs_GitLabApi( 'https://gitlab.requestum.com/ereferer/ereferer_isubmission/' ),
	__FILE__,
	'ereferer_isubmission'
);

//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication( 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQC6X3tm6B/R+AP7MTZvF04BvZwEiWo0TTvMhUR7fIlqLp+ARBAOk+lnpfWtyI9mh4+e7675Pio9raJZwl95b6Z/nIJkqMceJnR9/tMrhQjWGQJL3KorT4y3y5OmmdX6F5GNezYkvMODS46HyT7HZDindYst5SI4Vyz1PkuyqtxR3rRjmvjoyrsSmhx4ayWH51MPXNt+L8rAsq6HNd/FLAvYuknPaYSOFafBS3a8xkXwPTVcza+QCnIQ+vxDDKVizyQRU/iapl14C4zRsQ6XrD8OCjyR5Kogm8kyX0H+U/IP1iwzUw2t0OWRzN6zg7p5TM1Oe+eKGfNLg5SQYIjvpKof martvitaha@gmail.com' );

//Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');
