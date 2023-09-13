<?php

namespace Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Build\NeoContainer;
use Nette\Forms\Controls\HiddenField;

class FormCollectionItem extends NeoContainer
{
    const UNIQID = '__neoFC_uniqid__';
    private ?HiddenField $uniqId = null;
    public function __construct(bool $prototype = false)
    {
        if (!$prototype) {
            $this->uniqId = $this->addHidden(self::UNIQID, uniqid());
        }
    }

    public function getUniqid(): ?string
    {
        return $this->uniqId ? $this->uniqId->getValue() : null;
    }
}
