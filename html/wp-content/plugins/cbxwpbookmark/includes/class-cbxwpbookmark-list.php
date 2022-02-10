<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class CBXWPBookmark_List_Table extends WP_List_Table {

	/**
	 * The current list of all branches.
	 *
	 * @since  3.1.0
	 * @access public
	 * @var array
	 */
	function __construct($args = array()) {
		//Set parent defaults
		parent::__construct( array(
			'singular' => 'cbxwpbookmarklist',     //singular name of the listed records
			'plural'   => 'cbxwpbookmarklists',    //plural name of the listed records
			'ajax'     => false,      //does this table support ajax?
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}

	/**
	 * Callback for column 'id'
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_id( $item ) {
		//return '<a href="' . admin_url( 'admin.php?page=cbxwpbookmark&view=view&id=' . $item['id'] ) . '" title="' . esc_html__( 'View Review', 'cbxwpbookmark' ) . '">' . $item['id'] . '</a>' . ' (<a target="_blank" href="' . admin_url( 'admin.php?page=cbxwpbookmark&view=addedit&id=' . $item['id'] ) . '" title="' . esc_html__( 'Edit Review', 'cbxwpbookmark' ) . '">' . esc_html__( 'Edit', 'cbxwpbookmark' ) . '</a>)';

		return intval( $item['id'] );
	}//end column-id


	/**
	 * Callback for column 'object_id'
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_object_id( $item ) {
		$post_id     = intval( $item['object_id'] );
		$object_type = esc_attr( $item['object_type'] );
		$setting     = new CBXWPBookmark_Settings_API();;
		$enable_buddypress_bookmark = intval( $setting->get_option( 'enable_buddypress_bookmark', 'cbxwpbookmark_proaddon', 0 ) );

		$object_types = CBXWPBookmarkHelper::object_types( true ); //get plain post type as array

		$edit_link = '';

		if ( in_array( $object_type, $object_types ) ) {
			$post_title = wp_strip_all_tags( get_the_title( intval( $post_id ) ) );
			$post_title = ( $post_title == '' ) ? esc_html__( 'Untitled article', 'cbxwpbookmark' ) : $post_title;
			$edit_link  = '<a target="_blank" href="' . get_permalink( $post_id ) . '">' . esc_html( $post_title ) . '</a>';

			$edit_url = esc_url( get_edit_post_link( $post_id ) );
			if ( ! is_null( $edit_url ) ) {
				$edit_link .= ' - <a target="_blank" href="' . $edit_url . '" target="_blank" title="' . esc_html__( 'Edit Post', 'cbxwpbookmark' ) . '">' . $post_id . '</a>';
			}

			return $post_id.' - '.$edit_link;
		} else if ( $enable_buddypress_bookmark && $object_type == 'buddypress_activity' && function_exists( 'bp_activity_get' ) ) {

			//$activity_get = bp_activity_get_specific( array( 'activity_ids' => array($object_id) ) );
			$args = array(
				//'ids' => $object_id,
				'in'       => $post_id,
				'per_page' => 1
			);

			$activity_get = bp_activity_get( $args );


			if ( isset( $activity_get['activities'][0] ) ) {
				$activity = $activity_get['activities'][0];

				$content = wp_strip_all_tags( $activity->content );
				$content = ( $content != '' ) ? $content : esc_html__( 'buddyPress Activity', 'cbxwpbookmark' );

				$edit_link = '<a target="_blank" href="' . bp_activity_get_permalink( $post_id ) . '">' . $content . '</a>';

				return $post_id.' - '.$edit_link;
			}
		}

		if ( $edit_link == '' ) {
			return $post_id.' - '.esc_html__( 'Untitled article', 'cbxwpbookmark' );
		}

	}//end column_object_id

	/**
	 * Callback for column 'object_type'
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_object_type( $item ) {
		return esc_attr( $item['object_type'] );
	}//end column_object_type

	/**
	 * Callback for column 'User'
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_user_id( $item ) {
		$user_id = absint( $item['user_id'] );

		$user_html = $user_id;

		if ( current_user_can( 'edit_user', $user_id ) ) {
			$user_html = '<a href="' . get_edit_user_link( $user_id ) . '" target="_blank" title="' . esc_html__( 'Edit User', 'cbxwpbookmark' ) . '">' . $user_id . '</a>';
		}


		return $user_html;
	}//end column_user_id

	/**
	 * Callback for column 'cat_id
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cat_id( $item ) {
		$cat_id = intval( $item['cat_id'] );
		if ( $cat_id > 0 ) {
			return '<a title="' . esc_html__( 'Click to edit category', 'cbxwpbookmark' ) . '" href="' . admin_url( 'admin.php?page=cbxwpbookmarkcats&view=edit&id=' . intval( $cat_id ) ) . '">' . esc_attr( $item['cat_name'] ) . '</a>';
		} else {
			return esc_html__( 'N/A', 'cbxwpbookmark' );
		}


	}//end column_object_type


	/**
	 * Callback for column 'Date Created'
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_created_date( $item ) {
		$created_date = '';
		if ( $item['created_date'] != '0000-00-00 00:00:00' ) {
			$created_date = CBXWPBookmarkHelper::dateReadableFormat( stripslashes( $item['created_date'] ) );
		}

		return $created_date;
	}//end column_created_date

	/**
	 * Callback for column 'Date Created'
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_modyfied_date( $item ) {
		$created_date = '';
		if ( $item['modyfied_date'] != '0000-00-00 00:00:00' ) {
			$created_date = CBXWPBookmarkHelper::dateReadableFormat( stripslashes( $item['modyfied_date'] ) );
		}

		return $created_date;
	}//end column_modyfied_date

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/
			$item['id']                //The value of the checkbox should be the record's id
		);
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				return $item[ $column_name ];
			case 'object_id':
				return $item[ $column_name ];
			case 'object_type':
				return $item[ $column_name ];
			case 'user_id':
				return $item[ $column_name ];
			case 'cat_id':
				return $item[ $column_name ];
			case 'created_date':
				return $item[ $column_name ];
			/*case 'modyfied_date':
				return $item[ $column_name ];*/
			default:
				//return print_r( $item, true ); //Show the whole array for troubleshooting purposes
				echo apply_filters( 'cbxwpbookmark_list_admin_column_default', $item, $column_name );
		}
	}//end column_default

	function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'           => esc_html__( 'ID', 'cbxwpbookmark' ),
			'object_id'    => esc_html__( 'Post', 'cbxwpbookmark' ),
			'object_type'  => esc_html__( 'Post Type', 'cbxwpbookmark' ),
			'user_id'      => esc_html__( 'User', 'cbxwpbookmark' ),
			'cat_id'       => esc_html__( 'Category', 'cbxwpbookmark' ),
			'created_date' => esc_html__( 'Created', 'cbxwpbookmark' ),
			//'modyfied_date' => esc_html__( 'Modified', 'cbxwpbookmark' )
		);

		return apply_filters( 'cbxwpbookmark_list_admin_columns', $columns );
	}//end get_columns


	function get_sortable_columns() {
		$sortable_columns = array(
			'id'           => array( 'logs.id', false ), //true means it's already sorted
			'object_id'    => array( 'logs.object_id', false ),
			'object_type'  => array( 'logs.object_type', false ),
			'user_id'      => array( 'logs.user_id', false ),
			'cat_id'       => array( 'logs.cat_id', false ),
			'created_date' => array( 'logs.created_date', false ),
			//'modyfied_date' => array( 'logs.modyfied_date', false ),
		);

		return apply_filters( 'cbxwpbookmark_list_admin_sortable_columns', $sortable_columns );
	}//end get_sortable_columns


	/**
	 * Bulk action method
	 *
	 * @return array|mixed|void
	 */
	function get_bulk_actions() {
		$status_arr           = array();
		$status_arr['delete'] = esc_html__( 'Delete', 'cbxwpbookmark' );

		$bulk_actions = apply_filters( 'cbxwpbookmark_list_admin_bulk_action', $status_arr );

		return $bulk_actions;
	}//end get_bulk_actions

	/**
	 * Process bulk action
	 */
	function process_bulk_action() {

		$new_status = $this->current_action();

		if ( $new_status == - 1 ) {
			return;
		}


		//Detect when a bulk action is being triggered...
		if ( ! empty( $_REQUEST['cbxwpbookmarklist'] ) ) {
			global $wpdb;

			$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

			$results = $_REQUEST['cbxwpbookmarklist'];
			foreach ( $results as $id ) {

				$id = intval( $id );

				$single_bookmark = CBXWPBookmarkHelper::singleBookmark( $id );


				if ( 'delete' === $new_status ) {
					do_action( 'cbxbookmark_bookmark_removed_before', $id, $single_bookmark['user_id'], $single_bookmark['object_id'], $single_bookmark['object_type'] );

					$delete_status = $wpdb->query( $wpdb->prepare( "DELETE FROM $bookmark_table WHERE id=%d", intval( $id ) ) );

					if ( $delete_status !== false ) {
						do_action( 'cbxbookmark_bookmark_removed', $id, $single_bookmark['user_id'], $single_bookmark['object_id'], $single_bookmark['object_type'] );
					}
				}
			}
		}

		return;
	}//end process_bulk_action


	/**
	 * Prepare the review log items
	 */
	function prepare_items() {
		global $wpdb; //This is used only if making any database queries

		$user   = get_current_user_id();
		$screen = get_current_screen();

		$current_page = $this->get_pagenum();

		$option_name = $screen->get_option( 'per_page', 'option' ); //the core class name is WP_Screen

		$perpage = intval( get_user_meta( $user, $option_name, true ) );

		if ( $perpage == 0 ) {
			$perpage = intval( $screen->get_option( 'per_page', 'default' ) );
		}


		$columns  = $this->get_columns();
		$hidden   = get_hidden_columns( $this->screen );
		$sortable = $this->get_sortable_columns();


		$this->_column_headers = array( $columns, $hidden, $sortable );


		$this->process_bulk_action();

		$search      = ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
		$id          = ( isset( $_REQUEST['id'] ) && $_REQUEST['id'] != 0 ) ? intval( $_REQUEST['id'] ) : 0;
		$object_id   = ( isset( $_REQUEST['object_id'] ) && $_REQUEST['object_id'] != 0 ) ? intval( $_REQUEST['object_id'] ) : 0;
		$object_type = ( isset( $_REQUEST['object_type'] ) && $_REQUEST['object_type'] != '' ) ? esc_attr( $_REQUEST['object_type'] ) : '';
		$cat_id      = ( isset( $_REQUEST['cat_id'] ) && $_REQUEST['cat_id'] != 0 ) ? intval( $_REQUEST['cat_id'] ) : 0;
		$user_id     = ( isset( $_REQUEST['user_id'] ) && $_REQUEST['user_id'] != 0 ) ? intval( $_REQUEST['user_id'] ) : 0;
		$order       = ( isset( $_REQUEST['order'] ) && $_REQUEST['order'] != '' ) ? $_REQUEST['order'] : 'DESC';
		$orderby     = ( isset( $_REQUEST['orderby'] ) && $_REQUEST['orderby'] != '' ) ? $_REQUEST['orderby'] : 'logs.id';


		$data = $this->getLogData( $search, $id, $object_id, $object_type, $cat_id, $user_id, $orderby, $order, $perpage, $current_page );

		$total_items = intval( $this->getLogDataCount( $search, $id, $object_id, $object_type, $cat_id, $user_id ) );

		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $perpage,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $perpage )   //WE have to calculate the total number of pages
		) );

	}

	/**
	 * Get bookmark logs
	 *
	 * @param string $search
	 * @param int $id
	 * @param int $object_id
	 * @param string $object_type
	 * @param int $cat_id
	 * @param int $user_id
	 * @param string $orderby
	 * @param string $order
	 * @param int $perpage
	 * @param int $page
	 *
	 * @return array|null|object
	 */
	public function getLogData( $search = '', $id = 0, $object_id = 0, $object_type = '', $cat_id = 0, $user_id = 0, $orderby = 'logs.id', $order = 'DESC', $perpage = 20, $page = 1 ) {

		global $wpdb;

		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';
		$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';


		$sql_select = "logs.*, cat.cat_name as cat_name";
		//$sql_select = "logs.*";

		$sql_select = apply_filters( 'cbxwpbookmark_list_admin_select', $sql_select, $search, $id, $object_id, $object_type, $cat_id, $user_id, $orderby, $order, $perpage, $page );

		$join = $where_sql = '';

		$join .= " LEFT JOIN $category_table  as cat ON cat.id = logs.cat_id ";

		//$join = " LEFT JOIN $table_users AS users ON users.ID = logs.user_id ";

		$join = apply_filters( 'cbxwpbookmark_list_admin_join', $join, $search, $id, $object_id, $object_type, $cat_id, $user_id, $orderby, $order, $perpage, $page );

		/*if ( $search != '' ) {
			if ( $where_sql != '' ) {
				$where_sql .= ' AND ';
			}

			$where_sql .= $wpdb->prepare( " logs.headline LIKE '%%%s%%' OR logs.comment LIKE '%%%s%%'", $search, $search );
		}*/


		if ( $object_id !== 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'logs.object_id=%d', intval( $object_id ) );
		}

		if ( $object_type !== '' ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'logs.object_type=%s', esc_attr( $object_type ) );
		}

		if ( $cat_id !== 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'logs.cat_id=%d', intval( $cat_id ) );
		}

		if ( $user_id !== 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'logs.user_id=%d', intval( $user_id ) );
		}

		$where_sql = apply_filters( 'cbxwpbookmark_list_admin_where', $where_sql, $search, $id, $object_id, $object_type, $cat_id, $user_id, $orderby, $order, $perpage, $page );

		if ( $where_sql == '' ) {
			$where_sql = '1';
		}

		$start_point = ( $page * $perpage ) - $perpage;
		$limit_sql   = "LIMIT";
		$limit_sql   .= ' ' . $start_point . ',';
		$limit_sql   .= ' ' . $perpage;

		$sortingOrder = " ORDER BY $orderby $order ";

		$data = $wpdb->get_results( "SELECT $sql_select FROM $bookmark_table as logs $join  WHERE  $where_sql $sortingOrder  $limit_sql", 'ARRAY_A' );

		return $data;
	}//end getLogData

	/**
	 * Bookmark total counter
	 *
	 * @param string $search
	 * @param int $id
	 * @param int $object_id
	 * @param string $object_type
	 * @param int $cat_id
	 * @param int $user_id
	 *
	 * @return null|string
	 */
	public function getLogDataCount( $search = '', $id = 0, $object_id = 0, $object_type = '', $cat_id = 0, $user_id = 0 ) {

		global $wpdb;

		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

		$sql_select = "SELECT COUNT(*) FROM $bookmark_table as logs";

		$join = $where_sql = '';

		$join = apply_filters( 'cbxwpbookmark_list_admin_join_total', $join, $search, $id, $object_id, $object_type, $cat_id, $user_id );

		/*if ( $search != '' ) {
		if ( $where_sql != '' ) {
			$where_sql .= ' AND ';
		}

		$where_sql .= $wpdb->prepare( " logs.headline LIKE '%%%s%%' OR logs.comment LIKE '%%%s%%'", $search, $search );
	}*/


		if ( $object_id !== 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'logs.object_id=%d', intval( $object_id ) );
		}

		if ( $object_type !== '' ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'logs.object_type=%s', esc_attr( $object_type ) );
		}

		if ( $cat_id !== 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'logs.cat_id=%d', intval( $cat_id ) );
		}

		if ( $user_id !== 0 ) {
			$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'logs.user_id=%d', intval( $user_id ) );
		}

		$where_sql = apply_filters( 'cbxwpbookmark_list_admin_where_total', $where_sql, $search, $id, $object_type, $object_id, $user_id, $cat_id );

		if ( $where_sql == '' ) {
			$where_sql = '1';
		}


		$count = $wpdb->get_var( "$sql_select $join  WHERE  $where_sql" );

		return $count;
	}//end getLogDataCount


	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item
	 *
	 * @since  3.1.0
	 * @access public
	 *
	 */
	public function single_row( $item ) {
		$row_class = 'cbxwpbookmark_list_row';
		$row_class = apply_filters( 'cbxwpbookmark_list_row_class', $row_class, $item );
		echo '<tr id="cbxwpbookmark_list_row_' . $item['id'] . '" class="' . $row_class . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}//end single_row

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since  3.1.0
	 * @access public
	 */
	public function no_items() {
		echo '<div class="notice notice-warning inline "><p>' . esc_html__( 'No bookmarks found. Please change your search criteria for better result.', 'cbxwpbookmark' ) . '</p></div>';
	}//end no_items

	/**
	 * Pagination
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {

		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items     = $this->_pagination_args['total_items'];
		$total_pages     = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current              = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = $disable_last = $disable_prev = $disable_next = false;

		if ( $current == 1 ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		if ( $current == 2 ) {
			$disable_first = true;
		}
		if ( $current == $total_pages ) {
			$disable_last = true;
			$disable_next = true;
		}
		if ( $current == $total_pages - 1 ) {
			$disable_last = true;
		}

		$pagination_params = array();

		$search = isset( $_REQUEST['s'] ) ? esc_attr( wp_unslash( $_REQUEST['s'] ) ) : '';
		//$logdate = ( isset( $_REQUEST['logdate'] ) && $_REQUEST['logdate'] != '' ) ? sanitize_text_field( $_REQUEST['logdate'] ) : '';

		if ( $search != '' ) {
			$pagination_params['s'] = $search;
		}

		/*if ($logdate != '') {
			$pagination_params['logdate'] = $logdate;
		}*/


		$pagination_params = apply_filters( 'cbxwpbookmark_pagination_log_params', $pagination_params );

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		} else {
			$pagination_params['paged'] = max( 1, $current - 1 );

			$page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( $pagination_params, $current_url ) ),
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		} else {
			$pagination_params['paged'] = min( $total_pages, $current + 1 );

			$page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( $pagination_params, $current_url ) ),
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		} else {
			$pagination_params['paged'] = $total_pages;

			$page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( $pagination_params, $current_url ) ),
				__( 'Last page' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			//$page_class = ' no-pages';
			$page_class = ' ';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}
}//end class CBXWPBookmark_List_Table