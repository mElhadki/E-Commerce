<?php

namespace Weglot\Util\Url;

/**
 * Class UrlConfig
 * @package Weglot\Util\Url
 */
class UrlConfig
{
    /**
     * @var string
     */
    protected $raw;

    /**
     * @var string
     */
    protected $pathPrefix = '';

    /**
     * @var array
     */
    protected $excludedUrls = [];

    /**
     * UrlConfig constructor.
     * @param string $raw           Current visited url
     * @param string $pathPrefix    Prefix to access website root path (ie. : `/my/custom/path`, don't forget: starting `/` and no ending `/`)
     * @param array $excludedUrls
     */
    public function __construct($raw, $pathPrefix = '', $excludedUrls = [])
    {
        $this->raw = $raw;
        $this->pathPrefix = $pathPrefix;
        $this->excludedUrls = $excludedUrls;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @return string
     */
    public function getPathPrefix()
    {
        return $this->pathPrefix;
    }

    /**
     * @param array $excludedUrls
     * @return $this
     */
    public function setExcludedUrls($excludedUrls)
    {
        $this->excludedUrls = $excludedUrls;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludedUrls()
    {
        return $this->excludedUrls;
    }
}
