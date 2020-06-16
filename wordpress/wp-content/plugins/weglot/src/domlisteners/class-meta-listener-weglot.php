<?php

namespace WeglotWP\Domlisteners;

use Weglot\Parser\Listener\AbstractCrawlerAfterListener;
use Weglot\Parser\Parser;
use Weglot\Client\Api\Enum\WordType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
final class Meta_Listener_Weglot extends AbstractCrawlerAfterListener {
	protected $attributes = [
		'name' => [
			'twitter:image',
			'twitter:card',
			'twitter:site',
			'twitter:creator',
		],
	];

	/**
	 * {@inheritdoc}
	 */
	protected function xpath() {
		$selectors = [];
		foreach ( $this->attributes as $name => $values ) {
			foreach ( $values as $value ) {
				$selectors[] = '@' . $name . ' = \'' . $value . '\'';
			}
		}
		return '//meta[(' . implode( ' or ', $selectors ) . ') and not(ancestor-or-self::*[@' . Parser::ATTRIBUTE_NO_TRANSLATE . '])]/@content';
	}
	/**
	 * {@inheritdoc}
	 */
	protected function type( \DOMNode $node ) {
		return WordType::META_CONTENT;
	}
}
