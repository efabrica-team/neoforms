<?php

namespace Efabrica\NeoForms\DI;

use Efabrica\NeoForms\Build\NeoFormFactory;
use Efabrica\NeoForms\Render\NeoFormNetteRenderer;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Latte\Engine;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;

class NeoFormsCompilerExtension extends CompilerExtension
{
    public function loadConfiguration(): void
    {
        $di = $this->getContainerBuilder();

        $di->addDefinition($this->prefix('renderer'))
            ->setFactory(NeoFormRenderer::class)
        ;

        $di->addDefinition($this->prefix('netteRenderer'))
            ->setCreator(NeoFormNetteRenderer::class)
        ;

        $di->addDefinition($this->prefix('factory'))
            ->setFactory(NeoFormFactory::class)
        ;

        /** @var FactoryDefinition $latteFactory */
        $latteFactory = $di->getDefinition('nette.latteFactory');
        $latteEngine = $latteFactory->getResultDefinition();

        $di->addDefinition($this->prefix('template'))
            ->setFactory(NeoFormTemplate::class)
        ;

        $latteEngine->addSetup('addProvider', ['neoFormRenderer', '@' . $this->prefix('renderer')]);
        if (Engine::VERSION_ID >= 30000) {
            $di->addDefinition($this->prefix('latteExtension'))
                ->setFactory(NeoFormLatteExtension::class);
            $latteEngine->addSetup('addExtension', ['@' . $this->prefix('latteExtension')]);
        } else {
            $latteEngine->addSetup(NeoFormMacroSet::class . '::install(?->getCompiler())', ['@self']);
        }
    }
}
