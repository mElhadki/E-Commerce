<?php
/**
 * Get Field.
 *
 * @package CartFlows
 */

$label      = isset( $field_data['label'] ) ? $field_data['label'] : '';
$help       = isset( $field_data['help'] ) ? $field_data['help'] : '';
$after_html = isset( $field_data['after_html'] ) ? $field_data['after_html'] : '';

$name_class = 'field-' . $field_data['name'];

if ( isset( $field_data['field_type'] ) ) {
	$name_class .= ' wcf-field-' . $field_data['field_type'];
}
?>

<div class="wcf-field-row <?php echo $name_class; ?>">

	<?php if ( ! empty( $label ) || ! empty( $help ) ) { ?>
	<div class="wcf-field-row-heading">		

		<?php if ( ! empty( $label ) ) { ?>
		<label><?php echo esc_html( $label ); ?></label>
		<?php } ?>

		<?php if ( ! empty( $help ) ) { ?>
		<i class="wcf-field-heading-help dashicons dashicons-editor-help"></i>
		<span class="wcf-tooltip-text"><?php echo $help; ?></span>
		<?php } ?>
	</div>
	<?php } ?>

	<div class="wcf-field-row-content"><?php echo $field_data['generated_content']; ?>
		<?php
		if ( ! empty( $after_html ) ) {
			echo $after_html;
		}
		?>
	</div>
</div>
