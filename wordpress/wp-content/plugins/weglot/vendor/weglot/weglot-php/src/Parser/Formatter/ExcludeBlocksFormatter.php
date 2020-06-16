<?php

namespace Weglot\Parser\Formatter;

use WGSimpleHtmlDom\simple_html_dom;
use Weglot\Parser\Parser;

class ExcludeBlocksFormatter
{
    /**
     * @var simple_html_dom
     */
    protected $dom;

    /**
     * @var array
     */
    protected $excludeBlocks;

    /**
     * ExcludeBlocksFormatter constructor.
     * @param $dom
     */
    public function __construct($dom, $excludeBlocks)
    {
        $this
            ->setDom($dom)
            ->setExcludeBlocks($excludeBlocks);
        $this->handle();
    }

    /**
     * @param simple_html_dom $dom
     * @return $this
     */
    public function setDom(simple_html_dom $dom)
    {
        $this->dom = $dom;

        return $this;
    }

    /**
     * @return simple_html_dom
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * @param array $excludeBlocks
     * @return $this
     */
    public function setExcludeBlocks(array $excludeBlocks)
    {
        $this->excludeBlocks = $excludeBlocks;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludeBlocks()
    {
        return $this->excludeBlocks;
    }

    /**
     * Add ATTRIBUTE_NO_TRANSLATE to dom elements that don't
     * wanna be translated.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->excludeBlocks as $exception) {
            foreach ($this->dom->find($exception) as $k => $row) {
                $attribute = Parser::ATTRIBUTE_NO_TRANSLATE;
                $row->$attribute = '';
            }
        }
    }
}
