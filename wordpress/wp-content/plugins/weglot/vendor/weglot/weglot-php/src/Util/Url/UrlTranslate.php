<?php

namespace Weglot\Util\Url;

/**
 * Class UrlTranslate
 * @package Weglot\Util\Url
 */
class UrlTranslate
{
    /**
     * @var string
     */
    protected $default = 'en';

    /**
     * @var array
     */
    protected $languages = [];

    /**
     * UrlTranslate constructor.
     * @param string $default   Default language represented by ISO 639-1 code
     * @param array $languages  All available languages
     */
    public function __construct($default, $languages)
    {
        $this->default = $default;
        $this->languages = $languages;
    }

    /**
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param string $code  Language represented by ISO 639-1 code
     * @return bool
     */
    public function checkIfAvailable($code)
    {
        return in_array($code, $this->getLanguages()) || $code === $this->getDefault();
    }
}
