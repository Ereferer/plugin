<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');

// Create your menu outside the class
add_action('admin_menu','isubmission_list_menu');
// Render your admin menu outside the class
function isubmission_list_menu() {
    $hook_menu_isubmission = add_submenu_page('isubmission', __('Summary', ISUBMISSION_ID_LANGUAGES), __('Summary', ISUBMISSION_ID_LANGUAGES), 'manage_options', 'isubmission_list', 'isubmission_page_handler');
	add_action( "load-$hook_menu_isubmission", 'add_options_isubmission' );
}

// Add a Filter to Save Our Option
add_filter('set-screen-option', 'isubmission_set_option', 10, 3);
function isubmission_set_option($status, $option, $value) {
    if ( 'isubmission_per_page' == $option ) return $value;
		return $status;
}
// Add screen options to the page
function add_options_isubmission() {
	global $isubListTable;
	$screen = get_current_screen();
	$option = 'per_page';
	$args = array(
		   'label' => __('Summary', ISUBMISSION_ID_LANGUAGES),
		   'default' => 20,
		   'option' => 'isubmission_per_page'
		   );
	add_screen_option( $option, $args );
	$isubListTable = new isubmission_List_Table();
}

/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function isubmission_page_handler() {
    global $wpdb;

    $tableIsubmission = new isubmission_List_Table();
	//Fetch, prepare, sort, and filter our data...
	$tableIsubmission->prepare_items();

	// Initialization
    $message = '';
    if ('delete' === $tableIsubmission->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Entry(s) successfully deleted : %d', ISUBMISSION_ID_LANGUAGES), count($_REQUEST['id'])) . '</p></div>';
    }
	if ('delete_all' === $tableIsubmission->current_action()) {
		$message = '<div class="updated below-h2" id="message"><p>' . __('All entries successfully deleted', ISUBMISSION_ID_LANGUAGES) . '</p></div>';
	}
	
    ?>
<div class="wrap page_isubmission">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	
	<h1>
		<?php _e('Summary', ISUBMISSION_ID_LANGUAGES); ?>
		&nbsp;
		<a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=isubmission');?>">
			<?php _e('Back to options', ISUBMISSION_ID_LANGUAGES); ?>
		</a>
	</h1>
	
    <?php echo $message; ?>

    <form id="lists-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $tableIsubmission->display() ?>
    </form>

</div>
<?php
}

?>