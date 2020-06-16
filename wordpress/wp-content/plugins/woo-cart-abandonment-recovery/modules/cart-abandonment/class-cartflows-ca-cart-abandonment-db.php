<?php
/**
 * Cart Abandonment DB
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

/**
 * Cart Abandonment DB class.
 */
class Cartflows_Ca_Cart_Abandonment_Db {



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
	 *  Create tables
	 */
	public function create_tables() {
		$this->create_cart_abandonment_table();
		$this->create_cart_abandonment_template_table();
		$this->create_email_templates_meta_table();
		$this->create_email_history_table();
	}

	/**
	 *  Create Email templates meta table.
	 */
	public function create_email_templates_meta_table() {
		global $wpdb;

		$email_template_meta_db       = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_META_TABLE;
		$cart_abandonment_template_db = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;
		$charset_collate              = $wpdb->get_charset_collate();

		// Email templates meta table db sql command.
		$sql = "CREATE TABLE IF NOT EXISTS $email_template_meta_db (
		`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
		`email_template_id` BIGINT(20) NOT NULL,
		`meta_key` varchar(255) NOT NULL,
		`meta_value` longtext NOT NULL,
		PRIMARY KEY (`id`),
		FOREIGN KEY ( `email_template_id` )  REFERENCES $cart_abandonment_template_db(`id`) ON DELETE CASCADE
		) $charset_collate;\n";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}

	/**
	 *  Create tables for analytics.
	 */
	public function create_cart_abandonment_table() {

		global $wpdb;

		$cart_abandonment_db = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;
		$charset_collate     = $wpdb->get_charset_collate();

		// Cart abandonment tracking db sql command.
		$sql = "CREATE TABLE IF NOT EXISTS $cart_abandonment_db (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			checkout_id int(11) NOT NULL, 
			email VARCHAR(100),
			cart_contents LONGTEXT,
			cart_total DECIMAL(10,2),
			session_id VARCHAR(60) NOT NULL,
			other_fields LONGTEXT,
			order_status ENUM( 'normal','abandoned','completed','lost') NOT NULL DEFAULT 'normal',
			unsubscribed  boolean DEFAULT 0,
			coupon_code VARCHAR(50),
   			time DATETIME DEFAULT NULL,
			PRIMARY KEY  (`id`, `session_id`),
			UNIQUE KEY `session_id_UNIQUE` (`session_id`)
		) $charset_collate;\n";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}

	/**
	 *  Create tables for analytics.
	 */
	public function create_cart_abandonment_template_table() {

		global $wpdb;

		$cart_abandonment_template_db = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;

		$charset_collate = $wpdb->get_charset_collate();

		// Cart abandonment tracking db sql command.
		$sql = "CREATE TABLE IF NOT EXISTS $cart_abandonment_template_db (
			 `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
             `template_name` text NOT NULL,
             `email_subject` text NOT NULL,
             `email_body` mediumtext NOT NULL,
             `is_activated` tinyint(1) NOT NULL DEFAULT '0',
             `frequency` int(11) NOT NULL,
             `frequency_unit` ENUM( 'MINUTE','HOUR','DAY') NOT NULL DEFAULT 'MINUTE',
             PRIMARY KEY (`id`)
		) $charset_collate;\n";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}

