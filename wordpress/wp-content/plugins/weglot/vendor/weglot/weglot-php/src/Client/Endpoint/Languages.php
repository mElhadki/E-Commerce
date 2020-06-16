<?php

namespace Weglot\Client\Endpoint;

use Weglot\Client\Api\LanguageCollection;
use Weglot\Client\Factory\Languages as LanguagesFactory;

/**
 * Class Languages
 * @package Weglot\Client\Endpoint
 */
class Languages extends Endpoint
{
    const METHOD = 'GET';
    const ENDPOINT = '/languages';

    /**
     * @return LanguageCollection
     */
    public function handle()
    {
        $languageCollection = new LanguageCollection();
        $data = LanguagesFactory::data();

        foreach ($data as $language) {
            $factory = new LanguagesFactory($language);
            $languageCollection->addOne($factory->handle());
        }

        return $languageCollection;
    }
}
