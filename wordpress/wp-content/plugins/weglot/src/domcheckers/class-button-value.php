<?php

namespace WeglotWP\Domcheckers;

use Weglot\Parser\Check\Dom\AbstractDomChecker;
use Weglot\Client\Api\Enum\WordType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0.6
 */
class Button_Value extends AbstractDomChecker {
	/**
	 * {@inheritdoc}
	 */
	const DOM = 'button';
	/**
	 * {@inheritdoc}
	 */
	const PROPERTY = 'value';
	/**
	 * {@inheritdoc}
	 */
	const WORD_TYPE = WordType::VALUE;
}