	/**
	 *  Create tables for analytics.
	 */
	public function create_email_history_table() {

		global $wpdb;

		$cart_abandonment_history_db  = $wpdb->prefix . CARTFLOWS_CA_EMAIL_HISTORY_TABLE;
		$cart_abandonment_db          = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;
		$cart_abandonment_template_db = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $cart_abandonment_history_db (
			 `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
			 `template_id` BIGINT(20) NOT NULL,
			 `ca_session_id` VARCHAR(60),
			 `coupon_code` VARCHAR(50),
			 `scheduled_time` DATETIME,
			 `email_sent` boolean DEFAULT 0,   
			  PRIMARY KEY (`id`),
			  FOREIGN KEY ( `template_id` )  REFERENCES $cart_abandonment_template_db(`id`) ON DELETE CASCADE,
			  FOREIGN KEY ( `ca_session_id` )  REFERENCES $cart_abandonment_db(`session_id`) ON DELETE CASCADE
		) $charset_collate;\n";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}

	/**
	 *  Insert initial sample email templates.
	 *
	 * @param boolean $force_restore restore forcefully.
	 */
	public function template_table_seeder( $force_restore = false ) {
		global $wpdb;
		$cart_abandonment_template_db      = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;
		$cart_abandonment_template_meta_db = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_META_TABLE;

		$email_template_count = $wpdb->get_var( "SELECT COUNT(*) FROM $cart_abandonment_template_db" ); // phpcs:ignore

		if ( ( ! $email_template_count ) || $force_restore ) {

			$email_templates = array(
				array(
					'template_name'  => 'Sample Email Template 1',
					'subject'        => 'Purchase issue?',
					'body'           => "<p>Hi {{customer.firstname}}!</p><p>We\'re having trouble processing your recent purchase. Would you mind completing it?</p><p>Here\'s a link to continue where you left off:</p><p><a href='{{cart.checkout_url}}' target='_blank' rel='noopener'> Continue Your Purchase Now </a></p><p>Kindly,<br />{{admin.firstname}}<br />{{admin.company}}</p><p>{{cart.unsubscribe}}</p>",
					'frequency'      => 30,
					'frequency_unit' => 'MINUTE',
				),
				array(
					'template_name'  => 'Sample Email Template 2',
					'subject'        => 'Need help?',
					'body'           => "<p>Hi {{customer.firstname}}!</p><p>I'm {{admin.firstname}}, and I help handle customer issues at {{admin.company}}.</p><p>I just noticed that you tried to make a purchase, but unfortunately, there was some trouble. Is there anything I can do to help?</p><p>You should be able to complete your checkout in less than a minute:<br /><a href='{{cart.checkout_url}}' target='_blank' rel='noopener'> Click here to continue your purchase </a><p><p>Thanks!<br />{{admin.firstname}}<br />{{admin.company}}</p><p>{{cart.unsubscribe}}</p>",
					'frequency'      => 1,
					'frequency_unit' => 'DAY',
				),
				array(
					'template_name'  => 'Sample Email Template 3',
					'subject'        => 'Exclusive discount for you. Let\'s get things started!',
					'body'           => "<p>Few days back you left {{cart.product.names}} in your cart.</p><p>To help make up your mind, we have added an exclusive 10% discount coupon {{cart.coupon_code}} to your cart.</p><p><a href='{{cart.checkout_url}}' target='_blank' rel='noopener'>Complete Your Purchase Now &gt;&gt;</a></p><p>Hurry! This is a onetime offer and will expire in 24 Hours.</p><p>In case you couldn\'t finish your order due to technical difficulties or because you need some help, just reply to this email we will be happy to help.</p><p>Kind Regards,<br />{{admin.firstname}}<br />{{admin.company}}</p><p>{{cart.unsubscribe}}</p>",
					'frequency'      => 3,
					'frequency_unit' => 'DAY',
				),
			);

            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$template_index      = 1;
			$template_meta_index = 1;
			foreach ( $email_templates as $email_template ) {
				$wpdb->query(
					$wpdb->prepare(
						"INSERT INTO $cart_abandonment_template_db (`id`, `template_name`, `email_subject`, `email_body`, `frequency`, `frequency_unit`) 
				   VALUES ( %d, %s, %s, %s, %d, %s )",
						$force_restore ? null : $template_index++,
						$email_template['template_name'],
						$email_template['subject'],
						$email_template['body'],
						$email_template['frequency'],
						$email_template['frequency_unit']
					)
				);

				$meta_data = array(
					'override_global_coupon' => false,
					'discount_type'          => 'percent',
					'coupon_amount'          => 10,
					'coupon_expiry_date'     => '',
					'coupon_expiry_unit'     => 'hours',
				);

				$email_tmpl_id = $wpdb->insert_id;

				foreach ( $meta_data as $meta_key => $meta_value ) {
					$wpdb->query(
						$wpdb->prepare(
							"INSERT INTO $cart_abandonment_template_meta_db ( `id`, `email_template_id`, `meta_key`, `meta_value` ) 
						   VALUES ( %d, %d, %s, %s )",
							$force_restore ? null : $template_meta_index++,
							$email_tmpl_id,
							$meta_key,
							$meta_value
						)
					);
				}
			}
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

	}
}

Cartflows_Ca_Cart_Abandonment_Db::get_instance();
