<?php
/**
 * Template Name: No Header Footer
 *
 * @package CartFlows
 */

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php
		wp_body_open();

		do_action( 'cartflows_body_top' );
	?>

	<?php

	$atts_string = Cartflows_Helper::get_cartflows_container_atts();

	?>
	<div class="cartflows-container" <?php echo trim( $atts_string ); ?>>
		<?php
			do_action( 'cartflows_container_top' );
		?>
		<div class="cartflows-primary">
		<?php
		while ( have_posts() ) :

			the_post();
			the_content();

		endwhile;
		?>
		</div>
		<?php
			do_action( 'cartflows_container_bottom' );
		?>
	</div>

	<?php do_action( 'cartflows_wp_footer' ); ?>

	<?php wp_footer(); ?>
</body>

</html>

<?php
