<?php

namespace WeglotWP\Third\Ninjaforms\Regexcheckers;

use Weglot\Parser\Check\Regex\RegexChecker;
use Weglot\Util\SourceType;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0.7
 */
class Ninja_Form_Json_Nfi18n
{
    const REGEX = '#nfi18n = (.*?);#';

    const TYPE = SourceType::SOURCE_JSON;

    const VAR_NUMBER = 1;

    public static $KEYS = array( "title" , "changeEmailErrorMsg" , "changeDateErrorMsg" , "confirmFieldErrorMsg" , "fieldNumberNumMinError" , "fieldNumberNumMaxError" , "fieldNumberIncrementBy" , "fieldTextareaRTEInsertLink" , "fieldTextareaRTEInsertMedia" , "fieldTextareaRTESelectAFile" , "formErrorsCorrectErrors" , "validateRequiredField" , "honeypotHoneypotError" , "fileUploadOldCodeFileUploadInProgress" , "previousMonth" , "nextMonth" , "fieldsMarkedRequired" , "fileUploadOldCodeFileUpload" );
}
