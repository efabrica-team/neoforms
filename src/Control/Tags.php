<?php

namespace Efabrica\NeoForms\Control;

use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;
use Nette\Utils\Json;

class Tags extends TextInput
{
    private array $config;

    private ?string $placeholder;

    public function __construct(string $label, array $config, ?string $placeholder = null)
    {
        parent::__construct($label);
        $this->config = $config;
        $this->placeholder = $placeholder;
    }

    public function getTagsConfig(): array
    {
        return $this->config;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getControl(): Html
    {
        $control = parent::getControl();
        $control
            ->setAttribute('data-placeholder', $this->translate($this->placeholder))
            ->setAttribute('data-config', Json::encode($this->config))
        ;
        $control->setAttribute('class', 'js-tagsinput ' . $control->getAttribute('class'));
        return $control;
    }
}
