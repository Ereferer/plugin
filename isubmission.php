<?php
/**
 * @author      INFORMATUX (Patrice BOUTHIER)
 * @copyright   2017 INFORMATUX
 * @license     GPL-3.0+
 * Plugin Name: Article submit
 * Description: Vente et échange d'articles
 * Version:     1.0
 * Text Domain: isubmission
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */
defined('ABSPATH') or die('Are you crazy!');
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

//require ISUBMISSION_PATH . '/lib/plugin-update-checker/plugin-update-checker.php';
//
//
//
//
//
//$myUpdateChecker = new Puc_v4p4_Vcs_PluginUpdateChecker(
//	new Puc_v4p4_Vcs_GitLabApi( 'https://gitlab.requestum.com/ereferer/ereferer_isubmission/' ),
//	__FILE__,
//	'isubmission'
//);
//
////Optional: If you're using a private repository, specify the access token like this:
////$myUpdateChecker->setAuthentication( 'MIIEpQIBAAKCAQEAul97Zugf0fgD+zE2bxdOAb2cBIlqNE07zIVEe3yJai6fgEQQDpPpZ6X1rciPZoePnu+u+T4qPa2iWcJfeW+mf5yCZKjHHiZ0ff7TK4UI1hkCS9yqK0+Mt8uTppnV+heRjXs2JLzDg0uOh8k+x2Q4p3WLLeUiOFcs9T5LsqrcUd60Y5r46Mq7EpoceGslh+dTD1zbfi/KwLKuhzXfxSwL2LpJz2mEjhWnwUt2vMZF8D01XM2vkApyEPr8QwylYs8kEVP4mqZdeAuM0bEOl6w/Dgo8keSqIJvJMl9B/lPyD9YsM1MNrdDlkczes4O6eUzNTnvnihnzS4OUkGCI76SqHwIDAQABAoIBAQCqFLjpEKz5UP1RH3gtqXbm38Kh4UWqtVD9NCFrEBvXavTkeTiuFQ3MKQgrr/wt9Uh8Iv/rNXAXGX4vq9K1X87yZkIY2m3cdfuZgBP2g1GtEOWnlZk2LUKd+IqmX32G3jtWgjrHC/zgtPM9t8oy7KNHL130ZEb2Y5gDtvYd9w9ZJfQqdDyOMGUH8iQ62t9GDhcPfih+TsfsGpZkA8NkySjsZdWBIac7vKYE41c2iSyk+hXQh5nhxJV3+XaBCIbavfipGOQ6imwjzXc6pEhuthh4GiciBV9iM7xTGQNw0wpPuTmMdVz9NKu5rhnbZh5jFVutxGUR76S4kYGtaNDVPiMBAoGBAO1jp4hJpBf1F2W+NDewP/OCyx+HtNicUnglQE2uGUfWqlywETO/WIxCdZ74m9mUB3A3GIltOpyqjfmDDlQO2+ShClf6ZKdXv6rf0H689nZbuEOp7PJJyIM5HRUYN58NkmaX5cQk1QmygXl30DUeopXPEWyeTlxOZIQwebdYA2C7AoGBAMj78Vr4Q5fHh7rH6mMWDkBUdQEmC/jgJzi3l3B+2BVG5VHnXIamCqA24pkPOXn/LW/nzrfVbe+2oJZKv/Y6UNp11Ax5+pg0mHiUNTJ+zvS9m/C0RpuXMEiMADIPym+fxFk8/VdKLNtDT2vEZiiVwIxoIWTF5wYssTzCEL1ULQftAoGAMQFGf2r8VfnBh75ZFznqKcHRXsPsAF9c4vKFsMOE8oCNEK9EDdOtWt8JWvTlb2gQlQi6pvwGgnru7hgw6AddO3hHI1xMVQNXTNYBO3iUxGAwzL8Sa/3xR62NpGIocUwi8czfoLsdw2+3LLUgJSca1yQ066BOet2wAF3lcoTXxasCgYEAgBaI+Cf2s/l22CPmjeWViYwJ0YSU5rS8alofCpPcVJsNNQiVID8b0IWKHm/keoqVQ5dhWCmOWdJzP7U958e8la24SYdHnM8QSPBzgs3sSW+5vUq3IRvWqrWGvmDv6/nPYewLrSDZu5eHOzA8xyrBPvpyJc42cOn0vrsTfgXyePUCgYEAsF7LJlioWkYUyLig6UzVNad/dNAzRm+rHsv73Y9hVRGiNTLhdeJvwQrKWKCYRDvk/ZT8N9A8Ecx860wZSlTA6clYQZGLymJ7h8LukpQFTFJA94ZmPRGBiYX8xxLNK2nBTqpu/fZhY17BBG7S1beoYefyOns/V0CLr5EIGI9m82k=' );
//$myUpdateChecker->setAuthentication( '1VvsgPhM7DEQ1bTGE7eu' );
//
////Optional: Set the branch that contains the stable release.
//$myUpdateChecker->setBranch( 'master' );
//
////var_dump($myUpdateChecker);

//$tmp = wp_remote_get( 'https://gitlab.requestum.com/api/v4/projects?private_token=9koXpg98eAheJpvBs5tK' );
//var_dump($tmp);