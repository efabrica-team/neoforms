<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Control\Tags;
use Efabrica\NeoForms\Control\ToggleSwitch;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Efabrica\Nette\Forms\Rte\RteControl;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\UploadControl;

class NeoInputViewRenderer
{
    private NeoFormRenderer $renderer;

    public function __construct(NeoFormRenderer $renderer)
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
        if ($el instanceof TextArea || $el instanceof RteControl) {
            return $this->renderer->block('inputViewTextarea', [
                'value' => $el->getValue(),
            ]);
        }
        if ($el instanceof Checkbox) {
            return $this->renderer->block('inputViewCheckbox', [
                'value' => $el->getValue(),
            ]);
        }
        if ($el instanceof UploadControl) {
            $disabled = $el->isDisabled();
            $el->setDisabled(true);
            $s = $this->renderer->inputRenderer->upload($el, ['disabled' => true, 'readonly' => true], $options);
            $el->setDisabled($disabled);
            return $s;
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
