<?php

namespace Efabrica\NeoForms\Control;

use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use Stringable;

/**
 * Purely presentational element (heading, spacer, line break, ...). It has no value and is omitted from form values.
 */
class StaticHtml extends BaseControl
{
    public const DEFAULT_HEADING_LEVEL = 3;

    private Html $html;

    public function __construct(Html $html)
    {
        parent::__construct();
        $this->html = $html;
        $this->setOmitted();
    }

    public function getHtml(): Html
    {
        $html = clone $this->html;
        foreach ($this->control->attrs as $name => $value) {
            if ($name === 'class') {
                $html->appendAttribute('class', $value);
            } else {
                $html->setAttribute($name, $value);
            }
        }
        return $html;
    }

    public function getControl(): Html
    {
        return $this->getHtml();
    }

    public function getLabel(string|Stringable|null $caption = null): ?Html
    {
        return null;
    }
}
