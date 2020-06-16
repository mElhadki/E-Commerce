<?php
/**
 * General Setting Form
 *
 * @package CARTFLOWS
 */

$extensions = array(
	'common' => array(
		'title'       => __( 'Selec', 'cartflows' ),
		'setting_url' => admin_url( 'admin.php?page=' . CARTFLOWS_SETTINGS . '&action=common-settings' ),
	),
);

?>

<div class="wcf-container wcf-<?php echo $action; ?>">
<div id="poststuff">
	<div id="post-body" class="columns-2">
		<div id="post-body-content">
			<!-- All WordPress Notices below header -->
			<h1 class="screen-reader-text"> <?php esc_html_e( 'General', 'cartflows' ); ?> </h1>
			<div class="widgets postbox">
				<h2 class="hndle wcf-flex wcf-widgets-heading"><span><?php esc_html_e( 'Welcome', 'cartflows' ); ?></span>
				</h2>
				<div class="inside">
					<ul class="wcf-setting-tab-wrapper" >
						<?php

						foreach ( $extensions as $key => $data ) {
							echo '<li id="' . esc_attr( $key ) . '" class="wcf-setting-tab ' . esc_attr( $key ) . '">';
								echo '<a class="wcf-tab-title" href="#" target="_blank" rel="noopener">' . esc_attr( $data['title'] ) . '</a>';
								echo '<div class="wcf-tab-link-wrapper">';
									echo '<a class="wcf-tab-link" href="' . esc_url( $data['setting_url'] ) . '">' . esc_html__( 'Settings', 'cartflows' ) . '</a>';
								echo '</div>';
							echo '</li>';
						}
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<!-- /post-body -->
	<br class="clear">
</div>
</div>
