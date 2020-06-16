<?php

namespace Weglot\Client\Api;

use Weglot\Client\Api\Shared\AbstractCollectionEntry;

/**
 * Class LanguageEntry
 * @package Weglot\Client\Api
 */
class LanguageEntry extends AbstractCollectionEntry
{
    /**
     * ISO 639-1 code to identify language
     *
     * @see https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     * @var string
     */
    protected $iso_639_1;

    /**
     * English name of the language
     *
     * @var string
     */
    protected $englishName;

    /**
     * Name of the language in the language
     *
     * @var string
     */
    protected $localName;

    /**
     * Language is right to left
     *
     * @var bool
     */
    protected $isRtl;

    /**
     * LanguageEntry constructor.
     * @param string $iso_639_1     ISO 639-1 code to identify language
     * @param string $englishName   English name of the language
     * @param string $localName     Name of the language in the language
     * @param bool $isRtl           Language is right to left
     */
    public function __construct($iso_639_1, $englishName, $localName, $isRtl = false)
    {
        $this
            ->setIso639($iso_639_1)
            ->setEnglishName($englishName)
            ->setLocalName($localName)
            ->setRtl($isRtl);
    }

    /**
     * @param $iso_639_1
     * @return $this
     */
    public function setIso639($iso_639_1)
    {
        $this->iso_639_1 = $iso_639_1;

        return $this;
    }

    /**
     * @return string
     */
    public function getIso639()
    {
        return $this->iso_639_1;
    }

    /**
     * @param string $englishName
     * @return $this
     */
    public function setEnglishName($englishName)
    {
        $this->englishName = $englishName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnglishName()
    {
        return $this->englishName;
    }

    /**
     * @param $localName
     * @return $this
     */
    public function setLocalName($localName)
    {
        $this->localName = $localName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * @param bool $rtl
     * @return $this
     */
    public function setRtl($rtl)
    {
        $this->isRtl = $rtl;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRtl()
    {
        return $this->isRtl;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'code'      => $this->getIso639(),
            'english'   => $this->getEnglishName(),
            'local'     => $this->getLocalName(),
            'rtl'       => $this->isRtl(),
        ];
    }
}
