<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Morphism\Morphism;
use WeglotWP\Models\Schema_Option_V3;


/**
 * Migration service
 *
 * @since 2.0
 */
class Migration_Service_Weglot {

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->option_services           = weglot_get_service( 'Option_Service_Weglot' );
	}
}

