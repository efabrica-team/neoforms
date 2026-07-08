<?php

namespace Tests\Efabrica\NeoForms\Render\Template;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;
use PHPUnit\Framework\TestCase;

class NeoFormTemplateTest extends TestCase
{
    public function testControlUsesRegisteredRendererWhenMatching(): void
    {
        $template = new NeoFormTemplateExposed();
        $template->setControlRenderer(TextInput::class, function (TextInput $control) {
            return Html::el('custom-input')->setAttribute('name', $control->getName());
        });

        $form = new NeoForm();
        $control = $form->addText('foo', 'Foo');

        $html = $template->callControl($control, []);

        $this->assertStringContainsString('<custom-input', (string)$html);
        $this->assertStringContainsString('name="foo"', (string)$html);
    }

    public function testControlFallsBackToBuiltInChainWhenNoRendererRegistered(): void
    {
        $template = new NeoFormTemplateExposed();

        $form = new NeoForm();
        $control = $form->addText('foo', 'Foo');

        $html = $template->callControl($control, []);

        $this->assertStringContainsString('<input', (string)$html);
        $this->assertStringNotContainsString('<custom-input', (string)$html);
    }

    public function testSetControlRendererReturnsSelfForFluentUse(): void
    {
        $template = new NeoFormTemplate();
        $result = $template->setControlRenderer(TextInput::class, fn(TextInput $c) => Html::el());
        $this->assertSame($template, $result);
    }
}

class NeoFormTemplateExposed extends NeoFormTemplate
{
    /**
     * @param array<string, mixed> $attrs
     */
    public function callControl(\Nette\Forms\Controls\BaseControl $control, array $attrs): Html
    {
        return $this->control($control, $attrs);
    }
}
