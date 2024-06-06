<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Build\NeoForm;
use Nette\Forms\Form;
use Nette\Forms\FormRenderer;
use Nette\HtmlStringable;
use RuntimeException;

class NeoFormNetteRenderer implements FormRenderer
{
    private NeoFormRenderer $renderer;

    public function __construct(NeoFormRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(Form $form): string
    {
        if (!$form instanceof NeoForm) {
            throw new RuntimeException('form is not instance of NeoForm');
        }
        $rendered = $this->renderer->form($form, $form->getOptions());
        iterator_to_array($rendered);
        $return = $rendered->getReturn();
        if (!is_string($return) && !$return instanceof HtmlStringable) {
            throw new RuntimeException('form renderer must return string or HtmlStringable');
        }
        return $return;
    }
}
