<?php

namespace WeglotWP\Third\Calderaforms\Regexcheckers;

use Weglot\Parser\Check\Regex\RegexChecker;
use Weglot\Util\SourceType;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0.7
 */
class Caldera_Form_Json_Fields
{
    const REGEX = '#CF_VALIDATOR_STRINGS = (.*?);#';

    const TYPE = SourceType::SOURCE_JSON;

    const VAR_NUMBER = 1;

    public static $KEYS = array( "defaultMessage","email","url","number","integer","digits","alphanum","required","pattern","min","max","range","minlength","maxlength","length","mincheck","maxcheck","check","equalto","notblank" );
}
