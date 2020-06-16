<?php

namespace WeglotWP\Actions;

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

use Morphism\Morphism;
use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Models\Schema_Option_Migration_V3;

/**
 * Migration Weglot
 *
 * @since 2.0.0
 */
class Migration_Weglot implements Hooks_Interface_Weglot {


	/**
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->migration_services   = weglot_get_service( 'Migration_Service_Weglot' );
	}


	/**
	 * @see HooksInterface
	 * @since 2.0.0
	 * @version 3.0.0
	 * @return void
	 */
	public function hooks() {
		if ( ! defined( 'WEGLOT_LATEST_VERSION' ) && ! defined( 'WEGLOT_VERSION' ) ) {
			return;
		}

		$weglot_version = get_option( 'weglot_version' );

		if ( $weglot_version && version_compare( $weglot_version, '2.3.0', '>=' ) && version_compare( $weglot_version, '3.0.0', '<' ) ) {
			update_option( 'weglot_version', WEGLOT_VERSION );
		}
	}
}
