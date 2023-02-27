<?php

namespace Efabrica\NeoForms\Render\Input;

use Efabrica\NeoForms\Render\NeoInputRenderer;
use Efabrica\Nette\Forms\Rte\RteControl;
use Nette\Forms\Controls\BaseControl;

class RteControlRenderer implements CustomInputRenderer
{
    private NeoInputRenderer $renderer;

    public function __construct(NeoInputRenderer $inputRenderer)
    {
        $this->renderer = $inputRenderer;
    }

    public function render(BaseControl $el, array $attrs, array $options): ?string
    {
        if ($el instanceof RteControl) {
            return $this->renderer->textarea($el, $attrs, $options);
        }
        return null;
    }

    public function readonlyRender(BaseControl $el, array $options): ?string
    {
        if ($el instanceof RteControl) {
            return $this->renderer->block('inputViewTextarea', [
                'value' => $el->getValue(),
            ]);
        }
        return null;
    }
}
