<?php
/**
 * Cloning.
 *
 * @package cartflows-pro
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Cloning {


	/**
	 * Member Variable
	 *
	 * @var instance
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
	 *  Constructor
	 */
	public function __construct() {

		add_filter( 'post_row_actions', array( $this, 'clone_link' ), 99, 2 );
		add_action( 'admin_action_cartflows_clone_flow', array( $this, 'clone_flow' ) );
		add_action( 'admin_action_cartflows_clone_step', array( $this, 'clone_step' ) );
	}

	/**
	 * Clone flow with steps and its meta.
	 */
	public function clone_flow() {

		global $wpdb;

		if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'cartflows_clone_flow' === $_REQUEST['action'] ) ) ) {
			wp_die( 'No post to duplicate has been supplied!' );
		}

		/*
		 * Nonce verification
		 */
		if ( ! isset( $_GET['flow_clone_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['flow_clone_nonce'] ) ), basename( __FILE__ ) ) ) {
			return;
		}

		/**
		 * Get the original post id
		 */
		$post_id = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );

		/**
		 * And all the original post data then
		 */
		$post = get_post( $post_id );

		/**
		 * Assign current user to be the new post author
		 */
		$current_user    = wp_get_current_user();
		$new_post_author = $current_user->ID;

		/**
		 * If post data exists, create the post duplicate
		 */
		if ( isset( $post ) && null !== $post ) {

			/**
			 * New post data array
			 */

			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => $post->post_status,
				'post_title'     => $post->post_title . ' Clone',
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			);

			/**
			 * Insert the post
			 */
			$new_flow_id = wp_insert_post( $args );

			/**
			 * Get all current post terms ad set them to the new post
			 */
			// returns array of taxonomy names for post type, ex array("category", "post_tag");.
			$taxonomies = get_object_taxonomies( $post->post_type );

			foreach ( $taxonomies as $taxonomy ) {

				$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );

				wp_set_object_terms( $new_flow_id, $post_terms, $taxonomy, false );
			}

			/**
			 * Duplicate all post meta just in two SQL queries
			 */
			// @codingStandardsIgnoreStart
			$post_meta_infos = $wpdb->get_results(
				"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id"
			);
			// @codingStandardsIgnoreEnd

			if ( ! empty( $post_meta_infos ) ) {

				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ";

				$sql_query_sel = array();

				foreach ( $post_meta_infos as $meta_info ) {

					$meta_key = $meta_info->meta_key;

					if ( '_wp_old_slug' === $meta_key ) {
						continue;
					}

					$meta_value = addslashes( $meta_info->meta_value );

					$sql_query_sel[] = "($new_flow_id, '$meta_key', '$meta_value')";
				}

				$sql_query .= implode( ',', $sql_query_sel );

				// @codingStandardsIgnoreStart
				$wpdb->query( $sql_query );
    			// @codingStandardsIgnoreEnd
			}

			/* Steps Cloning */
			$flow_steps     = get_post_meta( $post_id, 'wcf-steps', true );
			$new_flow_steps = array();

			/* Set Steps Empty */
			update_post_meta( $new_flow_id, 'wcf-steps', $new_flow_steps );

			if ( is_array( $flow_steps ) && ! empty( $flow_steps ) ) {

				foreach ( $flow_steps as $index => $step_data ) {

					$step_id   = $step_data['id'];
					$step_type = get_post_meta( $step_id, 'wcf-step-type', true );

					$step_object = get_post( $step_id );

					/**
					 * New step post data array
					 */
					$step_args = array(
						'comment_status' => $step_object->comment_status,
						'ping_status'    => $step_object->ping_status,
						'post_author'    => $new_post_author,
						'post_content'   => $step_object->post_content,
						'post_excerpt'   => $step_object->post_excerpt,
						'post_name'      => $step_object->post_name,
						'post_parent'    => $step_object->post_parent,
						'post_password'  => $step_object->post_password,
						'post_status'    => $step_object->post_status,
						'post_title'     => $step_object->post_title,
						'post_type'      => $step_object->post_type,
						'to_ping'        => $step_object->to_ping,
						'menu_order'     => $step_object->menu_order,
					);

					/**
					 * Insert the post
					 */
					$new_step_id = wp_insert_post( $step_args );

					/**
					 * Duplicate all step meta
					 */
					// @codingStandardsIgnoreStart
					$post_meta_infos = $wpdb->get_results(
						"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$step_id"
					);
					// @codingStandardsIgnoreEnd

					if ( ! empty( $post_meta_infos ) ) {

						$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ";

						$sql_query_sel = array();

						foreach ( $post_meta_infos as $meta_info ) {

							$meta_key = $meta_info->meta_key;

							if ( '_wp_old_slug' === $meta_key ) {
								continue;
							}

							$meta_value = addslashes( $meta_info->meta_value );

							$sql_query_sel[] = "($new_step_id, '$meta_key', '$meta_value')";
						}

						$sql_query .= implode( ',', $sql_query_sel );

						// @codingStandardsIgnoreStart
						$wpdb->query( $sql_query );
		    			// @codingStandardsIgnoreEnd
					}

					// insert post meta.
					update_post_meta( $new_step_id, 'wcf-flow-id', $new_flow_id );
					update_post_meta( $new_step_id, 'wcf-step-type', $step_type );

					wp_set_object_terms( $new_step_id, $step_type, CARTFLOWS_TAXONOMY_STEP_TYPE );
					wp_set_object_terms( $new_step_id, 'flow-' . $new_flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );

					/* Add New Flow Steps */
					$new_flow_steps[] = array(
						'id'    => $new_step_id,
						'title' => $step_object->post_title,
						'type'  => $step_type,
					);
				}
			}

			/* Update New Flow Step Post Meta */
			update_post_meta( $new_flow_id, 'wcf-steps', $new_flow_steps );

			/* Clear Page Builder Cache */
			$this->clear_cache();

			/**
			 * Redirect to the new flow edit screen
			 */
			wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_flow_id ) );
			exit;
		} else {
			wp_die( 'Post creation failed, could not find original post: ' . $post_id );
		}
	}

	/**
	 * Clone step with its meta.
	 */
	public function clone_step() {

		global $wpdb;

		if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'cartflows_clone_step' === $_REQUEST['action'] ) ) ) {
			wp_die( 'No post to duplicate has been supplied!' );
		}

		/*
		 * Nonce verification
		 */
		if ( ! isset( $_GET['step_clone_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['step_clone_nonce'] ) ), 'step_clone' ) ) {
			return;
		}

		/**
		 * Get the original post id
		 */
		$post_id = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );

		/**
		 * And all the original post data then
		 */
		$post = get_post( $post_id );

		/**
		 * Assign current user to be the new post author
		 */
		$current_user    = wp_get_current_user();
		$new_post_author = $current_user->ID;

		/**
		 * If post data exists, create the post duplicate
		 */
		if ( isset( $post ) && null !== $post ) {

			/**
			 * New post data array
			 */
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => $post->post_status,
				'post_title'     => $post->post_title . ' Clone',
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			);

			/**
			 * Insert the post
			 */
			$new_step_id = wp_insert_post( $args );

			/**
			 * Get all current post terms ad set them to the new post
			 */
			// returns array of taxonomy names for post type, ex array("category", "post_tag");.
			$taxonomies = get_object_taxonomies( $post->post_type );

			foreach ( $taxonomies as $taxonomy ) {

				$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );

				wp_set_object_terms( $new_step_id, $post_terms, $taxonomy, false );
			}

			/**
			 * Duplicate all post meta just in two SQL queries
			 */
			// @codingStandardsIgnoreStart
			$post_meta_infos = $wpdb->get_results(
				"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id"
			);
			// @codingStandardsIgnoreEnd

			if ( ! empty( $post_meta_infos ) ) {

				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ";

				$sql_query_sel = array();

				foreach ( $post_meta_infos as $meta_info ) {

					$meta_key = $meta_info->meta_key;

					if ( '_wp_old_slug' === $meta_key ) {
						continue;
					}

					$meta_value = addslashes( $meta_info->meta_value );

					$sql_query_sel[] = "($new_step_id, '$meta_key', '$meta_value')";
				}

				$sql_query .= implode( ',', $sql_query_sel );

				// @codingStandardsIgnoreStart
				$wpdb->query( $sql_query );
    			// @codingStandardsIgnoreEnd
			}

			$flow_id    = get_post_meta( $post_id, 'wcf-flow-id', true );
			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );
			$step_type  = get_post_meta( $post_id, 'wcf-step-type', true );

			if ( ! is_array( $flow_steps ) ) {
				$flow_steps = array();
			}

			$flow_steps[] = array(
				'id'    => $new_step_id,
				'title' => $post->post_title,
				'type'  => $step_type,
			);

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			/* Clear Page Builder Cache */
			$this->clear_cache();

			/**
			 * Redirect to the new flow edit screen
			 */
			$redirect_url = add_query_arg( 'highlight-step-id', $new_step_id, get_edit_post_link( $flow_id, 'default' ) );

			wp_safe_redirect( $redirect_url );
			exit;
		} else {
			wp_die( 'Post creation failed, could not find original post: ' . $post_id );
		}
	}

	/**
	 * Add the clone link to action list for flows row actions
	 *
	 * @param array  $actions Actions array.
	 * @param object $post Post object.
	 *
	 * @return array
	 */
	public function clone_link( $actions, $post ) {

		if ( current_user_can( 'edit_posts' ) && isset( $post ) && CARTFLOWS_FLOW_POST_TYPE === $post->post_type ) {

			if ( isset( $actions['duplicate'] ) ) { // Duplicate page plugin remove.
				unset( $actions['duplicate'] );
			}
			if ( isset( $actions['edit_as_new_draft'] ) ) { // Duplicate post plugin remove.
				unset( $actions['edit_as_new_draft'] );
			}

			$actions['clone'] = '<a href="' . wp_nonce_url( 'admin.php?action=cartflows_clone_flow&post=' . $post->ID, basename( __FILE__ ), 'flow_clone_nonce' ) . '" title="' . __( 'Clone this flow', 'cartflows' ) . '" rel="permalink">' . __( 'Clone', 'cartflows' ) . '</a>';

			if ( ! _is_cartflows_pro() ) {

				$flow_posts = get_posts(
					array(
						'posts_per_page' => 4,
						'post_type'      => CARTFLOWS_FLOW_POST_TYPE,
						'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
					)
				);

				if ( is_array( $flow_posts ) ) {

					$flow_count = count( $flow_posts );

					if ( $flow_count > 3 || 3 === $flow_count ) {
						unset( $actions['clone'] );
					}
				}
			}
		}

		return $actions;
	}

	/**
	 * Clear Page Builder Cache
	 */
	public function clear_cache() {

		// Clear 'Elementor' file cache.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Cloning::get_instance();
