<?php

use WeglotWP\Helpers\Helper_Post_Meta_Weglot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


global $pagenow;

if($pagenow === 'post-new.php'){
	esc_html_e( 'You must first create the page a first time before you can benefit from custom URLs', 'weglot' );
}
else{

	$languages_available     = $this->language_services->get_languages_configured();
	$original_language       = weglot_get_original_language();
	list( $permalink )       = get_sample_permalink( $post->ID );
	$display_link            = str_replace( array( '%pagename%', '%postname%', home_url() ), '', $permalink );
	$display_link            = implode( '/', array_filter( explode( '/', $display_link ), 'strlen' ) );

	if ( ! empty( $display_link ) && '/' !== $display_link[ strlen( $display_link ) - 1 ] ) {
		$display_link .= '/';
	}

	?>
	<input type="hidden" id="weglot_post_id" data-id="<?php echo esc_attr( $post->ID ); ?>" />
	<?php
	foreach ( $languages_available as $language ) {
		$code                = $language->getIso639();
		if ( $code === $original_language ) {
			continue;
		}

		$post_name_weglot = $post->post_name;
		$post_name_input  = '';
		if ( isset( $this->custom_urls[ $code ] ) ) {
			$post_name_weglot = array_search( $post_name_weglot, $this->custom_urls[ $code ] );
			if ( false === $post_name_weglot || empty( $post_name_weglot ) ) {
				$post_name_weglot = $post->post_name;
			} else {
				$post_name_input = $post_name_weglot;
			}
		} ?>
		<label for="lang-<?php echo esc_attr( $code ); ?>">
			<strong><?php echo esc_html( $language->getLocalName() ); ?></strong>
		</label>
		<div class="weglot_custom_url">
			<p class="weglot_custom_url--text_link">
				<?php echo esc_url( home_url() ); ?>/<?php echo esc_html( $code ); ?>/<?php echo esc_html( $display_link ); ?><span id="text-edit-<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $post_name_weglot ); ?></span>
				<input type="text" id="lang-<?php echo esc_attr( $code ); ?>" name="post_name_weglot[<?php echo esc_attr( $code ); ?>]" value="<?php echo esc_attr( $post_name_input ); ?>" style="display:none;"/>

				<button type="button" class="button button-small button-weglot-lang" data-lang="<?php echo esc_attr( $code ); ?>" aria-label="Edit permalink weglot"><span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit', 'weglot' ); ?></button>

				<button type="button" class="button button-small button-weglot-lang-submit" data-lang="<?php echo esc_attr( $code ); ?>" style="display:none;"><?php esc_html_e( 'Ok', 'weglot' ); ?></button>
			</p>
			<p id="weglot_permalink_not_available_<?php echo esc_attr( $code ); ?>" class="weglot_text_error" style="display:none;"><?php esc_html_e( 'The permalink is not available.', 'weglot' ); ?></p>
			<a id="weglot_reset_custom_<?php echo esc_attr( $code ); ?>" data-lang="<?php echo esc_attr( $code ); ?>" data-id="<?php echo esc_attr( $post->ID ); ?>" href="<?php echo esc_attr( $post_name_weglot ) ; ?>" class="weglot_reset">
				<span class="dashicons dashicons-update-alt"></span> <?php esc_html_e( 'Reset custom url', 'weglot' ); ?>
			</a>
		</div>

		<?php
	}
}

