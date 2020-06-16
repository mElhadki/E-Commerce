<?php

namespace WeglotWP\Domcheckers;

use Weglot\Parser\Check\Dom\AbstractDomChecker;
use Weglot\Client\Api\Enum\WordType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Video_Source extends AbstractDomChecker {
	/**
	 * {@inheritdoc}
	 */
	const DOM = 'video source';
	/**
	 * {@inheritdoc}
	 */
	const PROPERTY = 'src';
	/**
	 * {@inheritdoc}
	 */
	const WORD_TYPE = WordType::IMG_SRC;
}
