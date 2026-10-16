<?php

namespace Tests\Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Nette\Localization\Translator;
use PHPUnit\Framework\TestCase;

class StaticHtmlTest extends TestCase
{
    private function renderer(NeoForm $form): NeoFormRenderer
    {
        $translator = new class implements Translator {
            public function translate(string|\Stringable $message, mixed ...$parameters): string
            {
                return strtoupper((string)$message);
            }
        };
        $form->setTranslator($translator);
        return new NeoFormRenderer(new NeoFormTemplate(), $translator);
    }

    public function testStaticElementsGoThroughTemplate(): void
    {
        $form = new NeoForm();
        $form->addHeading('Title', 2, 'title');
        $form->addAlert('Careful', 'warning');
        $form->addText('x');
        $form->addSeparator();
        $form->addSpace('lg');
        $r = $this->renderer($form);

        $this->assertSame(['title', '_static1', 'x', '_static2', '_static3'], array_keys(iterator_to_array($form->getComponents())));
        $this->assertSame('<h2 class="mt-3">TITLE</h2>', (string)$r->formRow($form['title'], ['+class' => 'mt-3']));
        $this->assertSame('<div role="alert" class="alert alert-warning">CAREFUL</div>', (string)$r->formRow($form['_static1']));
        $this->assertSame('<hr>', (string)$r->formRow($form['_static2']));
        $this->assertSame('<div aria-hidden="true" style="height: 2rem"></div>', (string)$r->formRow($form['_static3']));
        $this->assertTrue($form['title']->isOmitted());
    }

    public function testTemplateCanRestyle(): void
    {
        $form = new NeoForm();
        $form->addAlert('Hi', 'danger');
        $template = new class extends NeoFormTemplate {
            public function staticHtml(\Efabrica\NeoForms\Control\StaticHtml $control, array $attrs): \Nette\Utils\Html
            {
                return \Nette\Utils\Html::el('aside', ['data-kind' => $control->getKind()]);
            }
        };
        $form->setTemplate($template);
        $r = $this->renderer($form);
        $this->assertSame('<aside data-kind="alert"></aside>', (string)$r->formRow($form['_static1']));
    }
}
