<?php

namespace Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Build\NeoContainer;
use Nette\Forms\Controls\HiddenField;
use Nette\Utils\ArrayHash;

class FormCollectionItem extends NeoContainer
{
    public const UNIQID = '__neoFC_uniqId__';

    private ?HiddenField $uniqId = null;

    public function __construct(bool $prototype = false)
    {
        $this->setSingleRender(true);
        if (!$prototype) {
            $this->uniqId = $this->addHidden(self::UNIQID, uniqid());
        }
    }

    public function getUntrustedValues($returnType = ArrayHash::class, ?array $controls = null): object|array
    {
        if ($this->uniqId !== null && $this->uniqId->getParent() !== null && $this->uniqId->getValue() === '') {
            $this->removeComponent($this->uniqId);
        }
        $values = parent::getUntrustedValues($returnType, $controls);
        if ($this->uniqId !== null && $this->uniqId->getParent() === null) {
            $this->addComponent($this->uniqId, self::UNIQID);
        }
        return $values;
    }
}
