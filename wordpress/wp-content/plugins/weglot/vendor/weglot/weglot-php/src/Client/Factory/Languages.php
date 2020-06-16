<?php

namespace Weglot\Client\Factory;

use Weglot\Client\Api\LanguageEntry;

/**
 * Class Languages
 * @package Weglot\Client\Factory
 */
class Languages
{
    /**
     * @var array
     */
    protected $language;

    /**
     * Languages constructor.
     * @param array $language
     */
    public function __construct(array $language)
    {
        $this->language = $language;
    }

    /**
     * @param array $language
     * @return $this
     */
    public function setLanguage(array $language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param null $key
     * @return array|string|bool
     */
    public function getLanguage($key = null)
    {
        if ($key !== null && isset($this->language[$key])) {
            return $this->language[$key];
        }
        return $this->language;
    }

    /**
     * @return LanguageEntry
     */
    public function handle()
    {
        $language = new LanguageEntry(
            $this->getLanguage('code'),
            $this->getLanguage('english'),
            $this->getLanguage('local'),
            $this->getLanguage('rtl')
        );

        return $language;
    }

    /**
     * Only used to replace API endpoint
     * We planned to make this endpoint available soon !
     *
     * @return array
     */
    public static function data()
    {
        return [
            'sq' => [
                'code'  => 'sq',
                'english' => 'Albanian',
                'local' => 'Shqip',
                'rtl' => false,
            ],
            'en' => [
                'code'  => 'en',
                'english' => 'English',
                'local' => 'English',
                'rtl' => false,
            ],
            'ar' => [
                'code'  => 'ar',
                'english' => 'Arabic',
                'local' => 'العربية‏',
                'rtl' => true,
            ],
            'hy' => [
                'code'  => 'hy',
                'english' => 'Armenian',
                'local' => 'հայերեն',
                'rtl' => false,
            ],
            'az' => [
                'code'  => 'az',
                'english' => 'Azerbaijani',
                'local' => 'Azərbaycan dili',
                'rtl' => false,
            ],
            'af' => [
                'code'  => 'af',
                'english' => 'Afrikaans',
                'local' => 'Afrikaans',
                'rtl' => false,
            ],
            'eu' => [
                'code'  => 'eu',
                'english' => 'Basque',
                'local' => 'Euskara',
                'rtl' => false,
            ],
            'be' => [
                'code'  => 'be',
                'english' => 'Belarusian',
                'local' => 'Беларуская',
                'rtl' => false,
            ],
            'bg' => [
                'code'  => 'bg',
                'english' => 'Bulgarian',
                'local' => 'български',
                'rtl' => false,
            ],
            'bs' => [
                'code'  => 'bs',
                'english' => 'Bosnian',
                'local' => 'Bosanski',
                'rtl' => false,
            ],
            'cy' => [
                'code'  => 'cy',
                'english' => 'Welsh',
                'local' => 'Cymraeg',
                'rtl' => false,
            ],
            'vi' => [
                'code'  => 'vi',
                'english' => 'Vietnamese',
                'local' => 'Tiếng Việt',
                'rtl' => false,
            ],
            'hu' => [
                'code'  => 'hu',
                'english' => 'Hungarian',
                'local' => 'Magyar',
                'rtl' => false,
            ],
            'ht' => [
                'code'  => 'ht',
                'english' => 'Haitian',
                'local' => 'Kreyòl ayisyen',
                'rtl' => false,
            ],
            'gl' => [
                'code'  => 'gl',
                'english' => 'Galician',
                'local' => 'Galego',
                'rtl' => false,
            ],
            'nl' => [
                'code'  => 'nl',
                'english' => 'Dutch',
                'local' => 'Nederlands',
                'rtl' => false,
            ],
            'el' => [
                'code'  => 'el',
                'english' => 'Greek',
                'local' => 'Ελληνικά',
                'rtl' => false,
            ],
            'ka' => [
                'code'  => 'ka',
                'english' => 'Georgian',
                'local' => 'ქართული',
                'rtl' => false,
            ],
            'da' => [
                'code'  => 'da',
                'english' => 'Danish',
                'local' => 'Dansk',
                'rtl' => false,
            ],
            'he' => [
                'code'  => 'he',
                'english' => 'Hebrew',
                'local' => 'עברית',
                'rtl' => true,
            ],
            'id' => [
                'code'  => 'id',
                'english' => 'Indonesian',
                'local' => 'Bahasa Indonesia',
                'rtl' => false,
            ],
            'ga' => [
                'code'  => 'ga',
                'english' => 'Irish',
                'local' => 'Gaeilge',
                'rtl' => false,
            ],
            'it' => [
                'code'  => 'it',
                'english' => 'Italian',
                'local' => 'Italiano',
                'rtl' => false,
            ],
            'is' => [
                'code'  => 'is',
                'english' => 'Icelandic',
                'local' => 'Íslenska',
                'rtl' => false,
            ],
            'es' => [
                'code'  => 'es',
                'english' => 'Spanish',
                'local' => 'Español',
                'rtl' => false,
            ],
            'kk' => [
                'code'  => 'kk',
                'english' => 'Kazakh',
                'local' => 'Қазақша',
                'rtl' => false,
            ],
            'ca' => [
                'code'  => 'ca',
                'english' => 'Catalan',
                'local' => 'Català',
                'rtl' => false,
            ],
            'ky' => [
                'code'  => 'ky',
                'english' => 'Kyrgyz',
                'local' => 'кыргызча',
                'rtl' => false,
            ],
            'zh' => [
                'code'  => 'zh',
                'english' => 'Simplified Chinese',
                'local' => '中文 (简体)',
                'rtl' => false,
            ],
            'tw' => [
                'code'  => 'tw',
                'english' => 'Traditional Chinese',
                'local' => '中文 (繁體)',
                'rtl' => false,
            ],
            'ko' => [
                'code'  => 'ko',
                'english' => 'Korean',
                'local' => '한국어',
                'rtl' => false,
            ],
            'lv' => [
                'code'  => 'lv',
                'english' => 'Latvian',
                'local' => 'Latviešu',
                'rtl' => false,
            ],
            'lt' => [
                'code'  => 'lt',
                'english' => 'Lithuanian',
                'local' => 'Lietuvių',
                'rtl' => false,
            ],
            'mg' => [
                'code'  => 'mg',
                'english' => 'Malagasy',
                'local' => 'Malagasy',
                'rtl' => false,
            ],
            'ms' => [
                'code'  => 'ms',
                'english' => 'Malay',
                'local' => 'Bahasa Melayu',
                'rtl' => false,
            ],
            'mt' => [
                'code'  => 'mt',
                'english' => 'Maltese',
                'local' => 'Malti',
                'rtl' => false,
            ],
            'mk' => [
                'code'  => 'mk',
                'english' => 'Macedonian',
                'local' => 'Македонски',
                'rtl' => false,
            ],
            'mn' => [
                'code'  => 'mn',
                'english' => 'Mongolian',
                'local' => 'Монгол',
                'rtl' => false,
            ],
            'de' => [
                'code'  => 'de',
                'english' => 'German',
                'local' => 'Deutsch',
                'rtl' => false,
            ],
            'no' => [
                'code'  => 'no',
                'english' => 'Norwegian',
                'local' => 'Norsk',
                'rtl' => false,
            ],
            'fa' => [
                'code'  => 'fa',
                'english' => 'Persian',
                'local' => 'فارسی',
                'rtl' => true,
            ],
            'pl' => [
                'code'  => 'pl',
                'english' => 'Polish',
                'local' => 'Polski',
                'rtl' => false,
            ],
            'pt' => [
                'code'  => 'pt',
                'english' => 'Portuguese',
                'local' => 'Português',
                'rtl' => false,
            ],
            'ro' => [
                'code'  => 'ro',
                'english' => 'Romanian',
                'local' => 'Română',
                'rtl' => false,
            ],
            'ru' => [
                'code'  => 'ru',
                'english' => 'Russian',
                'local' => 'Русский',
                'rtl' => false,
            ],
            'sr' => [
                'code'  => 'sr',
                'english' => 'Serbian',
                'local' => 'Српски',
                'rtl' => false,
            ],
            'sk' => [
                'code'  => 'sk',
                'english' => 'Slovak',
                'local' => 'Slovenčina',
                'rtl' => false,
            ],
            'sl' => [
                'code'  => 'sl',
                'english' => 'Slovenian',
                'local' => 'Slovenščina',
                'rtl' => false,
            ],
            'sw' => [
                'code'  => 'sw',
                'english' => 'Swahili',
                'local' => 'Kiswahili',
                'rtl' => false,
            ],
            'tg' => [
                'code'  => 'tg',
                'english' => 'Tajik',
                'local' => 'Тоҷикӣ',
                'rtl' => false,
            ],
            'th' => [
                'code'  => 'th',
                'english' => 'Thai',
                'local' => 'ภาษาไทย',
                'rtl' => false,
            ],
            'tl' => [
                'code'  => 'tl',
                'english' => 'Tagalog',
                'local' => 'Tagalog',
                'rtl' => false,
            ],
            'tt' => [
                'code'  => 'tt',
                'english' => 'Tatar',
                'local' => 'Tatar',
                'rtl' => false,
            ],
            'tr' => [
                'code'  => 'tr',
                'english' => 'Turkish',
                'local' => 'Türkçe',
                'rtl' => false,
            ],
            'uz' => [
                'code'  => 'uz',
                'english' => 'Uzbek',
                'local' => 'O\'zbek',
                'rtl' => false,
            ],
            'uk' => [
                'code'  => 'uk',
                'english' => 'Ukrainian',
                'local' => 'Українська',
                'rtl' => false,
            ],
            'fi' => [
                'code'  => 'fi',
                'english' => 'Finnish',
                'local' => 'Suomi',
                'rtl' => false,
            ],
            'fr' => [
                'code'  => 'fr',
                'english' => 'French',
                'local' => 'Français',
                'rtl' => false,
            ],
            'hr' => [
                'code'  => 'hr',
                'english' => 'Croatian',
                'local' => 'Hrvatski',
                'rtl' => false,
            ],
            'cs' => [
                'code'  => 'cs',
                'english' => 'Czech',
                'local' => 'Čeština',
                'rtl' => false,
            ],
            'sv' => [
                'code'  => 'sv',
                'english' => 'Swedish',
                'local' => 'Svenska',
                'rtl' => false,
            ],
            'et' => [
                'code'  => 'et',
                'english' => 'Estonian',
                'local' => 'Eesti',
                'rtl' => false,
            ],
            'ja' => [
                'code'  => 'ja',
                'english' => 'Japanese',
                'local' => '日本語',
                'rtl' => false,
            ],
            'hi' => [
                'code'  => 'hi',
                'english' => 'Hindi',
                'local' => 'हिंदी',
                'rtl' => false,
            ],
            'ur' => [
                'code'  => 'ur',
                'english' => 'Urdu',
                'local' => 'اردو',
                'rtl' => false,
            ],
            'co' => [
                'code'  => 'co',
                'english' => 'Corsican',
                'local' => 'Corsu',
                'rtl' => false,
            ],
            'fj' => [
                'code'  => 'fj',
                'english' => 'Fijian',
                'local' => 'Vosa Vakaviti',
                'rtl' => false,
            ],
            'hw' => [
                'code'  => 'hw',
                'english' => 'Hawaiian',
                'local' => '‘Ōlelo Hawai‘i',
                'rtl' => false,
            ],
            'ig' => [
                'code'  => 'ig',
                'english' => 'Igbo',
                'local' => 'Igbo',
                'rtl' => false,
            ],
            'ny' => [
                'code'  => 'ny',
                'english' => 'Chichewa',
                'local' => 'chiCheŵa',
                'rtl' => false,
            ],
            'ps' => [
                'code'  => 'ps',
                'english' => 'Pashto',
                'local' => 'پښت',
                'rtl' => false,
            ],
            'sd' => [
                'code'  => 'sd',
                'english' => 'Sindhi',
                'local' => 'سنڌي، سندھی, सिन्धी',
                'rtl' => false,
            ],
            'sn' => [
                'code'  => 'sn',
                'english' => 'Shona',
                'local' => 'chiShona',
                'rtl' => false,
            ],
            'to' => [
                'code'  => 'to',
                'english' => 'Tongan',
                'local' => 'faka-Tonga',
                'rtl' => false,
            ],
            'yo' => [
                'code'  => 'yo',
                'english' => 'Yoruba',
                'local' => 'Yorùbá',
                'rtl' => false,
            ],
            'zu' => [
                'code'  => 'zu',
                'english' => 'Zulu',
                'local' => 'isiZulu',
                'rtl' => false,
            ],
            'ty' => [
                'code'  => 'ty',
                'english' => 'Tahitian',
                'local' => 'te reo Tahiti, te reo Māʼohi',
                'rtl' => false,
            ],
            'sm' => [
                'code'  => 'sm',
                'english' => 'Samoan',
                'local' => 'gagana fa\'a Samoa',
                'rtl' => false,
            ],
            'ku' => [
                'code'  => 'ku',
                'english' => 'Kurdish',
                'local' => 'كوردی',
                'rtl' => false,
            ],
            'ha' => [
                'code'  => 'ha',
                'english' => 'Hausa',
                'local' => 'هَوُسَ',
                'rtl' => false,
            ],
            'bn' => [
                'code'  => 'bn',
                'english' => 'Bengali',
                'local' => 'বাংলা',
                'rtl' => false,
            ],
            'st' => [
                'code'  => 'st',
                'english' => 'Southern Sotho',
                'local' => 'seSotho',
                'rtl' => false,
            ],
            'ba' => [
                'code'  => 'ba',
                'english' => 'Bashkir',
                'local' => 'башҡорт теле',
                'rtl' => false,
            ],
            'jv' => [
                'code'  => 'jv',
                'english' => 'Javanese',
                'local' => 'Wong Jawa',
                'rtl' => false,
            ],
            'kn' => [
                'code'  => 'kn',
                'english' => 'Kannada',
                'local' => 'ಕನ್ನಡ',
                'rtl' => false,
            ],
            'la' => [
                'code'  => 'la',
                'english' => 'Latin',
                'local' => 'Latine',
                'rtl' => false,
            ],
            'lo' => [
                'code'  => 'lo',
                'english' => 'Lao',
                'local' => 'ພາສາລາວ',
                'rtl' => false,
            ],
            'mi' => [
                'code'  => 'mi',
                'english' => 'Māori',
                'local' => 'te reo Māori',
                'rtl' => false,
            ],
            'ml' => [
                'code'  => 'ml',
                'english' => 'Malayalam',
                'local' => 'മലയാളം',
                'rtl' => false,
            ],
            'mr' => [
                'code'  => 'mr',
                'english' => 'Marathi',
                'local' => 'मराठी',
                'rtl' => false,
            ],
            'ne' => [
                'code'  => 'ne',
                'english' => 'Nepali',
                'local' => 'नेपाली',
                'rtl' => false,
            ],
            'pa' => [
                'code'  => 'pa',
                'english' => 'Punjabi',
                'local' => 'ਪੰਜਾਬੀ',
                'rtl' => false,
            ],
            'so' => [
                'code'  => 'so',
                'english' => 'Somali',
                'local' => 'Soomaaliga',
                'rtl' => false,
            ],
            'su' => [
                'code'  => 'su',
                'english' => 'Sundanese',
                'local' => 'Sundanese',
                'rtl' => false,
            ],
            'te' => [
                'code'  => 'te',
                'english' => 'Telugu',
                'local' => 'తెలుగు',
                'rtl' => false,
            ],
            'yi' => [
                'code'  => 'yi',
                'english' => 'Yiddish',
                'local' => 'ייִדיש',
                'rtl' => false,
            ],
            'am' => [
                'code'  => 'am',
                'english' => 'Amharic',
                'local' => 'አማርኛ',
                'rtl' => false,
            ],
            'eo' => [
                'code'  => 'eo',
                'english' => 'Esperanto',
                'local' => 'Esperanto',
                'rtl' => false,
            ],
            'fy' => [
                'code'  => 'fy',
                'english' => 'Western Frisian',
                'local' => 'frysk',
                'rtl' => false,
            ],
            'gd' => [
                'code'  => 'gd',
                'english' => 'Scottish Gaelic',
                'local' => 'Gàidhlig',
                'rtl' => false,
            ],
            'gu' => [
                'code'  => 'gu',
                'english' => 'Gujarati',
                'local' => 'ગુજરાતી',
                'rtl' => false,
            ],
            'km' => [
                'code'  => 'km',
                'english' => 'Central Khmer',
                'local' => 'ភាសាខ្មែរ',
                'rtl' => false,
            ],
            'lb' => [
                'code'  => 'lb',
                'english' => 'Luxembourgish',
                'local' => 'Lëtzebuergesch',
                'rtl' => false,
            ],
            'my' => [
                'code'  => 'my',
                'english' => 'Burmese',
                'local' => 'မျန္မာစာ',
                'rtl' => false,
            ],
            'si' => [
                'code'  => 'si',
                'english' => 'Sinhalese',
                'local' => 'සිංහල',
                'rtl' => false,
            ],
            'ta' => [
                'code'  => 'ta',
                'english' => 'Tamil',
                'local' => 'தமிழ்',
                'rtl' => false,
            ],
            'xh' => [
                'code'  => 'xh',
                'english' => 'Xhosa',
                'local' => 'isiXhosa',
                'rtl' => false,
            ],
            'fl' => [
                'code'  => 'fl',
                'english' => 'Filipino',
                'local' => 'Pilipino',
                'rtl' => false,
            ]
        ];
    }
}
