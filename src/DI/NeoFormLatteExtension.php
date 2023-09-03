<?php

namespace Efabrica\NeoForms\DI;

use Efabrica\NeoForms\DI\Node\FormNode;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Latte\Extension;

class NeoFormLatteExtension extends Extension
{
    private NeoFormRenderer $renderer;

    public function __construct(NeoFormRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getTags(): array
    {
        return [
            'neoForm' => [FormNode::class, 'create'],
        ];
    }
}
