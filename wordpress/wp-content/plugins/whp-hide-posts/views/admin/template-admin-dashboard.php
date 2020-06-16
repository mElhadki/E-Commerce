<div class="wrap">
	<h1><?php _e( 'Wordpress Hide Posts Settings', 'whp' ); ?></h1>
    <hr>
	<form method="post" action="options.php">
	    <?php settings_fields( 'whp-settings-group' ); ?>
	    <?php do_settings_sections( 'whp-settings-group' ); ?>

        <div class="rwp-post-types">
            <p><?php _e( 'Additionally enable Hide Posts functionality on the following post types:', 'whp' ); ?></p>
            <?php foreach ( $post_types as $post_type ) :
                if ( $post_type->name === 'post' ) continue; ?>
            <span class="whp-post-type">
                <label for="<?php echo $post_type->name; ?>">
                    <input
                        type="checkbox"
                        name="whp_enabled_post_types[]"
                        value="<?php echo $post_type->name; ?>"
                        id="<?php echo $post_type->name; ?>"
                        <?php echo in_array( $post_type->name, $enabled_post_types ) ? 'checked' : ''; ?>>
                    <?php echo ucfirst( $post_type->name ); ?>
                </label>
            </span>
            <?php endforeach; ?>
        </div>

	    <?php submit_button(); ?>
	</form>
</div>
