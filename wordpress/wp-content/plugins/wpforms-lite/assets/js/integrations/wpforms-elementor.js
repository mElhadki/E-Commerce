/* global wpforms, wpformsElementorVars, wpformsModernFileUpload, wpformsRecaptchaLoad, grecaptcha */
'use strict';

/**
 * WPForms integration with Elementor.
 *
 * @since 1.6.0
 */
var WPFormsElementor = window.wpforms.elementor || ( function( document, window, $ ) {

	/**
	 * Public functions and properties.
	 *
	 * @since 1.6.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.6.0
		 */
		init: function() {

			app.events();
		},

		/**
		 * Register JS events.
		 *
		 * @since 1.6.0
		 */
		events: function() {

			$( document ).on( 'elementor/popup/show', function( event, id, instance ) {

				var $modal = $( '#elementor-popup-modal-' + id ),
					$form  = $modal.find( '.wpforms-form' );

				if ( ! $form.length ) {
					return;
				}

				app.initFields( $form );
			} );
		},

		/**
		 * Init all things for WPForms.
		 *
		 * @since 1.6.0
		 *
		 * @param {object} $form jQuery selector.
		 */
		initFields: function( $form ) {

			// Init WPForms staff.
			wpforms.ready();

			// Init `Modern File Uplaod` field.
			if ( 'undefined' !== typeof wpformsModernFileUpload ) {
				wpformsModernFileUpload.init();
			}

			// Init reCAPTCHA.
			if (
				'undefined' !== typeof wpformsRecaptchaLoad &&
				'undefined' !== typeof grecaptcha
			) {
				'v3' === wpformsElementorVars.recaptcha_type ? grecaptcha.ready( wpformsRecaptchaLoad ) : wpformsRecaptchaLoad();
			}

			// Register a custom event.
			$( document ).trigger( 'wpforms_elementor_form_fields_initialized', [ $form ] );
		},
	};

	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsElementor.init();
