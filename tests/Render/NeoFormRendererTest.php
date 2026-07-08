<?php

namespace Tests\Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Render\NeoFormNetteRenderer;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Nette\Localization\Translator;
use Nette\Utils\Html;
use PHPUnit\Framework\TestCase;
use Stringable;

class NeoFormRendererTest extends TestCase
{
    private function createRenderer(): NeoFormRenderer
    {
        $translator = new class implements Translator {
            public function translate(string|Stringable $message, mixed ...$parameters): string
            {
                return (string) $message;
            }
        };
        return new NeoFormRenderer(new NeoFormTemplate(), $translator);
    }

    public function testRenderToHtmlReturnsHtmlWithoutExposingGenerator(): void
    {
        $renderer = $this->createRenderer();
        $form = new NeoForm();
        $form->addText('name', 'Name');

        $html = $renderer->renderToHtml($form);

        $this->assertInstanceOf(Html::class, $html);
        $this->assertStringContainsString('name="name"', (string) $html);
    }

    public function testRenderToHtmlMatchesNeoFormNetteRendererOutput(): void
    {
        $renderer = $this->createRenderer();

        $form1 = new NeoForm();
        $form1->addText('name', 'Name');
        $viaRenderToHtml = (string) $renderer->renderToHtml($form1, $form1->getOptions());

        $form2 = new NeoForm();
        $form2->addText('name', 'Name');
        $viaNetteRenderer = (new NeoFormNetteRenderer($renderer))->render($form2);

        $this->assertSame($viaRenderToHtml, $viaNetteRenderer);
    }

    public function testFormTemplateGeneratorEmbedsSentBlockBody(): void
    {
        $template = new NeoFormTemplate();
        $renderer = new NeoFormRenderer($template, $this->createTranslator());
        $form = new NeoForm();

        $generator = $template->form($renderer, $form, Html::el(), []);
        $generator->current();
        $generator->send(Html::fromHtml('<div id="block-body"></div>'));
        $result = $generator->getReturn();

        $this->assertStringContainsString('block-body', (string) $result);
    }

    private function createTranslator(): Translator
    {
        return new class implements Translator {
            public function translate(string|Stringable $message, mixed ...$parameters): string
            {
                return (string) $message;
            }
        };
    }
}
