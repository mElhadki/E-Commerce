<?php
namespace WeglotWP\Domcheckers;

use Weglot\Parser\Check\Dom\LinkHref;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Link_Data_Href extends LinkHref {
	/**
	 * {@inheritdoc}
	 */
	const PROPERTY = 'data-href';
}
