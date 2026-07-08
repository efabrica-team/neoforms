<?php

namespace Tests\Efabrica\NeoForms\DI\Node;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\DI\NeoFormLatteExtension;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Latte\Engine;
use Nette\Localization\Translator;
use PHPUnit\Framework\TestCase;
use Stringable;

class NeoFormUnpairedNodeTest extends TestCase
{
    private function createEngine(NeoFormRenderer $renderer): Engine
    {
        $engine = new Engine();
        $engine->setLoader(new \Latte\Loaders\StringLoader());
        $engine->addExtension(new NeoFormLatteExtension());
        $engine->addProvider('neoFormRenderer', $renderer);
        return $engine;
    }

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

    /**
     * Regression: {formErrors} used to compile to a call to the non-existent
     * NeoFormRenderer::formError() (missing trailing "s") and fatal at runtime.
     */
    public function testFormErrorsTagRendersControlErrors(): void
    {
        $form = new NeoForm();
        $control = $form->addText('name', 'Name');
        $control->addError('This field is required');

        $html = $this->createEngine($this->createRenderer())
            ->renderToString('{formErrors $form[\'name\']}', ['form' => $form]);

        $this->assertStringContainsString('This field is required', $html);
    }
}
