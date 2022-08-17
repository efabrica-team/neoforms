<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Build\NeoContainerTrait;
use Efabrica\NeoForms\Control\Col;
use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Efabrica\NeoForms\Control\Div;
use Efabrica\NeoForms\Control\GroupBuilder;
use Efabrica\NeoForms\Control\StaticTags;
use Efabrica\NeoForms\Control\Tags;
use Efabrica\NeoForms\Control\ToggleSwitch;
use Efabrica\Nette\Chooze\ChoozeControl;
use Efabrica\Nette\Chooze\Registrator;
use Efabrica\Nette\Forms\Rte\RteControl;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use RadekDostal\NetteComponents\DateTimePicker\TbDatePicker;
use RadekDostal\NetteComponents\DateTimePicker\TbDateTimePicker;

class NeoForm extends Form
{
    private bool $readonly = false;

    use NeoContainerTrait;

    public function setReadonly(bool $readonly = true): void
    {
        $this->readonly = $readonly;
    }

    public function isReadonly(): bool
    {
        return $this->readonly;
    }
}
