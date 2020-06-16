<?php
/**
 * CARTFLOWS Admin HTML.
 *
 * @package CARTFLOWS
 */

?>
<div class="wcf-menu-page-wrapper">
	<div id="wcf-menu-page">
		<div class="wcf-menu-page-header <?php echo esc_attr( implode( ' ', $header_wrapper_class ) ); ?>">
			<div class="wcf-container wcf-flex">
				<div class="wcf-title">
					<span class="screen-reader-text"><?php echo esc_attr( CARTFLOWS_PLUGIN_NAME ); ?></span>
					<img class="wcf-logo" src="<?php echo esc_attr( CARTFLOWS_URL ) . 'assets/images/cartflows-logo.svg'; ?>" />
				</div>
				<div class="wcf-top-links">
					<?php
						esc_attr_e( 'Modernizing WordPress eCommerce!', 'cartflows' );
					?>
				</div>
			</div>
		</div>

		<?php
		// Settings update message.
		if ( isset( $_REQUEST['message'] ) && ( 'saved' === $_REQUEST['message'] ) ) { //phpcs:ignore
			?>
				<div id="message" class="notice notice-success is-dismissive wcf-notice"><p> <?php esc_html_e( 'Settings saved successfully.', 'cartflows' ); ?> </p></div>
			<?php
		}
		?>
		<?php do_action( 'cartflows_render_admin_content', $menu_page_slug ); ?>
	</div>
</div>
