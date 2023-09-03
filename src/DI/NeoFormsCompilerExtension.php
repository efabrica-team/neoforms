<?php

namespace Efabrica\NeoForms\DI;

use Efabrica\NeoForms\Build\NeoFormFactory;
use Efabrica\NeoForms\Render\NeoFormNetteRenderer;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Efabrica\NeoForms\Render\Template\DefaultFormTemplate;
use Latte\Engine;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\Statement;

class NeoFormsCompilerExtension extends CompilerExtension
{
    public function loadConfiguration(): void
    {
        $di = $this->getContainerBuilder();


        $di->addDefinition($this->prefix('formNetteRenderer'))
            ->setCreator(NeoFormNetteRenderer::class)
        ;

        $di->addDefinition($this->prefix('factory'))
            ->setFactory(NeoFormFactory::class)
        ;

        /** @var FactoryDefinition $latteFactory */
        $latteFactory = $di->getDefinition('nette.latteFactory');
        $latteEngine = $latteFactory->getResultDefinition();

        $di->addDefinition($this->prefix('formTemplate'))
            ->setFactory(DefaultFormTemplate::class)
        ;

        $latteEngine->addSetup("addProvider", ['neoFormRenderer', new Statement(NeoFormRenderer::class)]);
        if (Engine::VERSION_ID >= 30000) {
            $latteEngine->addSetup("addExtension", '@');
        } else {
            $latteEngine->addSetup(NeoFormMacroSet::class . '::install(?->getCompiler())', ['@self']);
        }

        $di->addDefinition($this->prefix('formRenderer'))
            ->setFactory(NeoFormRenderer::class . '::obtainFromEngine', ['@' . Engine::class])
        ;
    }
}
