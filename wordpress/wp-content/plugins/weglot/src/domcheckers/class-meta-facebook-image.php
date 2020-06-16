<?php

namespace WeglotWP\Domcheckers;

use Weglot\Parser\Check\Dom\AbstractDomChecker;
use Weglot\Client\Api\Enum\WordType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @since 2.5.0
 */
class Meta_Facebook_Image extends AbstractDomChecker {
	/**
	 * {@inheritdoc}
	 */
	const DOM = "meta[property='og:image'], meta[property='og:image:secure_url']";
	/**
	 * {@inheritdoc}
	 */
	const PROPERTY = 'content';
	/**
	 * {@inheritdoc}
	 */
	const WORD_TYPE = WordType::IMG_SRC;
}
