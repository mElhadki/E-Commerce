<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Weglot\Util\Text;


/**
 * Dom Checkers
 *
 * @since 2.0
 */
class Dom_Listeners_Service_Weglot {

	/**
	 * @since 2.0
	 */
	public function __construct() {
		if ( '1' === WEGLOT_LIB_PARSER ) {
			return;
		}
		$this->dom_listeners = [
			'parser.crawler.after' => new \WeglotWP\Domlisteners\Meta_Listener_Weglot(),
		];
	}

	/**
	 * @since 2.0
	 * @return array
	 */
	public function get_dom_listeners() {
		return apply_filters( 'weglot_get_dom_listeners', $this->dom_listeners );
	}
}
