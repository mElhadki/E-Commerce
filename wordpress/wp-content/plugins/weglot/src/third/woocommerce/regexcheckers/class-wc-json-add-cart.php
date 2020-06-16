<?php

namespace WeglotWP\Third\Woocommerce\Regexcheckers;

use Weglot\Parser\Check\Regex\RegexChecker;
use Weglot\Util\SourceType;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0.7
 */
class Wc_Json_Add_Cart
{
    const REGEX = '#wc_add_to_cart_params = (.*?);#';

    const TYPE = SourceType::SOURCE_JSON;

    const VAR_NUMBER = 1;

    public static $KEYS = array( "i18n_view_cart" );
}
