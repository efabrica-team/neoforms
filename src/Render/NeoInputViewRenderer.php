<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Control\Tags;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;

class NeoInputViewRenderer
{
    private NeoFormRendererTemplate $renderer;

    public function __construct(NeoFormRendererTemplate $renderer)
    {
        $this->renderer = $renderer;
    }

    public function input(BaseControl $el, array $options = []): string
    {
        if (is_callable($el->getOption('readonly'))) {
            return $el->getOption('readonly')($el);
        }

        if ($el instanceof Button || $el instanceof HiddenField) {
            return '';
        }
        if ($el instanceof SelectBox) {
            return $this->renderer->block('inputViewSingle', [
                'value' => $el->getItems()[$el->getValue()] ?? $el->getValue(),
            ]);
        }
        if ($el instanceof MultiSelectBox) {
            $values = array_map(fn($v) => $el->getItems()[$v] ?? $v, $el->getValue());
            return $this->renderer->block('inputViewMulti', [
                'values' => $values,
            ]);
        }
        if ($el instanceof TextArea) {
            return $this->renderer->block('inputViewTextarea', [
                'value' => $el->getValue(),
            ]);
        }
        if ($el instanceof Checkbox) {
            return $this->renderer->block('inputViewCheckbox', [
                'value' => $el->getValue(),
            ]);
        }
        if ($el instanceof Tags) {
            return $this->renderer->block('tagsView', [
                'value' => $el->getValue(),
                'options' => $options,
            ]);
        }

        return $this->renderer->block('inputViewSingle', [
            'value' => $el->getValue(),
        ]);
    }
}
