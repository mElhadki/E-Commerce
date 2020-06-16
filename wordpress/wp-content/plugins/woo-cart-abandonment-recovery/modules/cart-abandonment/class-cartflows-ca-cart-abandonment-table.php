<?php
/**
 * Cart Abandonment
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Cart abandonment tracking table class.
 */
class Cartflows_Ca_Cart_Abandonment_Table extends WP_List_Table {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor function.
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct(
			array(
				'singular' => 'id',
				'plural'   => 'ids',
			)
		);
	}

	/**
	 * Default columns.
	 *
	 * @param object $item        item.
	 * @param string $column_name column name.
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Column name surname.
	 *
	 * @param  object $item item.
	 * @return string
	 */
	public function column_nameSurname( $item ) {

		$item_details = unserialize( $item['other_fields'] );

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		$view_url = add_query_arg(
			array(
				'page'       => WCF_CA_PAGE_NAME,
				'action'     => WCF_ACTION_REPORTS,
				'sub_action' => WCF_SUB_ACTION_REPORTS_VIEW,
				'session_id' => sanitize_text_field( $item['session_id'] ),
			),
			admin_url( '/admin.php' )
		);

		$actions = array(
			'view'   => sprintf( '<a href="%s">%s</a>', esc_url( $view_url ), __( 'View', 'woo-cart-abandonment-recovery' ) ),
			'delete' => sprintf( '<a onclick="return confirm(\'Are you sure to delete this order?\');" href="?page=%s&action=delete&id=%s">%s</a>', esc_html( $page ), esc_html( $item['id'] ), __( 'Delete', 'woo-cart-abandonment-recovery' ) ),
		);

		if ( WCF_CART_ABANDONED_ORDER === $item['order_status'] && ! $item['unsubscribed'] ) {
			$actions['unsubscribe'] = sprintf( '<a onclick="return confirm(\'Are you sure to unsubscribe this user? \');" href="?page=%s&action=unsubscribe&id=%s">%s</a>', esc_html( $page ), esc_html( $item['id'] ), __( 'Unsubscribe', 'woo-cart-abandonment-recovery' ) );

		}

		return sprintf(
			'<a href="%s"><span class="dashicons dashicons-admin-users"></span> %s %s %s </a>',
			esc_url( $view_url ),
			esc_html( $item_details['wcf_first_name'] ),
			esc_html( $item_details['wcf_last_name'] ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Render date column
	 *
	 * @param  object $item - row (key, value array).
	 * @return HTML
	 */
	public function column_time( $item ) {
		$database_time    = $item['time'];
		$date_time        = new DateTime( $database_time );
		$date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		$date_time        = $date_time->format( $date_time_format );

		return sprintf( '<span class="dashicons dashicons-clock"></span> %s', esc_html( $date_time ) );
	}

	/**
	 * This is how checkbox column renders.
	 *
	 * @param  object $item item.
	 * @return HTML
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			esc_html( $item['id'] )
		);
	}

	/**
	 * [OPTIONAL] Return array of bult actions if has any
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions      = array(
			'delete' => __( 'Delete', 'woo-cart-abandonment-recovery' ),
		);
		$filter_table = isset( $_GET['filter_table'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_table'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $filter_table ) || ( isset( $filter_table ) && WCF_CART_ABANDONED_ORDER === $filter_table ) ) {
			$actions['unsubscribe'] = __( 'Unsubscribe', 'woo-cart-abandonment-recovery' );
		}

		return $actions;
	}

	/**
	 * Whether the table has items to display or not
	 *
	 * @return bool
	 */
	public function has_items() {
		return ! empty( $this->items );
	}

	/**
	 * Fetch data from the database to render on view.
	 *
	 * @param string $cart_type abandoned|completed.
	 * @param string $from_date from date.
	 * @param string $to_date to date.
	 */
	public function prepare_items( $cart_type = WCF_CART_ABANDONED_ORDER, $from_date = '', $to_date = '' ) {
		global $wpdb;
		$cart_abandonment_table_name = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

		$per_page = 10;

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$paged       = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );
		$orderby     = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
		$order       = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING );
		$search_term = filter_input( INPUT_GET, 'search_term', FILTER_SANITIZE_STRING );

		$paged   = $paged ? max( 0, $paged - 1 ) : 0;
		$orderby = ( $orderby && in_array( $orderby, array_keys( $this->get_sortable_columns() ), true ) ) ? $orderby : 'id';
		$order   = ( $order && in_array( $order, array( 'asc', 'desc' ), true ) ) ? $order : 'desc';

		$this->items = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$cart_abandonment_table_name} WHERE `order_status` = %s AND DATE(`time`) >= %s AND DATE(`time`) <= %s AND `email` LIKE '%%%s%%'  ORDER BY %s %s LIMIT %d OFFSET %d", //phpcs:ignore
				$cart_type,
				$from_date,
				$to_date,
				$wpdb->esc_like( $search_term ),
				$orderby,
				$order,
				$per_page,
				$paged * $per_page
			),
			ARRAY_A
		);

		$total_items = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM $cart_abandonment_table_name WHERE `order_status` = %s AND DATE(`time`) >= %s AND DATE(`time`) <= %s", $cart_type, $from_date, $to_date)); // phpcs:ignore

		// [REQUIRED] configure pagination
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);

		$export_data = filter_input( INPUT_GET, 'export_data', FILTER_VALIDATE_BOOLEAN );
		if ( $export_data ) {

			$this->items = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$cart_abandonment_table_name} WHERE `order_status` = %s AND DATE(`time`) >= %s AND DATE(`time`) <= %s AND `email` LIKE '%%%s%%'  ORDER BY %s %s", //phpcs:ignore
					$cart_type,
					$from_date,
					$to_date,
					$wpdb->esc_like( $search_term ),
					$orderby,
					$order
				),
				ARRAY_A
			);
			return $this->items;
		}
	}

	/**
	 * Table columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'nameSurname'  => __( 'Name', 'woo-cart-abandonment-recovery' ),
			'email'        => __( 'Email', 'woo-cart-abandonment-recovery' ),
			'cart_total'   => __( 'Cart Total', 'woo-cart-abandonment-recovery' ),
			'order_status' => __( 'Order Status', 'woo-cart-abandonment-recovery' ),
			'time'         => __( 'Time', 'woo-cart-abandonment-recovery' ),
		);
		return $columns;
	}

	/**
	 * Table sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable = array(
			'nameSurname'  => array( 'name', true ),
			'cart_total'   => array( 'cart_total', true ),
			'cart_total'   => array( 'Cart Total', true ),
			'order_status' => array( 'Order Status', true ),
			'time'         => array( 'time', true ),
		);
		return $sortable;
	}

	/**
	 * Processes bulk actions
	 */
	public function process_bulk_action() {
		global $wpdb;
		$table_name = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

		$ids        = array();
		$request_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : ''; //phpcs:ignore
		if ( is_array( $request_id ) ) {
			$ids = array_map( 'intval', $request_id );
		} elseif ( isset( $request_id ) ) {
			$ids = array( intval( $request_id ) );
		}
		$ids = implode( ',', $ids );
		if ( ! empty( $ids ) ) {

			switch ( $this->current_action() ) {

				case 'delete':
                    $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)"); // phpcs:ignore
					break;
				case 'unsubscribe':
                    $wpdb->query( "UPDATE {$table_name} SET unsubscribed = 1 WHERE id IN($ids)" ); // phpcs:ignore
					break;

			}
		}

	}


}
