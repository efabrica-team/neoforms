<?php

namespace Efabrica\NeoForms\Render\Input;

use Efabrica\NeoForms\Render\NeoInputRenderer;
use Efabrica\Nette\Chooze\ChoozeControl;
use Nette\Forms\Controls\BaseControl;

class ChoozeControlRenderer implements CustomInputRenderer
{
    private NeoInputRenderer $renderer;

    public function __construct(NeoInputRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(BaseControl $el, array $attrs, array $options): ?string
    {
        if ($el instanceof ChoozeControl) {
            return $this->renderer->custom($el, $attrs, $options);
        }
        return null;
    }

    public function readonlyRender(BaseControl $el, array $options): ?string
    {
        return null;
    }
}