<?php
/**
 * Exporter
 *
 * @package CartFlows
 */

?>
<div class="cartflows-ie">
	<div class="postbox">
		<div class="inside">
			<h3><?php esc_html_e( 'Export Flows to a JSON file', 'cartflows' ); ?></h3>
			<p><?php esc_html_e( 'This tool allows you to generate and download a JSON file containing a list of all flows.', 'cartflows' ); ?></p>					
			<form method="post">
				<p><input type="hidden" name="cartflows-action" value="export" /></p>
				<p style="margin-bottom:0">
					<?php wp_nonce_field( 'cartflows-action-nonce', 'cartflows-action-nonce' ); ?>
					<?php submit_button( __( 'Export', 'cartflows' ), 'button-primary', 'submit', false, array( 'id' => '' ) ); ?>
				</p>
			</form>
		</div>
	</div>
</div>
