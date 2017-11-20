<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');

/**
 * LOAD THE BASE CLASS
 * ==============================================================
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wordpress.org/extend/plugins/custom-list-table-example/
 */
if ( ! class_exists('WP_List_Table') ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 */
class isubmission_List_Table extends WP_List_Table {

    function __construct(){
        global $status, $page;    
        //Set parent defaults
        parent::__construct( array(
			'singular' => 'item',     // singular name of the listed records
            'plural'   => 'items',    // plural name of the listed records
            'ajax'     => false       // does this table support ajax?
        ) );
    }
	
    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name) {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_date($item) {
        // Build row actions
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on current page
        // also notice how we use $this->_args['singular'] so in this example it will be something like &person=2
        $actions = array(
			//''        => '<span style="color:silver">ID: '.$item['id'].'</span>',
            'delete'  => sprintf('<a onclick="return isubmission_confirm_delete(\''.__('this entry', ISUBMISSION_ID_LANGUAGES).'\');" href="?page=%s&action=%s&id=%s">%s</a>', $_REQUEST['page'], 'delete', $item['id'], __('Delete from list', ISUBMISSION_ID_LANGUAGES)),
            'show'    => sprintf('<a href="%s" target="_blank">%s</a>', get_permalink($item['post_id']), __('See item', ISUBMISSION_ID_LANGUAGES)),
        );

        // Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ isubmission_convert_date($item['date'], 'FRT'),
            /*$2%s*/ $this->row_actions($actions)
        );
    }
	
    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_post_id($item) {
        return ( get_the_title( $item['post_id'] ) ) ? get_the_title( $item['post_id'] ) : '<span class="isubmission-red">' . __('Unknown title OR Deleted post', ISUBMISSION_ID_LANGUAGES) . '</span>';
    }
	
    /**
     * [OPTIONAL] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_image($item) {
        return ( get_the_post_thumbnail( $item['post_id'], 'thumbnail', array( 'class' => 'alignleft' ) ) ) ? get_the_post_thumbnail( $item['post_id'], 'thumbnail', array( 'class' => 'alignleft' ) ) : '<img class="alignleft wp-post-image" src="' . ISUBMISSION_URL . 'images/no-image-default.jpg" alt="No image" />';
    }
	
    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }
	
    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'      => '<input type="checkbox" />', //Render a checkbox instead of text
            'date'    => __('Date', ISUBMISSION_ID_LANGUAGES),
            'image'   => __('Featured Image', ISUBMISSION_ID_LANGUAGES),
            'post_id' => __('Submitted Posts', ISUBMISSION_ID_LANGUAGES),
        );
        return $columns;
    }
	
    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'date'    => array('date', true),   //true means it's already sorted
            'post_id' => array('post_id', false),  //true means it's already sorted
        );
        return $sortable_columns;
    }
	
    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'delete'     => __('Delete from list', ISUBMISSION_ID_LANGUAGES),
            'delete_all' => __('Delete All list', ISUBMISSION_ID_LANGUAGES),
        );
        return $actions;
    }
	
    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action() {
        global $wpdb, $plugin_table_isub;
        $table_name_isub = $wpdb->prefix . $plugin_table_isub; // do not forget about tables prefix
		
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name_isub WHERE id IN($ids)");
            }
        }
		
        if ('delete_all' === $this->current_action()) {
            $wpdb->query("DELETE FROM $table_name_isub");
        }
		
    }
	
	/**
	 * Get number of items to display on a single page
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $option
	 * @param int    $default
	 * @return int
	 */
	protected function get_items_per_page( $option, $default = 20 ) {
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 )
			$per_page = $default;

		/**
		 * Filters the number of items to be displayed on each page of the list table.
		 *
		 * The dynamic hook name, $option, refers to the `per_page` option depending
		 * on the type of list table in use. Possible values include: 'edit_comments_per_page',
		 * 'sites_network_per_page', 'site_themes_network_per_page', 'themes_network_per_page',
		 * 'users_network_per_page', 'edit_post_per_page', 'edit_page_per_page',
		 * 'edit_{$post_type}_per_page', etc.
		 *
		 * @since 2.9.0
		 *
		 * @param int $per_page Number of items to be displayed. Default 20.
		 */
		return (int) apply_filters( "{$option}", $per_page );
	}
	
    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items($search = NULL) {
        global $wpdb, $plugin_table_isub;
        $table_name = $wpdb->prefix . $plugin_table_isub; // do not forget about tables prefix

        // How much records will be shown per page (option screen)
		$screen   = get_current_screen();  // Get screen option
		$user     = get_current_user_id(); // Get current user ID
		$option   = $screen->get_option('per_page', 'option');
		$per_page = get_user_meta($user, $option, true);
		if ( empty ( $per_page) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}
		
        // here we configure table headers, defined in our methods
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
		$paged = !empty($_GET["paged"]) ? intval($_GET["paged"]) : '';		
		if (empty($paged) || !is_numeric($paged) || $paged <= 0) $paged = 1;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'date';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

		// If the value is not NULL, do a search for it
		if ( $search != NULL ) {
			// Trim Search Term
			$search = trim($search);
			/* Notice how you can search multiple columns for your search term easily, and return one data set */
			$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `date` LIKE '%%%s%%' OR `post_id` LIKE '%%%s%%' OR `image` LIKE '%%%s%%' ORDER BY $orderby $order", $search, $search, $search), ARRAY_A);
		} else {
			// [REQUIRED] define $items array
			// notice that last argument is ARRAY_A, so we will retrieve array
			if ($paged > 1)
				$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d,%d", $per_page, $paged), ARRAY_A);
			else
				$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d", $per_page), ARRAY_A);
		}
		
        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page'    => $per_page,    // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
		
    }
	
}

?>