<?php

namespace Efabrica\NeoForms\Control;

class StaticTags extends Tags
{
    public function __construct(string $label, array $choices, ?string $placeholder = null)
    {
        parent::__construct($label, ['type' => 'const', 'choices' => $choices], $placeholder);
    }
}
