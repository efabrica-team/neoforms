<?php

namespace Efabrica\NeoForms\Build\Internal;

use Efabrica\NeoForms\Build\NeoContainer;
use Efabrica\NeoForms\Control\CodeEditor;
use Efabrica\Nette\Chooze\ChoozeControl;
use Efabrica\Nette\Forms\Rte\Registrator;
use Efabrica\Nette\Forms\Rte\RteControl;
use JetBrains\PhpStorm\ExpectedValues;
use Nette\Forms\Controls\BaseControl;
use Nette\NotImplementedException;

/**
 * @used-by \Efabrica\NeoForms\Build\NeoForm
 * @used-by \Efabrica\NeoForms\Build\NeoContainer
 */
trait EfabricaTrait
{
    public function addRte(string $name, ?string $label = null): RteControl
    {
        $component = new RteControl($label);
        $registrator = $this->getOption(EfabricaConst::OPT_RTE);
        if (!$registrator instanceof Registrator) {
            throw new NotImplementedException('composer require efabrica/nette-rte');
        }
        foreach ($registrator->getDataUrls() as $type => $url) {
            $component->setDataUrl($type, $url);
        }
        $this->addComponent($component, $name);
        return $component;
    }

    /**
     * @param value-of<CodeEditor::MODES> $mode
     */
    public function addCodeEditor(
        string $name,
        #[ExpectedValues(CodeEditor::MODES)]
        string $mode,
        ?string $label = null
    ): CodeEditor {
        $component = new CodeEditor($mode, $label);
        $this->addComponent($component, $name);
        return $component;
    }

    /**
     * @see Registrator
     */
    public function addChooze(
        string $name,
        string $type,
        ?string $label = null,
        bool $multiple = false,
        array $urlParams = [],
        array $uploadParams = []
    ): ChoozeControl {
        $args = func_get_args();
        unset($args[1]);
        $this->__call('addChooze' . ucfirst($type), $args);
        $control = $this->getComponent($name);
        assert($control instanceof ChoozeControl);
        return $control;
    }

    /**
     * @see \Efabrica\IrisClient\FormExtension\AccountExtension
     */
    public function addAccount(string $name = 'account_id', string $label = 'Account'): BaseControl
    {
        $this->__call('addAccount', [$name, $label]);
        /** @var BaseControl $control */
        $control = $this->getComponent($name);
        return $control;
    }

    public function addContainer($name): NeoContainer
    {
        $control = new NeoContainer();
        $control->setOption(EfabricaConst::OPT_RTE, $this->getOption(EfabricaConst::OPT_RTE));
        $control->currentGroup = $this->currentGroup;
        if ($this->currentGroup !== null) {
            $this->currentGroup->add($control);
        }

        $this->addComponent($control, is_int($name) ? (string)$name : $name);
        return $control;
    }
}
