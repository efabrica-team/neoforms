<?php

namespace Efabrica\NeoForms\Render;

use Latte\Engine;

class NeoFormRendererTemplate
{
    private string $template;

    private ?Engine $engine = null;

    public function __construct(?string $template = null)
    {
        $this->template = $template ?? __DIR__ . '/templates/bootstrap5.latte';
    }

    public function setEngine(Engine $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function block(string $blockName, array $attrs = []): string
    {
        assert($this->engine instanceof Engine);
        return $this->engine->renderToString($this->template, $attrs, $blockName);
    }
}