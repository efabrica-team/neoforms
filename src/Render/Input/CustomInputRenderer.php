<?php

namespace Efabrica\NeoForms\Render\Input;

use Nette\Forms\Controls\BaseControl;

interface CustomInputRenderer
{
    /**
     * @param BaseControl $el
     * @param array       $attrs
     * @param array       $options
     * @return string|null
     *              NULL   - Do not handle. Tries a different renderer.
     *              ""     - Don't render this element at all.
     *              string - HTML string to render for this element
     */
    public function render(BaseControl $el, array $attrs, array $options): ?string;

    public function readonlyRender(BaseControl $el, array $options): ?string;
}