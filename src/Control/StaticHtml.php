<?php

namespace Efabrica\NeoForms\Control;

use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use Stringable;

/**
 * Purely presentational element (heading, space, separator, ...). It has no value and is omitted from form values.
 * It only carries meaning (kind, content, options); the markup is decided by NeoFormTemplate::staticHtml().
 */
class StaticHtml extends BaseControl
{
    public const DEFAULT_HEADING_LEVEL = 3;

    /**
     * @param 'html'|'heading'|'paragraph'|'alert'|'space'|'separator' $kind
     */
    public function __construct(private string $kind, string|Stringable|null $content = null)
    {
        parent::__construct($content);
        $this->setOmitted();
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * Translated at render time. Html is passed through untouched.
     */
    public function getContent(): string|Stringable|null
    {
        return $this->translate($this->getCaption());
    }

    public function getControl(): Html
    {
        return Html::el();
    }

    public function getLabel(string|Stringable|null $caption = null): ?Html
    {
        return null;
    }
}
