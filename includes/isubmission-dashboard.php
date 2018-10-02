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

$isubmission_status = '';

if ( '1' === get_option( 'isubmission_status' ) ) {

	$isubmission_status = '<span style="color: #00FF00;"><span style="font-size: 25px; vertical-align: middle;">&#10003;</span>' . __( 'Connexion successfull!', ISUBMISSION_ID_LANGUAGES ) . '</span>';
} else {

	$isubmission_status = '<span style="color: #FF0000;"><spawn style="font-size: 25px; vertical-align: middle;">&#10005;</spawn>' . __( 'Connexion unsuccessful!', ISUBMISSION_ID_LANGUAGES ) . '</span>';
}

$dashboardTab->createOption( array(
	'id'    => 'isubmission_api_key',
	'name'  => __( 'Clé API', ISUBMISSION_ID_LANGUAGES ),
	'type'  => 'text',
	'desc'  => __( 'Renseignez votre clé (API)', ISUBMISSION_ID_LANGUAGES ),
	'unit'  => $isubmission_status
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'name'  => __( 'Options', ISUBMISSION_ID_LANGUAGES ),
    'type'  => 'heading',
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'    => 'isubmission_categories',
	'name'  => __( 'Catégories autorisées pour les rédacteurs', ISUBMISSION_ID_LANGUAGES ),
    'type'  => 'multicheck-categories',
    'desc'  =>  __( 'Sélectionnez au moins uno catégorie', ISUBMISSION_ID_LANGUAGES ),
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
	'name'    => __( 'Statut des articles envoyés', ISUBMISSION_ID_LANGUAGES ),
	'options' => array(
		'publish' => __( 'Directement publié (recommandé):', ISUBMISSION_ID_LANGUAGES ),
		'pending' => __( 'En attente de relecture:', ISUBMISSION_ID_LANGUAGES ),
	),
	'type'    => 'radio',
	'desc'    => __( 'ATTENTION! Si vous choisissez le statut "en attente de relecture", vous ne disposez que de 15 jours pour valider l\'article. Passé ce délai, vorte site passera en inactif sur Ereferer et sera désactivé.', ISUBMISSION_ID_LANGUAGES ),
	'default' => 'publish'
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'     => 'isubmission_menu_name',
	'name'  => __( 'Nom du menu', ISUBMISSION_ID_LANGUAGES ),
	'type'   => 'text',
) );
// ----------------------------------------
$dashboardTab->createOption( array(
	'id'      => 'isubmission_is_posts_editable',
	'name'    => __( 'Modification possible?', ISUBMISSION_ID_LANGUAGES ),
	'options' => array(
		'yes' => __( 'Yes', ISUBMISSION_ID_LANGUAGES ),
		'no'  => __( 'No', ISUBMISSION_ID_LANGUAGES ),
	),
	'type'    => 'radio',
	'desc'    => __( 'Partner will be able to do small correction to their article. Nevertheless and to prevent abuse, they could not add new link or modify an existing link.', ISUBMISSION_ID_LANGUAGES ),
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