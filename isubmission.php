<?php
/**
 * @author      INFORMATUX (Patrice BOUTHIER)
 * @copyright   2017 INFORMATUX
 * @license     GPL-3.0+
 * Plugin Name: Article submit
 * Description: Vente et échange d'articles
 * Version:     1.1.3
 * Text Domain: isubmission
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */
defined('ABSPATH') or die('Are you crazyy!');
// -----------------------------------------
defined('ISUBMISSION_PATH') or define('ISUBMISSION_PATH', plugin_dir_path(__FILE__));
defined('ISUBMISSION_URL') or define('ISUBMISSION_URL', plugin_dir_url(__FILE__));
defined('ISUBMISSION_BASE') or define('ISUBMISSION_BASE', plugin_basename(__FILE__));
defined('ISUBMISSION_ID') or define('ISUBMISSION_ID', 'isubmission');
defined('ISUBMISSION_ID_LANGUAGES') or define('ISUBMISSION_ID_LANGUAGES', 'isubmission-translate');
defined('ISUBMISSION_VERSION') or define('ISUBMISSION_VERSION', '1.0');
defined('ISUBMISSION_NAME') or define('ISUBMISSION_NAME', 'Article submit');
// -----------------------------------------
require_once(ISUBMISSION_PATH . ISUBMISSION_ID . '-includes.php');

register_activation_hook( __FILE__, 'isubmission_install' );
register_deactivation_hook( __FILE__, 'isubmission_uninstall');

require ISUBMISSION_PATH . '/lib/plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/Ereferer/plugin',
	__FILE__,
	'isubmission'
);
