<?php
/**
 * Importer
 *
 * @package CartFlows
 */

?>
<div class="cartflows-ie">
	<div class="postbox">
		<div class="inside">
			<h3><?php esc_html_e( 'Import Flows to a JSON file', 'cartflows' ); ?></h3>
			<p><?php esc_html_e( 'This tool allows you to import the flows from the JSON file.', 'cartflows' ); ?></p>
			<form method="post" enctype="multipart/form-data">
				<p>
					<input type="file" name="file"/>
					<input type="hidden" name="cartflows-action" value="import" />
				</p>
				<p style="margin-bottom:0">
					<?php wp_nonce_field( 'cartflows-action-nonce', 'cartflows-action-nonce' ); ?>
					<?php submit_button( __( 'Import', 'cartflows' ), 'button-primary', 'submit', false, array( 'id' => '' ) ); ?>
				</p>
			</form>
		</div>
	</div>
</div>
