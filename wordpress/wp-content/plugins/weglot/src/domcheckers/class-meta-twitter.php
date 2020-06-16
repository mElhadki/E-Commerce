<?php

namespace WeglotWP\Domcheckers;

use Weglot\Parser\Check\Dom\AbstractDomChecker;
use Weglot\Client\Api\Enum\WordType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @since 2.0
 */
class Meta_Twitter extends AbstractDomChecker {
	/**
	 * {@inheritdoc}
	 */
	const DOM = "meta[name='twitter:card'],meta[name='twitter:site'],meta[name='twitter:creator']";
	/**
	 * {@inheritdoc}
	 */
	const PROPERTY = 'content';
	/**
	 * {@inheritdoc}
	 */
	const WORD_TYPE = WordType::META_CONTENT;
}
