<?php

namespace Efabrica\NeoForms\Render\Template;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\UploadControl;
use Nette\HtmlStringable;
use Nette\Utils\Html;

/**
 * Ships Bootstrap 5 markup out of the box: `form-control`/`form-select` inputs,
 * `is-invalid`/`invalid-feedback` validation styling, `mb-3` row spacing and
 * `btn btn-primary` buttons.
 *
 * Radio lists and checkbox lists are intentionally left on the base rendering -
 * Nette renders them as bare `<label><input>...</label>` items, and restyling
 * those into `form-check` groups requires per-item control (via the list's
 * separator/container/item-label prototypes) that's out of scope for a
 * default theme; override `radio()`/`checkboxList()` yourself if you need it.
 *
 * Checkbox caption gotcha: {@see NeoFormTemplate::formLabel()} is never even
 * called for {@see Checkbox} controls - the renderer's top-level formLabel()
 * short-circuits to an empty Html for buttons/hidden fields/checkboxes, since
 * a checkbox's caption traditionally sits next to the input, not above it.
 * That means checkbox() is the only place the caption is rendered at all;
 * copying the textInput()-style override for checkbox() without also pulling
 * in getLabel() would silently drop the caption text.
 */
class Bootstrap5FormTemplate extends NeoFormTemplate
{
    /**
     * @param array<string, mixed> $attrs
     */
    public function formRow(Html $label, Html $input, Html $errors, array $attrs): Html
    {
        return $this->applyAttrs(Html::el('div')->class('mb-3')->addHtml($label . $input . $errors), $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    public function formLabel(BaseControl $control, array $attrs): Html
    {
        $el = parent::formLabel($control, $attrs);
        $el->class('form-label');
        return $el;
    }

    /**
     * @param (string|HtmlStringable)[] $errors
     */
    public function formErrors(array $errors): Html
    {
        if ($errors === []) {
            return Html::el();
        }
        $list = Html::el('ul')->class('mb-0');
        foreach ($errors as $error) {
            $list->addHtml(Html::el('li')->addHtml($error));
        }
        return Html::el('div')->class('alert alert-danger')->addHtml($list);
    }

    /**
     * @param (string|HtmlStringable)[] $errors
     */
    public function rowErrors(array $errors): Html
    {
        if ($errors === []) {
            return Html::el();
        }
        $el = Html::el('div')->class('invalid-feedback d-block');
        foreach ($errors as $i => $error) {
            if ($i > 0) {
                $el->addHtml(Html::el('br'));
            }
            $el->addHtml($error);
        }
        return $el;
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function textInput(TextInput $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class ??= 'form-control';
        $el->class('is-invalid', $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function textarea(TextArea $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class ??= 'form-control';
        $el->class('is-invalid', $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function upload(UploadControl $control, array $attrs): Html
    {
        $el = $control->getControl();
        assert($el instanceof Html);
        $el->class ??= 'form-control';
        $el->class('is-invalid', $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function select(SelectBox $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class ??= 'form-select';
        $el->class('is-invalid', $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function multiSelect(MultiSelectBox $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class ??= 'form-select';
        $el->class('is-invalid', $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function checkbox(Checkbox $control, array $attrs): Html
    {
        // Checkbox::getControl() returns the caption nested *inside* the <label>
        // (`<label><input>Caption</label>`) and getLabel() is intentionally null
        // ("bypasses label generation") - getControlPart()/getLabelPart() are the
        // only way to get the input and caption as separate sibling elements.
        $input = $control->getControlPart();
        $input->class ??= 'form-check-input';
        $input->class('is-invalid', $control->hasErrors());

        $label = $control->getLabelPart();
        $label->class ??= 'form-check-label';

        return Html::el('div')->class('form-check')
            ->addHtml($this->applyAttrs($input, $attrs))
            ->addHtml($label)
        ;
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function button(Button $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class ??= 'btn btn-primary';
        return $this->applyAttrs($el, $attrs);
    }
}
