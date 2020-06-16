<?php

namespace Weglot\Util;

use Weglot\Util\Url\UrlConfig;
use Weglot\Util\Url\UrlTranslate;

/**
 * Class Url
 * @package Weglot\Util
 */
class Url
{
    /**
     * @var null|string
     */
    protected $host = null;

    /**
     * @var null|string
     */
    protected $path = null;

    /**
     * @var null|string
     */
    protected $query = null;

    /**
     * @var null|array
     */
    protected $allUrls = null;

    /**
     * @var UrlConfig
     */
    protected $config = null;

    /**
     * @var UrlTranslate
     */
    protected $translate = null;

    /**
     * Url constructor.
     * @param string $url           Current visited url
     * @param string $default       Default language represented by ISO 639-1 code
     * @param array $languages      All available languages
     * @param string $pathPrefix    Prefix to access website root path (ie. : `/my/custom/path`, don't forget: starting `/` and no ending `/`)
     */
    public function __construct($url, $default, $languages = [], $pathPrefix = '')
    {
        $this->config = new UrlConfig($url, $pathPrefix);
        $this->translate = new UrlTranslate($default, $languages);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->config->getRaw();
    }

    /**
     * @return null|string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @deprecated Use getPath() instead
     * @return null|string
     */
    public function getBaseUrl()
    {
        @trigger_error('Use getPath() instead', E_USER_DEPRECATED);
        return $this->getPath();
    }

    /**
     * @return string
     */
    public function getPathPrefix()
    {
        return $this->config->getPathPrefix();
    }

    /**
     * @param array $excludedUrls
     * @return $this
     */
    public function setExcludedUrls($excludedUrls)
    {
        $this->config->setExcludedUrls($excludedUrls);

        return $this;
    }

    /**
     * @return string
     */
    public function getDefault()
    {
        return $this->translate->getDefault();
    }

    /**
     * @return null|string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $code  Language represented by ISO 639-1 code
     * @return bool|string
     */
    public function getForLanguage($code)
    {
        $url = false;

        if ($this->translate->checkIfAvailable($code)) {
            $all = $this->currentRequestAllUrls();
            $url = $all[$code];
        }

        return $url;
    }

    /**
     * Check if we need to translate given URL
     *
     * @return bool
     */
    public function isTranslable()
    {
        if ($this->getPath() === null) {
            $this->detectUrlDetails();
        }

        foreach ($this->config->getExcludedUrls() as $regex) {
            $escapedRegex = Text::escapeForRegex($regex);
            $fullRegex = sprintf('/%s/', $escapedRegex);

            if (preg_match($fullRegex, $this->getPath()) === 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check current locale, based on URI segments from the given URL
     *
     * @return mixed
     */
    public function detectCurrentLanguage()
    {
        // parsing url to get only path & removing prefix if there is one
        $escapedPathPrefix = Text::escapeForRegex($this->config->getPathPrefix());
        $uriPath = parse_url($this->config->getRaw(), PHP_URL_PATH);
        $uriPath = preg_replace('/^' . $escapedPathPrefix . '/s', '', $uriPath);
        $uriSegments = explode('/', $uriPath);

        if (isset($uriSegments[1]) && in_array($uriSegments[1], $this->translate->getLanguages())) {
            return $uriSegments[1];
        }
        return $this->translate->getDefault();
    }

    /**
     * Generate possible host & base URL then store it into internal variables
     *
     * @return string   Host + path prefix + base URL
     */
    public function detectUrlDetails()
    {
        if (defined('WP_CLI') && WP_CLI) {
            return;
        }

        $escapedPathPrefix = Text::escapeForRegex($this->config->getPathPrefix());
        $languages = implode('|', $this->translate->getLanguages());

        $fullUrl = preg_replace('#' . $escapedPathPrefix . '\/(' . $languages . ')$#i', '', $this->getUrl());
        $fullUrl = preg_replace('#' . $escapedPathPrefix . '\/(' . $languages . ')/#i', '/', $fullUrl);
        $parsed = parse_url($fullUrl);

        if(isset($parsed['scheme'])) {
            $this->host = $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':'.$parsed['port'] : '');
        }
        $this->path = isset($parsed['path']) ? $parsed['path'] : '/';
        $this->query = isset($parsed['query']) ? $parsed['query'] : null;

        if (preg_match('#^' .$this->config->getPathPrefix(). '#i', $this->path)) {
            $this->path = preg_replace('#^' .$this->config->getPathPrefix(). '#i', '', $this->path);
        }

        if ($this->path === "") {
            $this->path = '/';
        }

        $url = $this->getHost() . $this->getPathPrefix() . $this->getPath();
        if (!is_null($this->getQuery())) {
            $url .= '?'. $this->getQuery();
        }
        return $url;
    }

    /**
     * Returns array with all possible URL for current Request
     *
     * @return array
     */
    public function currentRequestAllUrls()
    {
        if (defined('WP_CLI') && WP_CLI) {
            return array();
        }

        $urls = $this->allUrls;

        if ($urls === null) {
            if ($this->getPath() === null) {
                $this->detectUrlDetails();
            }

            $urls = [];
            $current = $this->getHost() . $this->config->getPathPrefix() . $this->getPath();
            if (!is_null($this->getQuery())) {
                $current .= '?'. $this->getQuery();
            }
            $urls[$this->translate->getDefault()] = $current;
            foreach ($this->translate->getLanguages() as $language) {
                $current = $this->getHost() . $this->config->getPathPrefix() . '/' . $language . $this->getPath();
                if (!is_null($this->getQuery())) {
                    $current .= '?'. $this->getQuery();
                }
                $urls[$language] = $current;
            }

            $this->allUrls = $urls;
        }

        return $urls;
    }

    /**
     * Render hreflang links for SEO
     *
     * @return string
     */
    public function generateHrefLangsTags()
    {
        $render = '';
        $urls = $this->currentRequestAllUrls();

        foreach ($urls as $language => $url) {
            $render .= '<link rel="alternate" href="' .$url. '" hreflang="' .$language. '"/>'."\n";
        }

        return $render;
    }
}
